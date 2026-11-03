import { Form, Head, Link, router } from '@inertiajs/react';
import {
    ArrowLeft,
    KeyRound,
    LogOut,
    Pencil,
    ShieldCheck,
    ShieldOff,
} from 'lucide-react';
import { useEffect, useState } from 'react';
import { useTranslation } from 'react-i18next';
import { toast } from 'sonner';
import UserAdministrationController from '@/actions/App/Modules/Identity/Http/Controllers/UserAdministrationController';
import type { Account, IdentityCheck } from '@/components/admin/users';
import {
    AccountStatusBadge,
    identityChecks,
    rememberedListQuery,
    selectClassName,
    useDateTime,
} from '@/components/admin/users';
import { ConflictDialog } from '@/components/app/ConflictDialog';
import { PageHeader } from '@/components/app/page-header';
import type { ActiveSession } from '@/components/app/session-list';
import { SessionList } from '@/components/app/session-list';
import InputError from '@/components/input-error';
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogTitle,
} from '@/components/ui/dialog';
import { Label } from '@/components/ui/label';
import { useCommand } from '@/lib/command';

type Props = {
    account: Account & {
        last_login_at: string | null;
        deactivated_at: string | null;
        created_at: string | null;
        /** A reset of this account needs a second administrator (TASK-011 TAC-03). */
        privileged: boolean;
    };
    pendingReset: {
        id: number;
        requested_by: string | null;
        requested_by_me: boolean;
        identity_check_method: IdentityCheck;
        identity_check_note: string | null;
        created_at: string | null;
        expires_at: string;
    } | null;
    sessions: ActiveSession[];
    /** Removing the TOTP always needs a second administrator (TASK-012 TAC-03). */
    twoFactor: {
        enabled: boolean;
        started: boolean;
        required: boolean;
        pendingReset: {
            id: number;
            requested_by: string | null;
            requested_by_me: boolean;
            identity_check_method: IdentityCheck;
            identity_check_note: string | null;
            created_at: string | null;
            expires_at: string;
        } | null;
        can: {
            request: boolean;
            approve: boolean;
            revoke: boolean;
        };
    };
    can: {
        update: boolean;
        suspend: boolean;
        reinstate: boolean;
        deactivate: boolean;
        reset: boolean;
        approveReset: boolean;
        revokeReset: boolean;
        revokeSessions: boolean;
    };
    /** Present only in the answer that issued it; it is never shown again. */
    issuedSecret?: IssuedSecret | null;
};

type IssuedSecret = {
    kind: 'activation' | 'temporary_password';
    value: string;
    expires_at: string;
};

