import { Form, Head, Link } from '@inertiajs/react';
import { ArrowLeft, ShieldCheck } from 'lucide-react';
import { useState } from 'react';
import { useTranslation } from 'react-i18next';
import GrantController from '@/actions/App/Modules/Access/Http/Controllers/GrantController';
import UserAdministrationController from '@/actions/App/Modules/Identity/Http/Controllers/UserAdministrationController';
import { useDateTime } from '@/components/admin/users';
import { ConflictDialog } from '@/components/app/ConflictDialog';
import { PageHeader } from '@/components/app/page-header';
import { StatusBadge } from '@/components/app/status-badge';
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
import type { GrantSummary } from '@/lib/access';
import { grantStatusCategory, grantSubject, scopeTarget } from '@/lib/access';
import { useCommand } from '@/lib/command';

type Props = {
    grant: GrantSummary & {
        capabilities: string[];
        reason: string;
        granted_by: string | null;
        approved_by: string | null;
        approved_at: string | null;
        revoked_by: string | null;
        revoked_at: string | null;
        revoke_reason: string | null;
        lock_version: number;
    };
    canOpenUser: boolean;
    can: { approve: boolean; revoke: boolean };
    /** A pending grant this account gave or receives: another administrator approves it (TAC-03). */
    approvalBlocked: boolean;
};

