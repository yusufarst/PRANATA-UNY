import { afterEach, describe, expect, it, vi } from 'vite-plus/test';
import {
    commandVisit,
    IDEMPOTENCY_HEADER,
    newIdempotencyKey,
} from '@/lib/command';

vi.mock('@inertiajs/react', () => import('@/test/inertia-mock'));

const uuidV4 =
    /^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/;

function setup(key = 'key-1') {
    const rotate = vi.fn();
    const conflict = vi.fn();

    return { visit: commandVisit(key, rotate, conflict), rotate, conflict };
}

afterEach(() => {
    vi.unstubAllGlobals();
});

describe('newIdempotencyKey', () => {
    it('makes a fresh UUID v4 each time', () => {
        const key = newIdempotencyKey();

        expect(key).toMatch(uuidV4);
        expect(newIdempotencyKey()).not.toBe(key);
    });

    it('still makes a UUID v4 outside a secure context', () => {
        vi.stubGlobal('crypto', {
            getRandomValues: (bytes: Uint8Array) =>
                globalThis.crypto.getRandomValues(bytes),
        });

        expect(newIdempotencyKey()).toMatch(uuidV4);
    });
});

describe('commandVisit (TAC-01, TAC-03)', () => {
    it('sends the same key until the command succeeds', () => {
        const { visit, rotate } = setup();

        expect(visit.headers).toEqual({ [IDEMPOTENCY_HEADER]: 'key-1' });

        visit.onError({ name: 'Wajib diisi.' });
        expect(rotate).not.toHaveBeenCalled();

        visit.onSuccess();
        expect(rotate).toHaveBeenCalledOnce();
    });

    it('opens the conflict dialog on 409 and keeps the input on screen', () => {
        const { visit, rotate, conflict } = setup();

        const result = visit.onHttpException({
            status: 409,
            headers: {},
            data: {
                component: 'settings/profile',
                props: { errors: { lock_version: 'Sudah diubah.' } },
            },
        });

        expect(result).toBe(false);
        expect(conflict).toHaveBeenCalledWith('Sudah diubah.');
        expect(rotate).toHaveBeenCalledOnce();
    });

    it('reads the message of a 409 page sent as text', () => {
        const { visit, conflict } = setup();

        visit.onHttpException({
            status: 409,
            headers: {},
            data: JSON.stringify({
                props: { errors: { lock_version: 'Sudah diubah.' } },
            }),
        });

        expect(conflict).toHaveBeenCalledWith('Sudah diubah.');
    });

    it('opens the dialog without a server message when the 409 has none', () => {
        const { visit, conflict } = setup();

        expect(
            visit.onHttpException({
                status: 409,
                headers: {},
                data: '<html></html>',
            }),
        ).toBe(false);
        expect(conflict).toHaveBeenCalledWith(null);
    });

    it('opens the dialog for a conflict sent back to the form', () => {
        const { visit, conflict } = setup();

        visit.onError({ lock_version: 'Sudah diubah.' });

        expect(conflict).toHaveBeenCalledWith('Sudah diubah.');
    });

    it('leaves other failures to the error page', () => {
        const { visit, conflict, rotate } = setup();

        expect(
            visit.onHttpException({ status: 500, headers: {}, data: '' }),
        ).toBeUndefined();
        expect(conflict).not.toHaveBeenCalled();
        expect(rotate).not.toHaveBeenCalled();
    });
});
