import { Form, Head, Link } from '@inertiajs/react';
import { ArrowLeft, KeyRound, Pencil } from 'lucide-react';
import { useState } from 'react';
import { useTranslation } from 'react-i18next';
import { toast } from 'sonner';
import UserAdministrationController from '@/actions/App/Modules/Identity/Http/Controllers/UserAdministrationController';
import type { Account } from '@/components/admin/users';
import {
    AccountStatusBadge,
    rememberedListQuery,
    useDateTime,
} from '@/components/admin/users';
import { ConflictDialog } from '@/components/app/ConflictDialog';
import { PageHeader } from '@/components/app/page-header';
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
    };
    can: { update: boolean; deactivate: boolean };
    /** Present only in the answer that issued it; it is never shown again. */
    issuedSecret?: {
        kind: 'activation';
        value: string;
        expires_at: string;
    };
};

export default function UserShow({ account, can, issuedSecret }: Props) {
    const { t } = useTranslation();
    const dateTime = useDateTime();
    const [deactivating, setDeactivating] = useState(false);
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
        [t('admin:users.fields.created_at'), dateTime(account.created_at) ?? none],
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
                        can.deactivate && (
                            <Button
                                variant="outline"
                                className="text-danger"
                                onClick={() => setDeactivating(true)}
                                data-test="deactivate-account-button"
                            >
                                {t('admin:users.deactivate')}
                            </Button>
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

                {issuedSecret && (
                    <Alert data-test="issued-secret">
                        <KeyRound aria-hidden="true" />
                        <AlertTitle>
                            {t('admin:users.secret.activation_title')}
                        </AlertTitle>
                        <AlertDescription className="gap-3">
                            <p>{t('admin:users.secret.activation_intro')}</p>
                            <p
                                className="font-mono text-2xl font-semibold tracking-widest text-foreground select-all"
                                data-test="issued-secret-value"
                            >
                                {issuedSecret.value}
                            </p>
                            <p>
                                {t('admin:users.secret.expires', {
                                    time: dateTime(issuedSecret.expires_at),
                                })}
                            </p>
                            <p>{t('admin:users.secret.use_note')}</p>
                            <Button
                                type="button"
                                variant="secondary"
                                size="sm"
                                onClick={() => copySecret(issuedSecret.value)}
                            >
                                {t('admin:users.secret.copy')}
                            </Button>
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
            </div>

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
            <ConflictDialog command={command} />
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