export default function UserShow({
    account,
    pendingReset,
    sessions,
    twoFactor,
    can,
    issuedSecret,
}: Props) {
    const { t } = useTranslation();
    const dateTime = useDateTime();

    // The secret stays on screen for this visit only. It is taken out of the page kept in the browser history, so Back
    // does not show it again (TASK-011 TAC-02), and a later answer about the account no longer carries it.
    const [secret, setSecret] = useState<IssuedSecret | null>(
        issuedSecret ?? null,
    );
    const [secretVersion, setSecretVersion] = useState(account.lock_version);

    if (issuedSecret && issuedSecret !== secret) {
        setSecret(issuedSecret);
        setSecretVersion(account.lock_version);
    } else if (
        !issuedSecret &&
        secret &&
        account.lock_version !== secretVersion
    ) {
        setSecret(null);
    }

    useEffect(() => {
        if (issuedSecret) {
            router.replaceProp('issuedSecret', null);
        }
    }, [issuedSecret]);

    const [statusChange, setStatusChange] = useState<
        'suspend' | 'reinstate' | null
    >(null);
    const [deactivating, setDeactivating] = useState(false);
    const [resetting, setResetting] = useState(false);
    const [resettingTwoFactor, setResettingTwoFactor] = useState(false);
    const [endingSessions, setEndingSessions] = useState(false);
    const command = useCommand();
    const none = t('admin:users.fields.none');

    const copySecret = async (value: string) => {
        try {
            await navigator.clipboard.writeText(value);
            toast.success(t('admin:users.secret.copied'));
        } catch {
            // Without clipboard access the code stays on screen to read out.
        }
    };

    const details: [string, string][] = [
        [t('admin:users.fields.username'), account.username],
        [t('admin:users.fields.email'), account.email ?? none],
        [
            t('admin:users.fields.staff_identifier'),
            account.staff_identifier ?? none,
        ],
        [
            t('admin:users.fields.role_group'),
            t(`admin:users.role_group.${account.role_group}`),
        ],
        [
            t('admin:users.fields.locale'),
            t(`admin:users.locale.${account.locale}`),
        ],
        [
            t('admin:users.fields.must_change_password'),
            t(
                account.must_change_password
                    ? 'admin:users.fields.yes'
                    : 'admin:users.fields.no',
            ),
        ],
        [
            t('admin:users.fields.last_login_at'),
            dateTime(account.last_login_at) ?? t('admin:users.fields.never'),
        ],
        [
            t('admin:users.fields.created_at'),
            dateTime(account.created_at) ?? none,
        ],
        ...(account.deactivated_at
            ? [
                  [
                      t('admin:users.fields.deactivated_at'),
                      dateTime(account.deactivated_at) ?? none,
                  ] as [string, string],
              ]
            : []),
    ];

    return (
        <>
            <Head title={account.name} />
            <div className="mx-auto flex w-full max-w-4xl flex-1 flex-col gap-6 p-4 sm:p-6 lg:p-8">
                <div>
                    <Button variant="ghost" size="sm" asChild className="-ml-2">
                        <Link
                            href={UserAdministrationController.index({
                                query: rememberedListQuery(),
                            })}
                        >
                            <ArrowLeft aria-hidden="true" />
                            {t('admin:users.back_to_list')}
                        </Link>
                    </Button>
                </div>

                <PageHeader
                    title={account.name}
                    description={t('admin:users.detail.title')}
                    status={<AccountStatusBadge status={account.status} />}
                    secondaryActions={
                        (can.reset ||
                            can.suspend ||
                            can.reinstate ||
                            can.deactivate) && (
                            <>
                                {can.reinstate && (
                                    <Button
                                        variant="outline"
                                        onClick={() =>
                                            setStatusChange('reinstate')
                                        }
                                        data-test="reinstate-account-button"
                                    >
                                        {t('admin:users.reinstate')}
                                    </Button>
                                )}
                                {can.reset && (
                                    <Button
                                        variant="outline"
                                        onClick={() => setResetting(true)}
                                        data-test="reset-password-button"
                                    >
                                        <KeyRound aria-hidden="true" />
                                        {t('admin:users.reset.open')}
                                    </Button>
                                )}
                                {can.suspend && (
                                    <Button
                                        variant="outline"
                                        onClick={() =>
                                            setStatusChange('suspend')
                                        }
                                        data-test="suspend-account-button"
                                    >
                                        {t('admin:users.suspend')}
                                    </Button>
                                )}
                                {can.deactivate && (
                                    <Button
                                        variant="outline"
                                        className="text-danger"
                                        onClick={() => setDeactivating(true)}
                                        data-test="deactivate-account-button"
                                    >
                                        {t('admin:users.deactivate')}
                                    </Button>
                                )}
                            </>
                        )
                    }
                    action={
                        can.update && (
                            <Button asChild>
                                <Link
                                    href={UserAdministrationController.edit(
                                        account.public_id,
                                    )}
                                >
                                    <Pencil aria-hidden="true" />
                                    {t('admin:users.edit')}
                                </Link>
                            </Button>
                        )
                    }
                />

                {secret && (
                    <Alert data-test="issued-secret">
                        <KeyRound aria-hidden="true" />
                        <AlertTitle>
                            {t(`admin:users.secret.${secret.kind}_title`)}
                        </AlertTitle>
                        <AlertDescription className="gap-3">
                            <p>
                                {t(`admin:users.secret.${secret.kind}_intro`)}
                            </p>
                            <p
                                className="font-mono text-2xl font-semibold tracking-widest text-foreground select-all"
                                data-test="issued-secret-value"
                            >
                                {secret.value}
                            </p>
                            <p>
                                {t('admin:users.secret.expires', {
                                    time: dateTime(secret.expires_at),
                                })}
                            </p>
                            <p>
                                {t(
                                    `admin:users.secret.${secret.kind}_use_note`,
                                )}
                            </p>
                            <Button
                                type="button"
                                variant="secondary"
                                size="sm"
                                onClick={() => copySecret(secret.value)}
                            >
                                {t('admin:users.secret.copy')}
                            </Button>
                        </AlertDescription>
                    </Alert>
                )}

                {pendingReset && (
                    <Alert data-test="pending-reset">
                        <ShieldCheck aria-hidden="true" />
                        <AlertTitle>
                            {t('admin:users.reset.pending_title')}
                        </AlertTitle>
                        <AlertDescription className="gap-3">
                            <p>
                                {t('admin:users.reset.pending_detail', {
                                    name:
                                        pendingReset.requested_by ??
                                        t(
                                            'admin:users.reset.unknown_requester',
                                        ),
                                    time:
                                        dateTime(pendingReset.created_at) ??
                                        none,
                                    method: t(
                                        `admin:users.reset.methods.${pendingReset.identity_check_method}`,
                                    ),
                                })}
                            </p>
                            {pendingReset.identity_check_note && (
                                <p className="break-words">
                                    {pendingReset.identity_check_note}
                                </p>
                            )}
                            <p>
                                {t('admin:users.reset.pending_expires', {
                                    time: dateTime(pendingReset.expires_at),
                                })}
                            </p>
                            {pendingReset.requested_by_me && (
                                <p>{t('admin:users.reset.pending_own')}</p>
                            )}
                            {(can.approveReset || can.revokeReset) && (
                                <div className="flex flex-wrap gap-2">
                                    {can.approveReset && (
                                        <Form
                                            {...UserAdministrationController.approveReset.form(
                                                {
                                                    user: account.public_id,
                                                    reset: pendingReset.id,
                                                },
                                            )}
                                            {...command.visit}
                                            options={{ preserveScroll: true }}
                                        >
                                            {({ processing }) => (
                                                <Button
                                                    type="submit"
                                                    size="sm"
                                                    disabled={processing}
                                                    data-test="approve-reset-button"
                                                >
                                                    {t(
                                                        'admin:users.reset.approve',
                                                    )}
                                                </Button>
                                            )}
                                        </Form>
                                    )}
                                    {can.revokeReset && (
                                        <Form
                                            {...UserAdministrationController.revokeReset.form(
                                                {
                                                    user: account.public_id,
                                                    reset: pendingReset.id,
                                                },
                                            )}
                                            {...command.visit}
                                            options={{ preserveScroll: true }}
                                        >
                                            {({ processing }) => (
                                                <Button
                                                    type="submit"
                                                    size="sm"
                                                    variant="outline"
                                                    disabled={processing}
                                                    data-test="revoke-reset-button"
                                                >
                                                    {t(
                                                        'admin:users.reset.revoke',
                                                    )}
                                                </Button>
                                            )}
                                        </Form>
                                    )}
                                </div>
                            )}
                        </AlertDescription>
                    </Alert>
                )}

                {account.status === 'DEACTIVATED' && (
                    <Alert>
                        <AlertDescription>
                            {t('admin:users.detail.deactivated_note')}
                        </AlertDescription>
                    </Alert>
                )}

                {account.status === 'SUSPENDED' && (
                    <Alert>
                        <AlertDescription>
                            {t('admin:users.detail.suspended_note')}
                        </AlertDescription>
                    </Alert>
                )}

                <section
                    aria-labelledby="account-details"
                    className="rounded-lg border border-border bg-card p-4 shadow-card sm:p-6"
                >
                    <h2
                        id="account-details"
                        className="mb-4 text-lg font-semibold text-foreground"
                    >
                        {t('admin:users.detail.section')}
                    </h2>
                    <dl className="grid gap-4 sm:grid-cols-2">
                        {details.map(([label, value]) => (
                            <div key={label} className="grid gap-1">
                                <dt className="text-sm text-muted-foreground">
                                    {label}
                                </dt>
                                <dd className="break-words text-foreground">
                                    {value}
                                </dd>
                            </div>
                        ))}
                    </dl>
                </section>

                <section
                    aria-labelledby="account-two-factor"
                    className="rounded-lg border border-border bg-card p-4 shadow-card sm:p-6"
                    data-test="account-two-factor"
                >
                    <div className="mb-4 flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                        <div className="grid gap-1">
                            <h2
                                id="account-two-factor"
                                className="text-lg font-semibold text-foreground"
                            >
                                {t('admin:users.two_factor.section')}
                            </h2>
                            <p className="text-sm text-muted-foreground">
                                {t(
                                    twoFactor.enabled
                                        ? 'admin:users.two_factor.enabled'
                                        : twoFactor.started
                                          ? 'admin:users.two_factor.pending_setup'
                                          : 'admin:users.two_factor.disabled',
                                )}
                            </p>
                            {twoFactor.required && (
                                <p className="text-sm text-muted-foreground">
                                    {t('admin:users.two_factor.required')}
                                </p>
                            )}
                        </div>
                        {twoFactor.can.request && (
                            <Button
                                variant="outline"
                                onClick={() => setResettingTwoFactor(true)}
                                data-test="reset-two-factor-button"
                            >
                                <ShieldOff aria-hidden="true" />
                                {t('admin:users.two_factor.reset')}
                            </Button>
                        )}
                    </div>
                    {twoFactor.pendingReset && (
                        <Alert data-test="pending-two-factor-reset">
                            <ShieldCheck aria-hidden="true" />
                            <AlertTitle>
                                {t('admin:users.two_factor.pending_title')}
                            </AlertTitle>
                            <AlertDescription className="gap-3">
                                <p>
                                    {t('admin:users.reset.pending_detail', {
                                        name:
                                            twoFactor.pendingReset
                                                .requested_by ??
                                            t(
                                                'admin:users.reset.unknown_requester',
                                            ),
                                        time:
                                            dateTime(
                                                twoFactor.pendingReset
                                                    .created_at,
                                            ) ?? none,
                                        method: t(
                                            `admin:users.reset.methods.${twoFactor.pendingReset.identity_check_method}`,
                                        ),
                                    })}
                                </p>
                                {twoFactor.pendingReset.identity_check_note && (
                                    <p className="break-words">
                                        {
                                            twoFactor.pendingReset
                                                .identity_check_note
                                        }
                                    </p>
                                )}
                                <p>
                                    {t('admin:users.reset.pending_expires', {
                                        time: dateTime(
                                            twoFactor.pendingReset.expires_at,
                                        ),
                                    })}
                                </p>
                                {twoFactor.pendingReset.requested_by_me && (
                                    <p>
                                        {t(
                                            'admin:users.two_factor.pending_own',
                                        )}
                                    </p>
                                )}
                                {(twoFactor.can.approve ||
                                    twoFactor.can.revoke) && (
                                    <div className="flex flex-wrap gap-2">
                                        {twoFactor.can.approve && (
                                            <Form
                                                {...UserAdministrationController.approveTwoFactorReset.form(
                                                    {
                                                        user: account.public_id,
                                                        twoFactorReset:
                                                            twoFactor
                                                                .pendingReset
                                                                .id,
                                                    },
                                                )}
                                                {...command.visit}
                                                options={{
                                                    preserveScroll: true,
                                                }}
                                            >
                                                {({ processing }) => (
                                                    <Button
                                                        type="submit"
                                                        size="sm"
                                                        disabled={processing}
                                                        data-test="approve-two-factor-reset-button"
                                                    >
                                                        {t(
                                                            'admin:users.two_factor.approve',
                                                        )}
                                                    </Button>
                                                )}
                                            </Form>
                                        )}
                                        {twoFactor.can.revoke && (
                                            <Form
                                                {...UserAdministrationController.revokeTwoFactorReset.form(
                                                    {
                                                        user: account.public_id,
                                                        twoFactorReset:
                                                            twoFactor
                                                                .pendingReset
                                                                .id,
                                                    },
                                                )}
                                                {...command.visit}
                                                options={{
                                                    preserveScroll: true,
                                                }}
                                            >
                                                {({ processing }) => (
                                                    <Button
                                                        type="submit"
                                                        size="sm"
                                                        variant="outline"
                                                        disabled={processing}
                                                        data-test="revoke-two-factor-reset-button"
                                                    >
                                                        {t(
                                                            'admin:users.two_factor.revoke',
                                                        )}
                                                    </Button>
                                                )}
                                            </Form>
                                        )}
                                    </div>
                                )}
                            </AlertDescription>
                        </Alert>
                    )}
                </section>

                <section
                    aria-labelledby="account-sessions"
                    className="rounded-lg border border-border bg-card p-4 shadow-card sm:p-6"
                    data-test="account-sessions"
                >
                    <div className="mb-4 flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                        <div className="grid gap-1">
                            <h2
                                id="account-sessions"
                                className="text-lg font-semibold text-foreground"
                            >
                                {t('admin:users.sessions.section')}
                            </h2>
                            <p className="text-sm text-muted-foreground">
                                {t('admin:users.sessions.intro')}
                            </p>
                        </div>
                        {can.revokeSessions && (
                            <Button
                                variant="outline"
                                onClick={() => setEndingSessions(true)}
                                data-test="revoke-sessions-button"
                            >
                                <LogOut aria-hidden="true" />
                                {t('admin:users.sessions.revoke')}
                            </Button>
                        )}
                    </div>
                    {sessions.length === 0 ? (
                        <p className="text-sm text-muted-foreground">
                            {t('admin:users.sessions.empty')}
                        </p>
                    ) : (
                        <SessionList sessions={sessions} />
                    )}
                </section>
            </div>

            <Dialog open={endingSessions} onOpenChange={setEndingSessions}>
                <DialogContent>
                    <DialogTitle>
                        {t('admin:users.sessions.confirm_title')}
                    </DialogTitle>
                    <DialogDescription>
                        {t('admin:users.sessions.confirm_description', {
                            count: sessions.length,
                        })}
                    </DialogDescription>
                    <Form
                        {...UserAdministrationController.revokeSessions.form(
                            account.public_id,
                        )}
                        {...command.visit}
                        onSuccess={() => {
                            command.visit.onSuccess();
                            setEndingSessions(false);
                        }}
                        options={{ preserveScroll: true }}
                        className="grid gap-4"
                    >
                        {({ processing, errors }) => (
                            <>
                                <InputError
                                    message={
                                        errors._idempotency ?? errors._domain
                                    }
                                />
                                <DialogFooter className="gap-2">
                                    <Button
                                        type="button"
                                        variant="secondary"
                                        onClick={() => setEndingSessions(false)}
                                    >
                                        {t('common:actions.cancel')}
                                    </Button>
                                    <Button
                                        type="submit"
                                        variant="destructive"
                                        disabled={processing}
                                        data-test="confirm-revoke-sessions-button"
                                    >
                                        {t('admin:users.sessions.confirm')}
                                    </Button>
                                </DialogFooter>
                            </>
                        )}
                    </Form>
                </DialogContent>
            </Dialog>

            <Dialog open={deactivating} onOpenChange={setDeactivating}>
                <DialogContent>
                    <DialogTitle>
                        {t('admin:users.deactivation.title')}
                    </DialogTitle>
                    <DialogDescription>
                        {t('admin:users.deactivation.description')}
                    </DialogDescription>
                    <Form
                        {...UserAdministrationController.deactivate.form(
                            account.public_id,
                        )}
                        {...command.visit}
                        onSuccess={() => {
                            command.visit.onSuccess();
                            setDeactivating(false);
                        }}
                        options={{ preserveScroll: true }}
                        className="grid gap-4"
                    >
                        {({ processing, errors }) => (
                            <>
                                <InputError
                                    message={
                                        errors._idempotency ?? errors._domain
                                    }
                                />
                                <input
                                    type="hidden"
                                    name="lock_version"
                                    value={account.lock_version}
                                />
                                <div className="grid gap-2">
                                    <Label htmlFor="reason">
                                        {t('admin:users.deactivation.reason')}
                                    </Label>
                                    <textarea
                                        id="reason"
                                        name="reason"
                                        required
                                        minLength={5}
                                        maxLength={500}
                                        rows={3}
                                        placeholder={t(
                                            'admin:users.deactivation.reason_placeholder',
                                        )}
                                        aria-invalid={!!errors.reason}
                                        className="w-full min-w-0 rounded-md border border-input bg-transparent px-3 py-2 text-base shadow-xs outline-none placeholder:text-muted-foreground focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50 aria-invalid:border-destructive md:text-sm"
                                    />
                                    <InputError message={errors.reason} />
                                </div>
                                <DialogFooter className="gap-2">
                                    <Button
                                        type="button"
                                        variant="secondary"
                                        onClick={() => setDeactivating(false)}
                                    >
                                        {t('common:actions.cancel')}
                                    </Button>
                                    <Button
                                        type="submit"
                                        variant="destructive"
                                        disabled={processing}
                                        data-test="confirm-deactivate-button"
                                    >
                                        {t('admin:users.deactivation.confirm')}
                                    </Button>
                                </DialogFooter>
                            </>
                        )}
                    </Form>
                </DialogContent>
            </Dialog>

            <Dialog
                open={statusChange !== null}
                onOpenChange={(open) => !open && setStatusChange(null)}
            >
                <DialogContent>
                    <DialogTitle>
                        {t(
                            statusChange === 'reinstate'
                                ? 'admin:users.reinstatement.title'
                                : 'admin:users.suspension.title',
                        )}
                    </DialogTitle>
                    <DialogDescription>
                        {t(
                            statusChange === 'reinstate'
                                ? 'admin:users.reinstatement.description'
                                : 'admin:users.suspension.description',
                        )}
                    </DialogDescription>
                    <Form
                        {...(statusChange === 'reinstate'
                            ? UserAdministrationController.reinstate.form(
                                  account.public_id,
                              )
                            : UserAdministrationController.suspend.form(
                                  account.public_id,
                              ))}
                        {...command.visit}
                        onSuccess={() => {
                            command.visit.onSuccess();
                            setStatusChange(null);
                        }}
                        options={{ preserveScroll: true }}
                        className="grid gap-4"
                    >
                        {({ processing, errors }) => (
                            <>
                                <InputError
                                    message={
                                        errors._idempotency ?? errors._domain
                                    }
                                />
                                <input
                                    type="hidden"
                                    name="lock_version"
                                    value={account.lock_version}
                                />
                                <div className="grid gap-2">
                                    <Label htmlFor="status_reason">
                                        {t('admin:users.deactivation.reason')}
                                    </Label>
                                    <textarea
                                        id="status_reason"
                                        name="reason"
                                        required
                                        minLength={5}
                                        maxLength={500}
                                        rows={3}
                                        placeholder={t(
                                            statusChange === 'reinstate'
                                                ? 'admin:users.reinstatement.reason_placeholder'
                                                : 'admin:users.suspension.reason_placeholder',
                                        )}
                                        aria-invalid={!!errors.reason}
                                        className="w-full min-w-0 rounded-md border border-input bg-transparent px-3 py-2 text-base shadow-xs outline-none placeholder:text-muted-foreground focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50 aria-invalid:border-destructive md:text-sm"
                                    />
                                    <InputError message={errors.reason} />
                                </div>
                                <DialogFooter className="gap-2">
                                    <Button
                                        type="button"
                                        variant="secondary"
                                        onClick={() => setStatusChange(null)}
                                    >
                                        {t('common:actions.cancel')}
                                    </Button>
                                    <Button
                                        type="submit"
                                        variant={
                                            statusChange === 'reinstate'
                                                ? 'default'
                                                : 'destructive'
                                        }
                                        disabled={processing}
                                        data-test="confirm-status-change-button"
                                    >
                                        {t(
                                            statusChange === 'reinstate'
                                                ? 'admin:users.reinstatement.confirm'
                                                : 'admin:users.suspension.confirm',
                                        )}
                                    </Button>
                                </DialogFooter>
                            </>
                        )}
                    </Form>
                </DialogContent>
            </Dialog>

            <Dialog open={resetting} onOpenChange={setResetting}>
                <DialogContent>
                    <DialogTitle>{t('admin:users.reset.title')}</DialogTitle>
                    <DialogDescription>
                        {t(
                            account.privileged
                                ? 'admin:users.reset.privileged_description'
                                : 'admin:users.reset.description',
                        )}
                    </DialogDescription>
                    <Form
                        {...UserAdministrationController.requestReset.form(
                            account.public_id,
                        )}
                        {...command.visit}
                        onSuccess={() => {
                            command.visit.onSuccess();
                            setResetting(false);
                        }}
                        options={{ preserveScroll: true }}
                        className="grid gap-4"
                    >
                        {({ processing, errors }) => (
                            <>
                                <InputError
                                    message={
                                        errors._idempotency ?? errors._domain
                                    }
                                />
                                <IdentityCheckFields errors={errors} />
                                <DialogFooter className="gap-2">
                                    <Button
                                        type="button"
                                        variant="secondary"
                                        onClick={() => setResetting(false)}
                                    >
                                        {t('common:actions.cancel')}
                                    </Button>
                                    <Button
                                        type="submit"
                                        disabled={processing}
                                        data-test="confirm-reset-button"
                                    >
                                        {t(
                                            account.privileged
                                                ? 'admin:users.reset.confirm_privileged'
                                                : 'admin:users.reset.confirm',
                                        )}
                                    </Button>
                                </DialogFooter>
                            </>
                        )}
                    </Form>
                </DialogContent>
            </Dialog>
            <Dialog
                open={resettingTwoFactor}
                onOpenChange={setResettingTwoFactor}
            >
                <DialogContent>
                    <DialogTitle>
                        {t('admin:users.two_factor.title')}
                    </DialogTitle>
                    <DialogDescription>
                        {t('admin:users.two_factor.description')}
                    </DialogDescription>
                    <Form
                        {...UserAdministrationController.requestTwoFactorReset.form(
                            account.public_id,
                        )}
                        {...command.visit}
                        onSuccess={() => {
                            command.visit.onSuccess();
                            setResettingTwoFactor(false);
                        }}
                        options={{ preserveScroll: true }}
                        className="grid gap-4"
                    >
                        {({ processing, errors }) => (
                            <>
                                <InputError
                                    message={
                                        errors._idempotency ?? errors._domain
                                    }
                                />
                                <IdentityCheckFields
                                    idPrefix="two_factor_"
                                    errors={errors}
                                />
                                <DialogFooter className="gap-2">
                                    <Button
                                        type="button"
                                        variant="secondary"
                                        onClick={() =>
                                            setResettingTwoFactor(false)
                                        }
                                    >
                                        {t('common:actions.cancel')}
                                    </Button>
                                    <Button
                                        type="submit"
                                        disabled={processing}
                                        data-test="confirm-two-factor-reset-button"
                                    >
                                        {t('admin:users.two_factor.confirm')}
                                    </Button>
                                </DialogFooter>
                            </>
                        )}
                    </Form>
                </DialogContent>
            </Dialog>
            <ConflictDialog command={command} />
        </>
    );
}

