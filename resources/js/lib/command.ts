import type { Errors, HttpExceptionResponse } from '@inertiajs/core';
import { router } from '@inertiajs/react';
import { useState } from 'react';
import { useTranslation } from 'react-i18next';

export const IDEMPOTENCY_HEADER = 'X-Idempotency-Key';

/**
 * A random UUID v4. crypto.randomUUID exists only in a secure context, so the fallback builds one from random bytes.
 */
export function newIdempotencyKey(): string {
    if (typeof crypto.randomUUID === 'function') {
        return crypto.randomUUID();
    }

    const bytes = crypto.getRandomValues(new Uint8Array(16));
    bytes[6] = (bytes[6] & 0x0f) | 0x40;
    bytes[8] = (bytes[8] & 0x3f) | 0x80;
    const hex = Array.from(bytes, (byte) =>
        byte.toString(16).padStart(2, '0'),
    ).join('');

    return `${hex.slice(0, 8)}-${hex.slice(8, 12)}-${hex.slice(12, 16)}-${hex.slice(16, 20)}-${hex.slice(20)}`;
}

/**
 * The visit props of a command, to spread on an Inertia Form or pass to a router or useForm visit.
 */
export type CommandVisit = {
    headers: Record<string, string>;
    onSuccess: () => void;
    onError: (errors: Errors) => void;
    onHttpException: (response: HttpExceptionResponse) => boolean | void;
};

export type Command = {
    visit: CommandVisit;
    /** The conflict message while the conflict dialog is open, otherwise null. */
    conflict: string | null;
    /** Close the dialog and keep the input on screen. */
    dismissConflict: () => void;
    /** Drop the input and show the page again with the latest data. */
    reloadLatest: () => void;
};

/**
 * The errors.lock_version of the page a 409 answered with, if it has one.
 */
function lockVersionError(data: HttpExceptionResponse['data']): string | null {
    let page: unknown = data;

    if (typeof data === 'string') {
        try {
            page = JSON.parse(data);
        } catch {
            return null;
        }
    }

    const error = (page as { props?: { errors?: { lock_version?: unknown } } })
        ?.props?.errors?.lock_version;

    return typeof error === 'string' ? error : null;
}

/**
 * The visit callbacks of a command with this key: rotate takes a new key, conflict opens the dialog with the server message.
 */
export function commandVisit(
    key: string,
    rotate: () => void,
    conflict: (message: string | null) => void,
): CommandVisit {
    const open = (message: string | null) => {
        rotate();
        conflict(message);
    };

    return {
        headers: { [IDEMPOTENCY_HEADER]: key },
        onSuccess: rotate,
        onError: (errors) => {
            // A conflict without a page of its own goes back to the form with errors.lock_version.
            if (typeof errors.lock_version === 'string') {
                open(errors.lock_version);
            }
        },
        onHttpException: (response) => {
            if (response.status !== 409) {
                return;
            }

            open(lockVersionError(response.data));

            // The page of the response is not rendered, so the input stays on screen under the dialog.
            return false;
        },
    };
}

/**
 * A material command (COMMAND_AND_API_CONTRACTS sections 4 and 5).
 *
 * Every submission carries an idempotency key, so a double click or a resend after a lost answer has one effect. The key
 * stays the same until the command succeeds or conflicts, because a failed command changed nothing and may be sent again.
 * A version conflict (409) opens the conflict dialog over the input, and the user chooses to load the latest data (IX-002).
 */
export function useCommand(): Command {
    const { t } = useTranslation();
    const [key, setKey] = useState(newIdempotencyKey);
    const [conflict, setConflict] = useState<string | null>(null);

    return {
        visit: commandVisit(
            key,
            () => setKey(newIdempotencyKey()),
            (message) =>
                setConflict(message ?? t('common:conflict.description')),
        ),
        conflict,
        dismissConflict: () => setConflict(null),
        reloadLatest: () => {
            setConflict(null);
            router.visit(window.location.href, {
                preserveScroll: true,
                preserveState: false,
                replace: true,
            });
        },
    };
}