export default function GrantShow({
    grant,
    canOpenUser,
    can,
    approvalBlocked,
}: Props) {
    const { t } = useTranslation();
    const dateTime = useDateTime();
    const command = useCommand();
    const [revoking, setRevoking] = useState(false);
    const none = t('admin:access.fields.none');
    const target = scopeTarget(grant);

    const details: [string, string][] = [
        [t('admin:access.fields.subject'), grantSubject(grant)],
        [
            t('admin:access.fields.scope'),
            target === null
                ? t(`admin:access.scope.${grant.scope_type}`)
                : `${t(`admin:access.scope.${grant.scope_type}`)}: ${target}`,
        ],
        [t('admin:access.fields.effective_from'), grant.effective_from],
        [t('admin:access.fields.effective_to'), grant.effective_to ?? none],
        [t('admin:access.fields.reason'), grant.reason],
        [t('admin:access.fields.granted_by'), grant.granted_by ?? none],
        [
            t('admin:access.fields.created_at'),
            dateTime(grant.created_at) ?? none,
        ],
        ...(grant.approved_at
            ? [
                  [
                      t('admin:access.fields.approved_by'),
                      `${grant.approved_by ?? none} · ${dateTime(grant.approved_at)}`,
                  ] as [string, string],
              ]
            : []),
        ...(grant.revoked_at
            ? [
                  [
                      t('admin:access.fields.revoked_by'),
                      `${grant.revoked_by ?? none} · ${dateTime(grant.revoked_at)}`,
                  ] as [string, string],
                  [
                      t('admin:access.fields.revoke_reason'),
                      grant.revoke_reason ?? none,
                  ] as [string, string],
              ]
            : []),
    ];

    return (
        <>
            <Head title={grant.user.name} />
            <div className="mx-auto flex w-full max-w-4xl flex-1 flex-col gap-6 p-4 sm:p-6 lg:p-8">
                <div>
                    <Button variant="ghost" size="sm" asChild className="-ml-2">
                        <Link href={GrantController.index()}>
                            <ArrowLeft aria-hidden="true" />
                            {t('admin:access.back_to_list')}
                        </Link>
                    </Button>
                </div>

                <PageHeader
                    title={grant.user.name}
                    description={t('admin:access.detail.title', {
                        username: grant.user.username,
                    })}
                    status={
                        <StatusBadge
                            category={grantStatusCategory[grant.status]}
                            label={t(`admin:access.status.${grant.status}`)}
                        />
                    }
                    secondaryActions={
                        (can.revoke || canOpenUser) && (
                            <>
                                {canOpenUser && (
                                    <Button variant="outline" asChild>
                                        <Link
                                            href={UserAdministrationController.show(
                                                grant.user.public_id,
                                            )}
                                        >
                                            {t('admin:access.open_user')}
                                        </Link>
                                    </Button>
                                )}
                                {can.revoke && (
                                    <Button
                                        variant="outline"
                                        className="text-danger"
                                        onClick={() => setRevoking(true)}
                                        data-test="revoke-grant-button"
                                    >
                                        {t('admin:access.revoke.open')}
                                    </Button>
                                )}
                            </>
                        )
                    }
                    action={
                        can.approve && (
                            <Form
                                {...GrantController.approve.form(
                                    grant.public_id,
                                )}
                                {...command.visit}
                                options={{ preserveScroll: true }}
                            >
                                {({ processing }) => (
                                    <>
                                        <input
                                            type="hidden"
                                            name="lock_version"
                                            value={grant.lock_version}
                                        />
                                        <Button
                                            type="submit"
                                            disabled={processing}
                                            data-test="approve-grant-button"
                                        >
                                            <ShieldCheck aria-hidden="true" />
                                            {t('admin:access.approve')}
                                        </Button>
                                    </>
                                )}
                            </Form>
                        )
                    }
                />

                {grant.status === 'PENDING_APPROVAL' && (
                    <Alert data-test="pending-grant">
                        <ShieldCheck aria-hidden="true" />
                        <AlertTitle>{t('admin:access.pending_title')}</AlertTitle>
                        <AlertDescription>
                            {t(
                                approvalBlocked
                                    ? 'admin:access.pending_other'
                                    : 'admin:access.pending_description',
                            )}
                        </AlertDescription>
                    </Alert>
                )}

                <section
                    aria-labelledby="grant-details"
                    className="rounded-lg border border-border bg-card p-4 shadow-card sm:p-6"
                >
                    <h2
                        id="grant-details"
                        className="mb-4 text-lg font-semibold text-foreground"
                    >
                        {t('admin:access.detail.section')}
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
                    aria-labelledby="grant-capabilities"
                    className="rounded-lg border border-border bg-card p-4 shadow-card sm:p-6"
                >
                    <h2
                        id="grant-capabilities"
                        className="mb-4 text-lg font-semibold text-foreground"
                    >
                        {t('admin:access.detail.capabilities')}
                    </h2>
                    <ul className="grid gap-2 font-mono text-sm sm:grid-cols-2">
                        {grant.capabilities.map((capability) => (
                            <li key={capability} className="break-words">
                                {capability}
                            </li>
                        ))}
                    </ul>
                </section>
            </div>

            <Dialog open={revoking} onOpenChange={setRevoking}>
                <DialogContent>
                    <DialogTitle>{t('admin:access.revoke.title')}</DialogTitle>
                    <DialogDescription>
                        {t('admin:access.revoke.description')}
                    </DialogDescription>
                    <Form
                        {...GrantController.revoke.form(grant.public_id)}
                        {...command.visit}
                        onSuccess={() => {
                            command.visit.onSuccess();
                            setRevoking(false);
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
                                    value={grant.lock_version}
                                />
                                <div className="grid gap-2">
                                    <Label htmlFor="revoke_reason">
                                        {t('admin:access.fields.revoke_reason')}
                                    </Label>
                                    <textarea
                                        id="revoke_reason"
                                        name="reason"
                                        required
                                        minLength={5}
                                        maxLength={500}
                                        rows={3}
                                        placeholder={t(
                                            'admin:access.revoke.reason_placeholder',
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
                                        onClick={() => setRevoking(false)}
                                    >
                                        {t('common:actions.cancel')}
                                    </Button>
                                    <Button
                                        type="submit"
                                        variant="destructive"
                                        disabled={processing}
                                        data-test="confirm-revoke-grant-button"
                                    >
                                        {t('admin:access.revoke.confirm')}
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

GrantShow.layout = {
    breadcrumbs: [
        {
            title: 'admin:access.title',
            href: GrantController.index(),
        },
    ],
};
