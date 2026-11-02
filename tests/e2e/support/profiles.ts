import { resolve } from 'node:path';

// The same names and pattern as database/seeders/ScenarioSeeder.php.
export const PROFILES = ['super-admin', 'internal', 'vendor'] as const;
export const PASSWORD = 'password';

export type Profile = (typeof PROFILES)[number];

export function runId(): string {
    return process.env.E2E_RUN_ID ?? 'local';
}

export function emailOf(profile: Profile): string {
    return `${profile}.${runId()}@e2e.pranata.test`;
}

export function storageStateOf(profile: Profile): string {
    return resolve(import.meta.dirname, '..', '.auth', `${profile}.json`);
}
