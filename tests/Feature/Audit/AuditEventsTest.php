<?php

use App\Modules\Audit\Support\AuditPartitions;
use App\Modules\Platform\Support\DatabaseRoles;
use App\Modules\Platform\Support\IntegrityTriggers;
use Carbon\CarbonImmutable;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\DB;
use Tests\Support\DatabaseSession;

/**
 * @return list<string>
 */
function auditPartitionNames(): array
{
    return DB::table('pg_inherits')
        ->join('pg_class', 'pg_class.oid', '=', 'pg_inherits.inhrelid')
        ->whereRaw("pg_inherits.inhparent = 'audit_events'::regclass")
        ->orderBy('pg_class.relname')
        ->pluck('pg_class.relname')
        ->all();
}

function insertAuditProbe(string $action, ?string $occurredAt = null): void
{
    DB::table('audit_events')->insert(array_filter([
        'action' => $action,
        'module' => 'audit',
        'occurred_at' => $occurredAt,
    ]));
}

function auditPartitionOf(string $action): ?string
{
    $partition = DB::scalar('select tableoid::regclass::text from audit_events where action = ?', [$action]);

    return is_string($partition) ? $partition : null;
}

test('the partitions of the current month and three months ahead exist, with a default partition', function () {
    $month = CarbonImmutable::now('UTC')->startOfMonth();
    $expected = ['audit_events_default'];

    for ($offset = 0; $offset <= 3; $offset++) {
        $expected[] = 'audit_events_'.$month->addMonthsNoOverflow($offset)->format('Y_m');
    }

    expect(auditPartitionNames())->toContain(...$expected);
});

test('an event lands in the partition of its month, and outside the range in the default partition', function () {
    insertAuditProbe('audit.probe_now');
    insertAuditProbe('audit.probe_old', '2001-01-15 00:00:00+00');

    expect(auditPartitionOf('audit.probe_now'))->toBe('audit_events_'.CarbonImmutable::now('UTC')->format('Y_m'))
        ->and(auditPartitionOf('audit.probe_old'))->toBe('audit_events_default');
});

test('pranata_app may read and add audit events and nothing else', function () {
    insertAuditProbe('audit.probe_old', '2001-01-15 00:00:00+00');

    $update = fn () => DB::table('audit_events')->where('action', 'audit.probe_old')->update(['reason' => 'diubah']);
    $delete = fn () => DB::table('audit_events')->where('action', 'audit.probe_old')->delete();

    expect(DatabaseSession::sqlStateAs(DatabaseRoles::APP, fn () => insertAuditProbe('audit.probe_app')))->toBeNull()
        ->and(DatabaseSession::sqlStateAs(DatabaseRoles::APP, fn () => DB::table('audit_events')->count()))->toBeNull()
        ->and(DatabaseSession::sqlStateAs(DatabaseRoles::APP, $update))->toBe('42501')
        ->and(DatabaseSession::sqlStateAs(DatabaseRoles::APP, $delete))->toBe('42501')
        ->and(DatabaseSession::sqlStateAs(DatabaseRoles::APP, fn () => DB::statement('truncate audit_events')))->toBe('42501')
        ->and(DatabaseSession::sqlStateAs(DatabaseRoles::APP, fn () => DB::table('audit_events_default')->count()))->toBe('42501');
});

test('the append-only trigger refuses UPDATE and DELETE even for roles with the privilege', function () {
    insertAuditProbe('audit.probe_old', '2001-01-15 00:00:00+00');

    foreach ([null, DatabaseRoles::OWNER] as $role) {
        expect(DatabaseSession::sqlStateAs($role, fn () => DB::table('audit_events')->update(['reason' => 'diubah'])))
            ->toBe(IntegrityTriggers::APPEND_ONLY)
            ->and(DatabaseSession::sqlStateAs($role, fn () => DB::table('audit_events')->delete()))
            ->toBe(IntegrityTriggers::APPEND_ONLY)
            ->and(DatabaseSession::sqlStateAs($role, fn () => DB::statement('delete from audit_events_default')))
            ->toBe(IntegrityTriggers::APPEND_ONLY);
    }

    expect(DB::table('audit_events')->where('action', 'audit.probe_old')->value('reason'))->toBeNull();
});

test('pranata_app creates the partitions ahead, and repeating it changes nothing', function () {
    $created = [];
    $repeated = [];

    $state = DatabaseSession::sqlStateAs(DatabaseRoles::APP, function () use (&$created, &$repeated) {
        $created = AuditPartitions::ensure(DB::connection(), CarbonImmutable::parse('2030-12-10 03:00:00', 'UTC'), 2);
        $repeated = AuditPartitions::ensure(DB::connection(), CarbonImmutable::parse('2030-12-31 23:00:00', 'UTC'), 2);
        insertAuditProbe('audit.probe_future', '2031-02-28 23:59:59+00');

        expect(auditPartitionOf('audit.probe_future'))->toBe('audit_events_2031_02');
    });

    expect($state)->toBeNull()
        ->and($created)->toBe(['audit_events_2030_12', 'audit_events_2031_01', 'audit_events_2031_02'])
        ->and($repeated)->toBe($created);
});

test('a month is a UTC calendar month', function () {
    // 1 March 05:00 in Jakarta is still 29 February in UTC.
    $names = AuditPartitions::ensure(DB::connection(), CarbonImmutable::parse('2032-03-01 05:00:00', 'Asia/Jakarta'), 0);
    insertAuditProbe('audit.probe_jakarta', '2032-03-01 06:00:00+07');
    insertAuditProbe('audit.probe_utc', '2032-03-01 00:00:00+00');

    expect($names)->toBe(['audit_events_2032_02'])
        ->and(auditPartitionOf('audit.probe_jakarta'))->toBe('audit_events_2032_02')
        ->and(auditPartitionOf('audit.probe_utc'))->toBe('audit_events_default');
});

test('a month the default partition already holds is not created and its events stay', function () {
    insertAuditProbe('audit.probe_caught', '2033-05-20 00:00:00+00');

    $state = DatabaseSession::sqlStateAs(DatabaseRoles::APP, fn () => AuditPartitions::ensure(DB::connection(), CarbonImmutable::parse('2033-05-01', 'UTC'), 0));

    expect($state)->toBe('PN005')
        ->and(auditPartitionOf('audit.probe_caught'))->toBe('audit_events_default')
        ->and(auditPartitionNames())->not->toContain('audit_events_2033_05');
});

test('the months ahead stay within bounds', function () {
    expect(fn () => AuditPartitions::ensure(DB::connection(), monthsAhead: 25))->toThrow(InvalidArgumentException::class)
        ->and(fn () => AuditPartitions::ensure(DB::connection(), monthsAhead: -1))->toThrow(InvalidArgumentException::class);
});

test('audit:partitions lists the partitions it keeps ready', function () {
    $this->artisan('audit:partitions', ['--months' => '1'])
        ->expectsOutput('audit_events_'.CarbonImmutable::now('UTC')->format('Y_m'))
        ->expectsOutput('audit_events_'.CarbonImmutable::now('UTC')->startOfMonth()->addMonthNoOverflow()->format('Y_m'))
        ->assertSuccessful();

    $this->artisan('audit:partitions', ['--months' => 'tiga'])
        ->expectsOutputToContain('--months must be a whole number')
        ->assertExitCode(2);
});

test('the scheduler runs audit:partitions every day', function () {
    $events = collect(app(Schedule::class)->events())
        ->filter(fn ($event) => str_contains((string) $event->command, 'audit:partitions'));

    expect($events)->toHaveCount(1)
        ->and($events->first()->expression)->toBe('15 1 * * *');
});
