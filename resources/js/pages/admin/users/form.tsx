import { Form, Head, Link } from '@inertiajs/react';
import { useTranslation } from 'react-i18next';
import UserAdministrationController from '@/actions/App/Modules/Identity/Http/Controllers/UserAdministrationController';
import type { Account } from '@/components/admin/users';
import {
    creatableRoleGroups,
    rememberedListQuery,
    selectClassName,
} from '@/components/admin/users';
import { ConflictDialog } from '@/components/app/ConflictDialog';
import { PageHeader } from '@/components/app/page-header';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { useCommand } from '@/lib/command';

type Props = {
    /** The account to change, or null for a new one. */
    account: Account | null;
};

export default function UserForm({ account }: Props) {
    const { t } = useTranslation();
    const command = useCommand();
    const creating = account === null;
    const title = t(
        creating
            ? 'admin:users.form.create_title'
            : 'admin:users.form.edit_title',
    );

    // Cancel goes back where the form was opened from: the detail, or for a new account the list.
    const cancelHref = creating
        ? UserAdministrationController.index({ query: rememberedListQuery() })
        : UserAdministrationController.show(account.public_id);

    return (
        <>
            <Head title={title} />
            <div className="mx-auto flex w-full max-w-3xl flex-1 flex-col gap-6 p-4 sm:p-6 lg:p-8">
                <PageHeader
                    title={title}
                    description={t(
                        creating
                            ? 'admin:users.form.create_intro'
                            : 'admin:users.form.edit_intro',
                    )}
                />

                <Form
                    {...(creating
                        ? UserAdministrationController.store.form()
                        : UserAdministrationController.update.form(
                              account.public_id,
                          ))}
                    {...command.visit}
                    options={{ preserveScroll: true }}
                    className="flex flex-col gap-6 rounded-lg border border-border bg-card p-4 shadow-card sm:p-6"
                >
                    {({ processing, errors }) => (
                        <>
                            <InputError
                                message={errors._idempotency ?? errors._domain}
                            />

                            {!creating && (
                                <input
                                    type="hidden"
                                    name="lock_version"
                                    value={account.lock_version}
                                />
                            )}

                            {creating && (
                                <div className="grid gap-2">
                                    <Label htmlFor="role_group">
                                        {t('admin:users.fields.role_group')}
                                    </Label>
                                    <select
                                        id="role_group"
                                        name="role_group"
                                        required
                                        defaultValue="INTERNAL"
                                        aria-describedby="role_group-hint"
                                        aria-invalid={!!errors.role_group}
                                        className={selectClassName}
                                    >
                                        {creatableRoleGroups.map((group) => (
                                            <option key={group} value={group}>
                                                {t(
                                                    `admin:users.role_group.${group}`,
                                                )}
                                            </option>
                                        ))}
                                    </select>
                                    <p
                                        id="role_group-hint"
                                        className="text-sm text-muted-foreground"
                                    >
                                        {t('admin:users.fields.role_group_hint')}
                                    </p>
                                    <InputError message={errors.role_group} />
                                </div>
                            )}

                            <div className="grid gap-2">
                                <Label htmlFor="name">
                                    {t('admin:users.fields.name')}
                                </Label>
                                <Input
                                    id="name"
                                    name="name"
                                    required
                                    maxLength={150}
                                    autoComplete="off"
                                    defaultValue={account?.name}
                                    aria-invalid={!!errors.name}
                                />
                                <InputError message={errors.name} />
                            </div>

                            <div className="grid gap-2">
                                <Label htmlFor="username">
                                    {t('admin:users.fields.username')}
                                </Label>
                                <Input
                                    id="username"
                                    name="username"
                                    required
                                    maxLength={60}
                                    pattern="[A-Za-z0-9._]+"
                                    autoComplete="off"
                                    spellCheck={false}
                                    defaultValue={account?.username}
                                    aria-invalid={!!errors.username}
                                />
                                <InputError message={errors.username} />
                            </div>

                            <div className="grid gap-2">
                                <Label htmlFor="email">
                                    {t('admin:users.fields.email')}
                                </Label>
                                <Input
                                    id="email"
                                    name="email"
                                    type="email"
                                    maxLength={255}
                                    autoComplete="off"
                                    defaultValue={account?.email ?? ''}
                                    aria-describedby="email-hint"
                                    aria-invalid={!!errors.email}
                                />
                                <p
                                    id="email-hint"
                                    className="text-sm text-muted-foreground"
                                >
                                    {t('admin:users.fields.email_hint')}
                                </p>
                                <InputError message={errors.email} />
                            </div>

                            <div className="grid gap-2">
                                <Label htmlFor="staff_identifier">
                                    {t('admin:users.fields.staff_identifier')}
                                </Label>
                                <Input
                                    id="staff_identifier"
                                    name="staff_identifier"
                                    maxLength={40}
                                    autoComplete="off"
                                    defaultValue={
                                        account?.staff_identifier ?? ''
                                    }
                                    aria-describedby="staff_identifier-hint"
                                    aria-invalid={!!errors.staff_identifier}
                                />
                                <p
                                    id="staff_identifier-hint"
                                    className="text-sm text-muted-foreground"
                                >
                                    {t(
                                        'admin:users.fields.staff_identifier_hint',
                                    )}
                                </p>
                                <InputError message={errors.staff_identifier} />
                            </div>

                            <div className="grid gap-2">
                                <Label htmlFor="locale">
                                    {t('admin:users.fields.locale')}
                                </Label>
                                <select
                                    id="locale"
                                    name="locale"
                                    required
                                    defaultValue={account?.locale ?? 'id'}
                                    aria-invalid={!!errors.locale}
                                    className={selectClassName}
                                >
                                    <option value="id">
                                        {t('admin:users.locale.id')}
                                    </option>
                                    <option value="en">
                                        {t('admin:users.locale.en')}
                                    </option>
                                </select>
                                <InputError message={errors.locale} />
                            </div>

                            <div className="flex flex-col-reverse gap-2 sm:flex-row sm:justify-end">
                                <Button variant="outline" asChild>
                                    <Link href={cancelHref}>
                                        {t('common:actions.cancel')}
                                    </Link>
                                </Button>
                                <Button
                                    type="submit"
                                    disabled={processing}
                                    data-test="save-account-button"
                                >
                                    {t(
                                        creating
                                            ? 'admin:users.form.submit_create'
                                            : 'admin:users.form.submit_edit',
                                    )}
                                </Button>
                            </div>
                        </>
                    )}
                </Form>
                <ConflictDialog command={command} />
            </div>
        </>
    );
}

UserForm.layout = {
    breadcrumbs: [
        {
            title: 'admin:users.title',
            href: UserAdministrationController.index(),
        },
    ],
};
