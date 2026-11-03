import { Form, Head, usePage } from '@inertiajs/react';
import { useTranslation } from 'react-i18next';
import SessionController from '@/actions/App/Modules/Identity/Http/Controllers/SessionController';
import { ConflictDialog } from '@/components/app/ConflictDialog';
import type { ActiveSession } from '@/components/app/session-list';
import { SessionList } from '@/components/app/session-list';
import Heading from '@/components/heading';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { useCommand } from '@/lib/command';

type Props = {
    sessions: ActiveSession[];
};

// PG-007 Akun & keamanan: the active sessions of the own account (TASK-011 TAC-04).
export default function Sessions({ sessions }: Props) {
    const { t } = useTranslation();
    const command = useCommand();
    const { errors } = usePage<{ errors: Record<string, string> }>().props;
    const others = sessions.filter((session) => !session.current).length;

    return (
        <>
            <Head title={t('account:sessions.page_title')} />

            <h1 className="sr-only">{t('account:sessions.page_title')}</h1>

            <div className="space-y-6">
                <Heading
                    variant="small"
                    title={t('account:sessions.heading')}
                    description={t('account:sessions.intro')}
                />

                <InputError message={errors._idempotency ?? errors._domain} />

                {sessions.length === 0 ? (
                    <p className="text-sm text-muted-foreground">
                        {t('account:sessions.empty')}
                    </p>
                ) : (
                    <SessionList
                        sessions={sessions}
                        action={(session) =>
                            !session.current && (
                                <Form
                                    {...SessionController.revoke.form(
                                        session.key,
                                    )}
                                    {...command.visit}
                                    options={{ preserveScroll: true }}
                                >
                                    {({ processing }) => (
                                        <Button
                                            type="submit"
                                            variant="outline"
                                            size="sm"
                                            disabled={processing}
                                            data-test="revoke-session-button"
                                        >
                                            {t('account:sessions.revoke')}
                                        </Button>
                                    )}
                                </Form>
                            )
                        }
                    />
                )}

                {others > 0 && (
                    <Form
                        {...SessionController.revokeOthers.form()}
                        {...command.visit}
                        options={{ preserveScroll: true }}
                        className="grid gap-2"
                    >
                        {({ processing }) => (
                            <>
                                <p className="text-sm text-muted-foreground">
                                    {t('account:sessions.revoke_others_note')}
                                </p>
                                <div>
                                    <Button
                                        type="submit"
                                        variant="outline"
                                        disabled={processing}
                                        data-test="revoke-other-sessions-button"
                                    >
                                        {t('account:sessions.revoke_others')}
                                    </Button>
                                </div>
                            </>
                        )}
                    </Form>
                )}
            </div>
            <ConflictDialog command={command} />
        </>
    );
}

Sessions.layout = {
    breadcrumbs: [
        {
            title: 'account:sessions.page_title',
            href: SessionController.index(),
        },
    ],
};
