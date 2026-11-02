<?php

use App\Models\User;
use App\Modules\Audit\Support\AuditLogger;
use App\Modules\Platform\Support\DatabaseRoles;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\Support\DatabaseSession;

/**
 * Bind a request as the HTTP kernel would, with a known address and browser.
 */
function auditRequest(): Request
{
    $request = Request::create('/probe', 'POST', server: ['REMOTE_ADDR' => '203.0.113.7', 'HTTP_USER_AGENT' => 'PestProbe/1.0']);
    app()->instance('request', $request);

    return $request;
}

test('an event records the actor, action, subject, changes, reason, request and address', function () {
    $user = User::factory()->create();
    $this->actingAs($user);
    $request = auditRequest();

    app(AuditLogger::class)->record(
        'identity.profile_updated',
        'identity',
        subject: $user,
        changes: ['name' => ['before' => 'Lama', 'after' => 'Baru']],
        reason: 'Permintaan pengguna',
        metadata: ['channel' => 'web'],
        workspace: 'unit',
    );

    $event = DB::table('audit_events')->sole();

    expect($event->actor_user_id)->toEqual($user->id)
        ->and($event->action)->toBe('identity.profile_updated')
        ->and($event->module)->toBe('identity')
        ->and($event->subject_type)->toBe('users')
        ->and($event->subject_id)->toEqual($user->id)
        ->and($event->workspace)->toBe('unit')
        ->and($event->reason)->toBe('Permintaan pengguna')
        ->and(json_decode($event->changes, true))->toBe(['name' => ['after' => 'Baru', 'before' => 'Lama']])
        ->and(json_decode($event->metadata, true))->toBe(['channel' => 'web'])
        ->and(Str::isUuid($event->request_id))->toBeTrue()
        ->and($event->request_id)->toBe($request->attributes->get(AuditLogger::REQUEST_ID))
        ->and($event->ip)->toBe('203.0.113.7')
        ->and($event->user_agent)->toBe('PestProbe/1.0')
        ->and($event->occurred_at)->not->toBeNull();
});

test('events of one request share its request id', function () {
    auditRequest();
    $logger = app(AuditLogger::class);

    $logger->record('identity.login_failed', 'identity');
    $logger->record('identity.login_failed', 'identity');

    expect(DB::table('audit_events')->distinct()->pluck('request_id'))->toHaveCount(1)
        ->and(DB::table('audit_events')->count())->toBe(2);
});

test('an event without a signed-in user or subject keeps those fields empty', function () {
    app(AuditLogger::class)->record('identity.login_failed', 'identity', metadata: ['email' => 'tamu@example.test']);

    $event = DB::table('audit_events')->sole();

    expect($event->actor_user_id)->toBeNull()
        ->and($event->subject_type)->toBeNull()
        ->and($event->subject_id)->toBeNull()
        ->and($event->changes)->toBeNull();
});

test('an event never carries a secret, at any depth', function (array $changes, array $metadata, string $refused) {
    expect(fn () => app(AuditLogger::class)->record('identity.password_changed', 'identity', changes: $changes, metadata: $metadata))
        ->toThrow(InvalidArgumentException::class, $refused)
        ->and(DB::table('audit_events')->count())->toBe(0);
})->with([
    'password' => [['password' => ['before' => 'a', 'after' => 'b']], [], 'password'],
    'remember token' => [['remember_token' => ['after' => 'x']], [], 'remember_token'],
    'two-factor secret' => [['two_factor_secret' => ['after' => 'x']], [], 'two_factor_secret'],
    'recovery codes' => [['two_factor_recovery_codes' => ['after' => 'x']], [], 'two_factor_recovery_codes'],
    'nested camel case' => [[], ['context' => ['apiKey' => 'x']], 'context.apiKey'],
]);

test('an invalid action or module code is refused', function (string $action, string $module) {
    expect(fn () => app(AuditLogger::class)->record($action, $module))->toThrow(InvalidArgumentException::class)
        ->and(DB::table('audit_events')->count())->toBe(0);
})->with([
    ['login_failed', 'identity'],
    ['Identity.Login', 'identity'],
    ['identity.login_failed', 'Identity'],
    ['identity.login_failed', 'identity module'],
]);

test('the event is part of the transaction of the action', function () {
    try {
        DB::transaction(function () {
            app(AuditLogger::class)->record('identity.profile_updated', 'identity');

            throw new RuntimeException('the action failed');
        });
    } catch (RuntimeException) {
        // The action failed, so its audit event goes with it.
    }

    expect(DB::table('audit_events')->count())->toBe(0);
});

test('a failed audit write fails the action', function () {
    expect(fn () => DB::transaction(function () {
        DB::table('users')->insert(['name' => 'Gagal', 'email' => 'gagal@example.test', 'password' => 'not-a-real-hash']);
        app(AuditLogger::class)->record('identity.user_created', 'identity', workspace: str_repeat('x', 41));
    }))->toThrow(QueryException::class);

    expect(DB::table('users')->where('email', 'gagal@example.test')->exists())->toBeFalse();
});

test('pranata_app writes audit events', function () {
    $state = DatabaseSession::sqlStateAs(DatabaseRoles::APP, function () {
        app(AuditLogger::class)->record('identity.login_failed', 'identity');

        expect(DB::table('audit_events')->count())->toBe(1);
    });

    expect($state)->toBeNull();
});
