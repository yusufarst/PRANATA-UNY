<?php

use App\Models\User;
use App\Modules\Audit\Support\AuditLogger;
use App\Modules\Platform\Exceptions\CommandBusyException;
use App\Modules\Platform\Exceptions\CommandRetriesExhaustedException;
use App\Modules\Platform\Exceptions\DomainRuleViolation;
use App\Modules\Platform\Exceptions\StaleVersionException;
use App\Modules\Platform\Support\Action;
use App\Modules\Platform\Support\HasLockVersion;
use App\Modules\Platform\Support\RequestId;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Sleep;
use Illuminate\Support\Str;
use Inertia\Testing\AssertableInertia as Assert;

/*
 * The base Action, lock_version, the error model and the request id (TASK-009 TAC-03, TAC-04 and TAC-05).
 */
class CommandProbeRecord extends Model
{
    use HasLockVersion;

    protected $table = 'command_probe_records';

    protected $guarded = [];
}

class CommandProbeAction extends Action
{
    /**
     * @param  Closure(int): void  $work  given the attempt, from 0
     */
    public function handle(Closure $work): string
    {
        $attempt = 0;

        return $this->transaction(function () use ($work, &$attempt) {
            DB::table('command_probe_records')->insert(['title' => 'attempt '.$attempt, 'lock_version' => 0]);
            $this->afterCommit(fn () => Log::info('probe committed'));
            $work($attempt++);

            return 'done';
        });
    }
}

function sqlFailure(string $state): PDOException
{
    $exception = new PDOException("SQLSTATE[{$state}]");
    $exception->errorInfo = [$state, 7, 'probe'];

    return $exception;
}

beforeEach(function () {
    Schema::create('command_probe_records', function ($table) {
        $table->id();
        $table->string('title');
        $table->integer('lock_version')->default(0);
        $table->timestamps();
    });
});

test('a deadlock or serialization failure is retried and then succeeds once', function (string $state) {
    Sleep::fake();
    Log::spy();

    $result = (new CommandProbeAction)->handle(function (int $attempt) use ($state) {
        if ($attempt < Action::MAX_RETRIES) {
            throw sqlFailure($state);
        }
    });

    expect($result)->toBe('done')
        ->and(DB::table('command_probe_records')->pluck('title')->all())->toBe(['attempt 2']);

    Sleep::assertSleptTimes(Action::MAX_RETRIES);
    Log::shouldHaveReceived('info')->with('probe committed')->once();
})->with(['40P01', '40001']);

test('a deadlock that outlasts the retries answers 503 and leaves nothing behind', function () {
    Sleep::fake();
    Log::spy();

    expect(fn () => (new CommandProbeAction)->handle(fn () => throw sqlFailure('40P01')))
        ->toThrow(CommandRetriesExhaustedException::class);

    expect(DB::table('command_probe_records')->count())->toBe(0)
        ->and((new CommandRetriesExhaustedException(sqlFailure('40P01')))->getStatusCode())->toBe(503);

    Log::shouldNotHaveReceived('info');
});

test('other failures are not retried', function () {
    $runs = 0;

    expect(fn () => (new CommandProbeAction)->handle(function () use (&$runs) {
        $runs++;

        throw new RuntimeException('probe');
    }))->toThrow(RuntimeException::class, 'probe');

    expect($runs)->toBe(1)
        ->and(DB::table('command_probe_records')->count())->toBe(0);
});

test('a row lock that does not come free is a busy command', function () {
    expect(fn () => (new CommandProbeAction)->handle(fn () => throw sqlFailure('55P03')))
        ->toThrow(CommandBusyException::class);

    expect(DB::table('command_probe_records')->count())->toBe(0);
});

test('saving at the seen version raises it by one', function () {
    $record = CommandProbeRecord::create(['title' => 'Lama']);
    $record->title = 'Baru';
    $record->saveAtVersion(0);

    expect($record->lock_version)->toBe(1)
        ->and($record->isDirty())->toBeFalse()
        ->and(CommandProbeRecord::find($record->id)->only('title', 'lock_version'))->toBe(['title' => 'Baru', 'lock_version' => 1]);
});

