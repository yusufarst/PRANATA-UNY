import { Laptop, Smartphone } from 'lucide-react';
import type { ReactNode } from 'react';
import { useTranslation } from 'react-i18next';
import { useDateTime } from '@/components/admin/users';
import { Badge } from '@/components/ui/badge';

/** A database session as the server shows it: the key is a keyed hash, never the session id (TASK-011 TAC-04). */
export type ActiveSession = {
    key: string;
    ip_address: string | null;
    user_agent: string | null;
    last_active_at: string;
    current: boolean;
};

const browsers: [RegExp, string][] = [
    [/Edg\//, 'Edge'],
    [/OPR\/|Opera/, 'Opera'],
    [/Firefox\//, 'Firefox'],
    [/Chrome\//, 'Chrome'],
    [/Safari\//, 'Safari'],
];

const systems: [RegExp, string][] = [
    [/Android/, 'Android'],
    [/iPhone|iPad|iPod/, 'iOS'],
    [/Windows/, 'Windows'],
    [/Mac OS X|Macintosh/, 'macOS'],
    [/CrOS/, 'ChromeOS'],
    [/Linux/, 'Linux'],
];

function nameOf(agent: string, names: [RegExp, string][]): string | null {
    return names.find(([pattern]) => pattern.test(agent))?.[1] ?? null;
}

/** A short device name, such as "Chrome · Windows", from the browser's user agent. */
export function describeDevice(agent: string | null): string | null {
    if (!agent) {
        return null;
    }

    const parts = [nameOf(agent, browsers), nameOf(agent, systems)].filter(
        (part): part is string => part !== null,
    );

    return parts.length > 0 ? parts.join(' · ') : null;
}

const mobile = /Android|iPhone|iPad|iPod|Mobile/;

export function SessionList({
    sessions,
    action,
}: {
    sessions: ActiveSession[];
    /** The action of one row, if this user may end that session. */
    action?: (session: ActiveSession) => ReactNode;
}) {
    const { t } = useTranslation();
    const dateTime = useDateTime();

    return (
        <ul className="grid gap-3" data-test="session-list">
            {sessions.map((session) => {
                const Icon = mobile.test(session.user_agent ?? '')
                    ? Smartphone
                    : Laptop;

                return (
                    <li
                        key={session.key}
                        className="flex flex-col gap-3 rounded-lg border border-border bg-card p-4 sm:flex-row sm:items-center sm:justify-between"
                    >
                        <div className="flex min-w-0 items-start gap-3">
                            <Icon
                                className="mt-0.5 size-5 shrink-0 text-muted-foreground"
                                aria-hidden="true"
                            />
                            <div className="grid min-w-0 gap-1">
                                <p className="flex flex-wrap items-center gap-2 font-medium text-foreground">
                                    {describeDevice(session.user_agent) ??
                                        t('account:sessions.unknown_device')}
                                    {session.current && (
                                        <Badge variant="secondary">
                                            {t('account:sessions.this_device')}
                                        </Badge>
                                    )}
                                </p>
                                <p className="text-sm break-words text-muted-foreground">
                                    {t('account:sessions.detail', {
                                        ip:
                                            session.ip_address ??
                                            t('account:sessions.unknown_ip'),
                                        time: dateTime(session.last_active_at),
                                    })}
                                </p>
                            </div>
                        </div>
                        {action?.(session)}
                    </li>
                );
            })}
        </ul>
    );
}
