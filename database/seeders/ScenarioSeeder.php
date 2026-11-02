<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use RuntimeException;

/**
 * Synthetic data for the browser tests. Later Tasks extend it: the organization and the role of a profile
 * have no tables yet, so a profile is only a user for now.
 */
class ScenarioSeeder extends Seeder
{
    /**
     * The same names as tests/e2e/support/profiles.ts.
     *
     * @var list<string>
     */
    public const PROFILES = ['super-admin', 'internal', 'vendor'];

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
                    'name' => "E2E {$profile} {$run}",
                    'password' => Hash::make('password'),
                    'email_verified_at' => now(),
                ],
            );
        }
    }
}