/**
 * How the administrator checked who asked, for a password or a 2FA reset (SECURITY_MODEL section 3.1).
 */
function IdentityCheckFields({
    idPrefix = '',
    errors,
}: {
    idPrefix?: string;
    errors: Partial<Record<string, string>>;
}) {
    const { t } = useTranslation();

    return (
        <>
            <div className="grid gap-2">
                <Label htmlFor={`${idPrefix}identity_check_method`}>
                    {t('admin:users.reset.method')}
                </Label>
                <select
                    id={`${idPrefix}identity_check_method`}
                    name="identity_check_method"
                    required
                    defaultValue=""
                    aria-invalid={!!errors.identity_check_method}
                    className={selectClassName}
                >
                    <option value="" disabled>
                        {t('admin:users.reset.method_placeholder')}
                    </option>
                    {identityChecks.map((method) => (
                        <option key={method} value={method}>
                            {t(`admin:users.reset.methods.${method}`)}
                        </option>
                    ))}
                </select>
                <InputError message={errors.identity_check_method} />
            </div>
            <div className="grid gap-2">
                <Label htmlFor={`${idPrefix}identity_check_note`}>
                    {t('admin:users.reset.note')}
                </Label>
                <textarea
                    id={`${idPrefix}identity_check_note`}
                    name="identity_check_note"
                    maxLength={500}
                    rows={3}
                    placeholder={t('admin:users.reset.note_placeholder')}
                    aria-invalid={!!errors.identity_check_note}
                    className="w-full min-w-0 rounded-md border border-input bg-transparent px-3 py-2 text-base shadow-xs outline-none placeholder:text-muted-foreground focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50 aria-invalid:border-destructive md:text-sm"
                />
                <InputError message={errors.identity_check_note} />
            </div>
        </>
    );
}

UserShow.layout = {
    breadcrumbs: [
        {
            title: 'admin:users.title',
            href: UserAdministrationController.index(),
        },
    ],
};