test('saving at a stale version writes nothing', function () {
    $record = CommandProbeRecord::create(['title' => 'Lama']);
    DB::table('command_probe_records')->where('id', $record->id)->update(['lock_version' => 1]);

    $record->title = 'Baru';

    expect(fn () => $record->saveAtVersion(0))->toThrow(StaleVersionException::class);
    expect(CommandProbeRecord::find($record->id)->only('title', 'lock_version'))->toBe(['title' => 'Lama', 'lock_version' => 1]);
});

test('a version conflict answers 409 with the latest data and the conflict message', function () {
    $user = User::factory()->create();
    $record = CommandProbeRecord::create(['title' => 'Terbaru', 'lock_version' => 3]);

    Route::middleware(['web', 'auth'])->patch('/_probe/records/{id}', function (int $id) {
        $latest = CommandProbeRecord::findOrFail($id);
        $latest->title = request('title');
        $latest->saveAtVersion((int) request('lock_version'));
    });
    Route::middleware(['web', 'auth'])->patch('/_probe/records/{id}/page', function (int $id) {
        $latest = CommandProbeRecord::findOrFail($id);
        $latest->title = request('title');

        try {
            $latest->saveAtVersion((int) request('lock_version'));
        } catch (StaleVersionException $exception) {
            throw $exception->showing('home', ['record' => CommandProbeRecord::findOrFail($id)->only('title', 'lock_version')]);
        }
    });

    $this->actingAs($user)
        ->patchJson("/_probe/records/{$record->id}", ['title' => 'Milik saya', 'lock_version' => 2])
        ->assertConflict()
        ->assertJsonPath('errors.lock_version.0', __('command.conflict'));

    $this->actingAs($user)
        ->patch("/_probe/records/{$record->id}/page", ['title' => 'Milik saya', 'lock_version' => 2], ['X-Inertia' => 'true'])
        ->assertConflict()
        ->assertHeader('X-Inertia', 'true')
        ->assertJsonPath('component', 'home')
        ->assertJsonPath('props.record', ['title' => 'Terbaru', 'lock_version' => 3])
        ->assertJsonPath('props.errors.lock_version', __('command.conflict'));

    $this->actingAs($user)
        ->from('/settings/profile')
        ->patch("/_probe/records/{$record->id}", ['title' => 'Milik saya', 'lock_version' => 2])
        ->assertRedirect('/settings/profile')
        ->assertSessionHasErrors('lock_version');

    expect(CommandProbeRecord::find($record->id)->title)->toBe('Terbaru');
});

test('a business prerequisite answers 422 under errors._domain with its reason key', function () {
    $violation = DomainRuleViolation::because('command.busy');

    expect($violation->errors())->toBe(['_domain' => [__('command.busy')]])
        ->and($violation->reasonKey)->toBe('command.busy')
        ->and($violation->status)->toBe(422);
});

test('every response carries the request id that the log context and audit events carry', function () {
    $user = User::factory()->create();
    $seen = [];

    Route::middleware(['web', 'auth'])->post('/_probe/audited', function () use (&$seen) {
        $seen['log'] = Log::sharedContext()['request_id'] ?? null;
        app(AuditLogger::class)->record('identity.profile_updated', 'identity');

        return response('ok');
    });

    // An id sent by the client is never trusted.
    $sent = (string) Str::uuid();
    $response = $this->actingAs($user)->withHeader(RequestId::HEADER, $sent)->post('/_probe/audited');

    $id = $response->headers->get(RequestId::HEADER);

    expect(Str::isUuid($id))->toBeTrue()
        ->and($id)->not->toBe($sent)
        ->and($seen['log'])->toBe($id)
        ->and(DB::table('audit_events')->sole()->request_id)->toBe($id);
});

test('an error page shows the request id of its response', function () {
    $response = $this->get('/alamat-yang-tidak-ada')->assertNotFound();

    $response->assertInertia(fn (Assert $page) => $page
        ->component('errors/show')
        ->where('requestId', $response->headers->get(RequestId::HEADER)));
});
