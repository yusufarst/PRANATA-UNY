<?php

use App\Models\User;
use App\Modules\Platform\Http\Middleware\EnsureIdempotentCommand;
use App\Modules\Platform\Support\Action;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Sleep;
use Illuminate\Support\Str;

/*
 * X-Idempotency-Key (COMMAND_AND_API_CONTRACTS section 4; TASK-009 TAC-01 and TAC-02), through a probe command that counts
 * how often it runs.
 */
class IdempotentProbeAction extends Action
{
    /**
     * Write one effect and run $afterCommit once it commits; $fail throws inside the transaction instead, so nothing commits.
     */
    public function handle(Closure $afterCommit, bool $fail = false): void
    {
        $this->transaction(function () use ($afterCommit, $fail) {
            DB::table('idempotent_probe_effects')->insert(['id' => DB::table('idempotent_probe_effects')->count() + 1]);
            $this->afterCommit($afterCommit);

            if ($fail) {
                throw new RuntimeException('rule broken');
            }
        });
    }
}

beforeEach(function () {
    Schema::create('idempotent_probe_effects', fn ($table) => $table->unsignedInteger('id')->primary());

    $this->runs = 0;

    Route::middleware(['web', 'auth', 'idempotent'])->group(function () {
        Route::post('/_probe/command', function (Request $request) {
            $request->validate(['title' => ['required', 'string']]);
            $this->runs++;

            return redirect('/_probe/done/'.$this->runs);
        })->name('_probe.command');
    });

    $this->user = User::factory()->create();
    $this->actingAs($this->user);
});

function idempotencyKey(): string
{
    return (string) Str::uuid();
}

test('a double submit with the same key runs the command once and redirects to the same place', function () {
    $key = idempotencyKey();

    $first = $this->withHeader(EnsureIdempotentCommand::HEADER, $key)->post('/_probe/command', ['title' => 'Satu']);
    $second = $this->withHeader(EnsureIdempotentCommand::HEADER, $key)->post('/_probe/command', ['title' => 'Satu']);

    $first->assertRedirect('/_probe/done/1');
    $second->assertStatus(303)->assertRedirect('/_probe/done/1')->assertHeader('X-Idempotent-Replay', 'true');

    expect($this->runs)->toBe(1)
        ->and(DB::table(EnsureIdempotentCommand::TABLE)->sole()->status)->toBe('COMPLETED');
});

test('the same key with different input is refused with 422 and runs nothing', function () {
    $key = idempotencyKey();

    $this->withHeader(EnsureIdempotentCommand::HEADER, $key)->post('/_probe/command', ['title' => 'Satu']);

    $this->withHeader(EnsureIdempotentCommand::HEADER, $key)
        ->postJson('/_probe/command', ['title' => 'Dua'])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('_idempotency');

    $this->withHeader(EnsureIdempotentCommand::HEADER, $key)
        ->post('/_probe/command', ['title' => 'Dua'])
        ->assertSessionHasErrors('_idempotency');

    expect($this->runs)->toBe(1);
});

test('a material command without a valid key is refused', function (?string $key) {
    $request = $key === null ? $this : $this->withHeader(EnsureIdempotentCommand::HEADER, $key);

    $request->postJson('/_probe/command', ['title' => 'Satu'])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('_idempotency');

    expect($this->runs)->toBe(0);
})->with([null, 'bukan-uuid', '00000000-0000-1000-8000-000000000000']);

test('a key whose command failed validation may be sent again with corrected input', function () {
    $key = idempotencyKey();

    $this->withHeader(EnsureIdempotentCommand::HEADER, $key)
        ->post('/_probe/command', ['title' => ''])
        ->assertSessionHasErrors('title');

    expect(DB::table(EnsureIdempotentCommand::TABLE)->sole()->status)->toBe('FAILED');

    $this->withHeader(EnsureIdempotentCommand::HEADER, $key)
        ->post('/_probe/command', ['title' => 'Satu'])
        ->assertSessionHasNoErrors()
        ->assertRedirect('/_probe/done/1');

    expect($this->runs)->toBe(1);
});

