<?php

namespace Database\Seeders;

use App\Models\User;
use App\Modules\Organization\Models\OrgAppointment;
use App\Modules\Organization\Models\OrgPosition;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;
use Laravel\Fortify\RecoveryCode;
use RuntimeException;

/**
 * Synthetic data for the browser tests: the profiles and the synthetic organization (TASK-013). Later Tasks extend it;
 * the role of a profile has no table yet, so a profile is only a user for now.
 */
class ScenarioSeeder extends Seeder
{
    /**
     * The same names as tests/e2e/support/profiles.ts.
     *
     * @var list<string>
     */
    public const PROFILES = ['super-admin', 'internal', 'vendor'];

    /**
     * Synthetic; the same value as tests/e2e/support/totp.ts.
     */
    public const SUPER_ADMIN_TOTP_SECRET = 'E2EPRANATATOTPAB';

    public function run(): void
    {
        if (app()->environment('production')) {
            throw new RuntimeException('ScenarioSeeder is forbidden in production.');
        }

        $run = getenv('E2E_RUN_ID') ?: 'local';

        foreach (self::PROFILES as $profile) {
            // Safe to repeat: the identity carries the run, and a repeat of one run updates its own rows.
            User::query()->updateOrCreate(
                ['email' => "{$profile}.{$run}@e2e.pranata.test"],
                [
                    'role_group' => strtoupper(str_replace('-', '_', $profile)),
                    'username' => self::usernameOf($profile, $run),
                    'name' => "E2E {$profile} {$run}",
                    'password' => Hash::make('password'),
                    'email_verified_at' => now(),
                    ...($profile === 'super-admin' ? self::confirmedTotp() : []),
                ],
            );
        }

        $this->call([OrganizationSeeder::class, AccessProfileSeeder::class]);

        // A synthetic appointment, so the decree evidence of PG-505 can be exercised in the browser (TASK-016). It grants
        // nothing (OD-28). Safe to repeat: the same person and position update their own row.
        $internal = User::query()->where('email', "internal.{$run}@e2e.pranata.test")->firstOrFail();
        $position = OrgPosition::query()->where('code', 'KAPRODI_TI')->firstOrFail();

        OrgAppointment::query()->updateOrCreate(
            ['user_id' => $internal->id, 'position_id' => $position->id],
            [
                'org_unit_id' => $position->org_unit_id,
                'effective_from' => OrganizationSeeder::EFFECTIVE_FROM,
                'decree_ref' => 'SK/SINTETIS/2026/001',
                'status' => OrgAppointment::ACTIVE,
            ],
        );
    }

    /**
     * The super-admin opens Administration only with confirmed TOTP (TASK-012 TAC-02); the browser tests answer its
     * challenge from the same synthetic secret.
     *
     * @return array<string, mixed>
     */
    private static function confirmedTotp(): array
    {
        return [
            'two_factor_secret' => encrypt(self::SUPER_ADMIN_TOTP_SECRET),
            'two_factor_recovery_codes' => encrypt(json_encode(Collection::times(8, fn () => RecoveryCode::generate())->all())),
            'two_factor_confirmed_at' => now(),
        ];
    }

    /**
     * The same pattern as usernameOf() in tests/e2e/support/profiles.ts.
     */
    public static function usernameOf(string $profile, string $run): string
    {
        return str_replace('-', '_', $profile).'.'.$run;
    }
}
