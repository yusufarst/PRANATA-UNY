import { createHmac } from 'node:crypto';

// The authenticator app of the tests: RFC 6238 with SHA-1, 6 digits and 30 second steps, as the server checks it.

const BASE32 = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
const STEP_SECONDS = 30;

// Synthetic, for the E2E super-admin only; the same value as database/seeders/ScenarioSeeder.php.
export const SUPER_ADMIN_TOTP_SECRET = 'E2EPRANATATOTPAB';

function base32Decode(secret: string): Buffer {
    let bits = '';

    for (const char of secret.replace(/[\s=]/g, '').toUpperCase()) {
        const value = BASE32.indexOf(char);

        if (value === -1) {
            throw new Error(`[${char}] is not base32`);
        }

        bits += value.toString(2).padStart(5, '0');
    }

    const bytes: number[] = [];

    for (let index = 0; index + 8 <= bits.length; index += 8) {
        bytes.push(parseInt(bits.slice(index, index + 8), 2));
    }

    return Buffer.from(bytes);
}

export function totpCode(secret: string, at: number = Date.now()): string {
    const counter = Buffer.alloc(8);

    counter.writeBigUInt64BE(BigInt(Math.floor(at / 1000 / STEP_SECONDS)));

    const hmac = createHmac('sha1', base32Decode(secret)).update(counter).digest();
    const offset = hmac[hmac.length - 1] & 0x0f;
    const binary = hmac.readUInt32BE(offset) & 0x7fffffff;

    return (binary % 1_000_000).toString().padStart(6, '0');
}

/**
 * The server takes a code once, so a second sign-in waits for the next step and its fresh code.
 */
export async function nextTotpCode(secret: string, after: string): Promise<string> {
    let code = totpCode(secret);

    while (code === after) {
        await new Promise((resolve) => setTimeout(resolve, 1000));
        code = totpCode(secret);
    }

    return code;
}