test('a command that committed and then failed is not run again by a resend', function () {
    Route::middleware(['web', 'auth', 'idempotent'])->post('/_probe/after-commit', function () {
        $this->runs++;
        (new IdempotentProbeAction)->handle(fn () => throw new RuntimeException('mail down'));
    });
    $key = idempotencyKey();

    $this->withHeader(EnsureIdempotentCommand::HEADER, $key)->post('/_probe/after-commit')->assertServerError();
    $this->withHeader(EnsureIdempotentCommand::HEADER, $key)
        ->post('/_probe/after-commit')
        ->assertStatus(303)
        ->assertHeader('X-Idempotent-Replay', 'true');

    expect($this->runs)->toBe(1)
        ->and(DB::table('idempotent_probe_effects')->count())->toBe(1)
        ->and(DB::table(EnsureIdempotentCommand::TABLE)->sole()->status)->toBe('COMPLETED');
});

test('a command that failed inside its transaction may be sent again', function () {
    $fail = true;
    Route::middleware(['web', 'auth', 'idempotent'])->post('/_probe/rolled-back', function () use (&$fail) {
        $this->runs++;
        (new IdempotentProbeAction)->handle(fn () => null, $fail);
    });
    $key = idempotencyKey();

    $this->withHeader(EnsureIdempotentCommand::HEADER, $key)->post('/_probe/rolled-back')->assertServerError();
    expect(DB::table(EnsureIdempotentCommand::TABLE)->sole()->status)->toBe('FAILED');

    $fail = false;
    $this->withHeader(EnsureIdempotentCommand::HEADER, $key)->post('/_probe/rolled-back')->assertOk();

    expect($this->runs)->toBe(2)
        ->and(DB::table('idempotent_probe_effects')->count())->toBe(1);
});

test('a key is bound to its user', function () {
    $key = idempotencyKey();

    $this->withHeader(EnsureIdempotentCommand::HEADER, $key)->post('/_probe/command', ['title' => 'Satu']);

    $this->actingAs(User::factory()->create())
        ->withHeader(EnsureIdempotentCommand::HEADER, $key)
        ->post('/_probe/command', ['title' => 'Satu'])
        ->assertRedirect('/_probe/done/2');

    expect($this->runs)->toBe(2)
        ->and(DB::table(EnsureIdempotentCommand::TABLE)->count())->toBe(2);
});

test('a duplicate of a command still in progress gets 409 after waiting', function () {
    Sleep::fake();
    $key = idempotencyKey();
    $input = ['title' => 'Satu'];

    DB::table(EnsureIdempotentCommand::TABLE)->insert([
        'user_id' => $this->user->id,
        'idempotency_key' => $key,
        'route_name' => '_probe.command',
        'request_sha256' => EnsureIdempotentCommand::fingerprint(Request::create('/_probe/command', 'POST', $input)),
        'status' => 'IN_PROGRESS',
        'created_at' => now(),
        'expires_at' => now()->addDay(),
    ]);

    $this->withHeader(EnsureIdempotentCommand::HEADER, $key)
        ->postJson('/_probe/command', $input)
        ->assertConflict()
        ->assertJsonPath('errors._idempotency.0', __('command.in_progress'));

    expect($this->runs)->toBe(0);
    Sleep::assertSleptTimes(20);
});

test('reading requests and guests pass without a key', function () {
    Route::middleware(['web', 'idempotent'])->get('/_probe/read', fn () => 'ok');

    $this->get('/_probe/read')->assertOk();

    auth()->logout();
    Route::middleware(['web', 'idempotent'])->post('/_probe/guest', fn () => 'ok');

    $this->post('/_probe/guest')->assertOk();
});

test('the scheduler prunes expired keys every hour', function () {
    $row = fn (string $key, $expires) => [
        'user_id' => $this->user->id,
        'idempotency_key' => $key,
        'route_name' => '_probe.command',
        'request_sha256' => str_repeat('0', 64),
        'status' => 'COMPLETED',
        'created_at' => now(),
        'expires_at' => $expires,
    ];

    $kept = idempotencyKey();
    DB::table(EnsureIdempotentCommand::TABLE)->insert([$row(idempotencyKey(), now()->subMinute()), $row($kept, now()->addHour())]);

    Artisan::call('platform:prune-idempotency-keys');

    expect(DB::table(EnsureIdempotentCommand::TABLE)->pluck('idempotency_key')->all())->toBe([$kept]);

    $this->artisan('schedule:list')->expectsOutputToContain('platform:prune-idempotency-keys')->assertSuccessful();
});
