import { Form, Head, Link } from '@inertiajs/react';
import { ShieldCheck } from 'lucide-react';
import { useState } from 'react';
import { useTranslation } from 'react-i18next';
import GrantController from '@/actions/App/Modules/Access/Http/Controllers/GrantController';
import UserAdministrationController from '@/actions/App/Modules/Identity/Http/Controllers/UserAdministrationController';
import { selectClassName } from '@/components/admin/users';
import { ConflictDialog } from '@/components/app/ConflictDialog';
import { PageHeader } from '@/components/app/page-header';
import InputError from '@/components/input-error';
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { scopeNeedsUnit } from '@/lib/access';
import { useCommand } from '@/lib/command';

type Profile = {
    code: string;
    name: string;
    description: string | null;
    is_privileged: boolean;
    capabilities: string[];
    scopes: string[];
};

type Capability = {
    value: string;
    scopes: string[];
    /** The scopes in which a grant of it waits for a second person. */
    privileged: string[];
};

type Props = {
    /** The user the form was opened for, from their detail. */
    user: { public_id: string; username: string; name: string } | null;
    canOpenUser: boolean;
    today: string;
    options: {
        profiles: Profile[];
        capabilities: Capability[];
        units: {
            public_id: string;
            code: string;
            name: string;
            depth: number;
        }[];
    };
};

const textareaClassName =
    'w-full min-w-0 rounded-md border border-input bg-transparent px-3 py-2 text-base shadow-xs outline-none placeholder:text-muted-foreground focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50 aria-invalid:border-destructive md:text-sm';

export default function GrantForm({
    user,
    canOpenUser,
    today,
    options,
}: Props) {
    const { t } = useTranslation();
    const command = useCommand();
    const [target, setTarget] = useState<'profile' | 'capability'>('profile');
    const [profileCode, setProfileCode] = useState('');
    const [capabilityValue, setCapabilityValue] = useState('');
    const [scope, setScope] = useState('');

    const profile = options.profiles.find((each) => each.code === profileCode);
    const capability = options.capabilities.find(
        (each) => each.value === capabilityValue,
    );
    const scopes =
        (target === 'profile' ? profile?.scopes : capability?.scopes) ?? [];
    const chosenScope = scopes.includes(scope) ? scope : '';
    // TAC-03: mirrors the server, which decides whether the grant waits for approval.
    const privileged =
        target === 'profile'
            ? (profile?.is_privileged ?? false)
            : chosenScope !== '' &&
              (capability?.privileged.includes(chosenScope) ?? false);

    // Cancel goes back to the user the form was opened for, or to the list.
    const cancelHref =
        user !== null && canOpenUser
            ? UserAdministrationController.show(user.public_id)
            : GrantController.index();

    return (
        <>
            <Head title={t('admin:access.form.title')} />
            <div className="mx-auto flex w-full max-w-3xl flex-1 flex-col gap-6 p-4 sm:p-6 lg:p-8">
                <PageHeader
                    title={t('admin:access.form.title')}
                    description={t('admin:access.form.intro')}
                />

                <Form
                    {...GrantController.store.form()}
                    {...command.visit}
                    options={{ preserveScroll: true }}
                    className="flex flex-col gap-6 rounded-lg border border-border bg-card p-4 shadow-card sm:p-6"
                >
                    {({ processing, errors }) => (
                        <>
                            {Object.keys(errors).length > 0 && (
                                <Alert
                                    variant="destructive"
                                    data-test="form-errors"
                                >
                                    <AlertTitle>
                                        {t('admin:access.form.errors_title')}
                                    </AlertTitle>
                                    <AlertDescription>
                                        <ul className="list-disc pl-4">
                                            {Object.entries(errors).map(
                                                ([field, message]) => (
                                                    <li key={field}>
                                                        {message}
                                                    </li>
                                                ),
                                            )}
                                        </ul>
                                    </AlertDescription>
                                </Alert>
                            )}

                            <div className="grid gap-2">
                                <Label htmlFor="username">
                                    {t('admin:access.fields.username')}
                                </Label>
                                <Input
                                    id="username"
                                    name="username"
                                    required
                                    maxLength={60}
                                    autoComplete="off"
                                    spellCheck={false}
                                    defaultValue={user?.username ?? ''}
                                    aria-describedby="username-hint"
                                    aria-invalid={!!errors.username}
                                />
                                <p
                                    id="username-hint"
                                    className="text-sm text-muted-foreground"
                                >
                                    {user === null
                                        ? t('admin:access.form.username_hint')
                                        : user.name}
                                </p>
                                <InputError message={errors.username} />
                            </div>

                            <fieldset className="grid gap-2">
                                <legend className="mb-2 text-sm font-medium">
                                    {t('admin:access.fields.target')}
                                </legend>
                                <div className="flex flex-wrap gap-4">
                                    {(['profile', 'capability'] as const).map(
                                        (each) => (
                                            <label
                                                key={each}
                                                className="flex min-h-11 items-center gap-2 text-sm"
                                            >
                                                <input
                                                    type="radio"
                                                    name="target"
                                                    value={each}
                                                    checked={target === each}
                                                    onChange={() =>
                                                        setTarget(each)
                                                    }
                                                    className="size-4"
                                                />
                                                {t(
                                                    `admin:access.form.target_${each}`,
                                                )}
                                            </label>
                                        ),
                                    )}
                                </div>
                                <InputError message={errors.target} />
                            </fieldset>

                            {target === 'profile' ? (
                                <div className="grid gap-2">
                                    <Label htmlFor="profile">
                                        {t('admin:access.fields.profile')}
                                    </Label>
                                    <select
                                        id="profile"
                                        name="profile"
                                        required
                                        value={profileCode}
                                        onChange={(event) =>
                                            setProfileCode(event.target.value)
                                        }
                                        aria-describedby="profile-hint"
                                        aria-invalid={!!errors.profile}
                                        className={selectClassName}
                                    >
                                        <option value="" disabled>
                                            {t('admin:access.form.choose')}
                                        </option>
                                        {options.profiles.map((each) => (
                                            <option
                                                key={each.code}
                                                value={each.code}
                                            >
                                                {each.name}
                                            </option>
                                        ))}
                                    </select>
                                    <p
                                        id="profile-hint"
                                        className="text-sm break-words text-muted-foreground"
                                    >
                                        {profile === undefined
                                            ? t(
                                                  'admin:access.form.profile_hint',
                                              )
                                            : (profile.description ??
                                              profile.capabilities.join(', '))}
                                    </p>
                                    <InputError message={errors.profile} />
                                </div>
                            ) : (
                                <div className="grid gap-2">
                                    <Label htmlFor="capability">
                                        {t('admin:access.fields.capability')}
                                    </Label>
                                    <select
                                        id="capability"
                                        name="capability"
                                        required
                                        value={capabilityValue}
                                        onChange={(event) =>
                                            setCapabilityValue(
                                                event.target.value,
                                            )
                                        }
                                        aria-invalid={!!errors.capability}
                                        className={selectClassName}
                                    >
                                        <option value="" disabled>
                                            {t('admin:access.form.choose')}
                                        </option>
                                        {options.capabilities.map((each) => (
                                            <option
                                                key={each.value}
                                                value={each.value}
                                            >
                                                {each.value}
                                            </option>
                                        ))}
                                    </select>
                                    <InputError message={errors.capability} />
                                </div>
                            )}

                            <div className="grid gap-2">
                                <Label htmlFor="scope_type">
                                    {t('admin:access.fields.scope_type')}
                                </Label>
                                <select
                                    id="scope_type"
                                    name="scope_type"
                                    required
                                    value={chosenScope}
                                    onChange={(event) =>
                                        setScope(event.target.value)
                                    }
                                    disabled={scopes.length === 0}
                                    aria-describedby="scope_type-hint"
                                    aria-invalid={!!errors.scope_type}
                                    className={selectClassName}
                                >
                                    <option value="" disabled>
                                        {t('admin:access.form.choose')}
                                    </option>
                                    {scopes.map((each) => (
                                        <option key={each} value={each}>
                                            {t(`admin:access.scope.${each}`)}
                                        </option>
                                    ))}
                                </select>
                                <p
                                    id="scope_type-hint"
                                    className="text-sm text-muted-foreground"
                                >
                                    {t('admin:access.form.scope_hint')}
                                </p>
                                <InputError message={errors.scope_type} />
                            </div>

                            {scopeNeedsUnit(chosenScope) && (
                                <div className="grid gap-2">
                                    <Label htmlFor="unit">
                                        {t('admin:access.fields.unit')}
                                    </Label>
                                    <select
                                        id="unit"
                                        name="unit"
                                        required
                                        defaultValue=""
                                        aria-invalid={!!errors.unit}
                                        className={selectClassName}
                                    >
                                        <option value="" disabled>
                                            {t('admin:access.form.choose')}
                                        </option>
                                        {options.units.map((unit) => (
                                            <option
                                                key={unit.public_id}
                                                value={unit.public_id}
                                            >
                                                {`${'  '.repeat(unit.depth)}${unit.name} (${unit.code})`}
                                            </option>
                                        ))}
                                    </select>
                                    <InputError message={errors.unit} />
                                </div>
                            )}

                            {chosenScope === 'POLICY_FAMILY' && (
                                <div className="grid gap-2">
                                    <Label htmlFor="scope_key">
                                        {t('admin:access.fields.scope_key')}
                                    </Label>
                                    <Input
                                        id="scope_key"
                                        name="scope_key"
                                        required
                                        maxLength={80}
                                        autoComplete="off"
                                        spellCheck={false}
                                        placeholder="procurement.method"
                                        aria-invalid={!!errors.scope_key}
                                    />
                                    <InputError message={errors.scope_key} />
                                </div>
                            )}

                            <div className="grid gap-4 sm:grid-cols-2">
                                <div className="grid gap-2">
                                    <Label htmlFor="effective_from">
                                        {t(
                                            'admin:access.fields.effective_from',
                                        )}
                                    </Label>
                                    <Input
                                        id="effective_from"
                                        name="effective_from"
                                        type="date"
                                        required
                                        min={today}
                                        defaultValue={today}
                                        aria-invalid={!!errors.effective_from}
                                    />
                                    <InputError
                                        message={errors.effective_from}
                                    />
                                </div>
                                <div className="grid gap-2">
                                    <Label htmlFor="effective_to">
                                        {t('admin:access.fields.effective_to')}
                                    </Label>
                                    <Input
                                        id="effective_to"
                                        name="effective_to"
                                        type="date"
                                        min={today}
                                        aria-describedby="effective_to-hint"
                                        aria-invalid={!!errors.effective_to}
                                    />
                                    <p
                                        id="effective_to-hint"
                                        className="text-sm text-muted-foreground"
                                    >
                                        {t(
                                            'admin:access.form.effective_to_hint',
                                        )}
                                    </p>
                                    <InputError message={errors.effective_to} />
                                </div>
                            </div>

                            <div className="grid gap-2">
                                <Label htmlFor="reason">
                                    {t('admin:access.fields.reason')}
                                </Label>
                                <textarea
                                    id="reason"
                                    name="reason"
                                    required
                                    minLength={5}
                                    maxLength={500}
                                    rows={3}
                                    placeholder={t(
                                        'admin:access.form.reason_placeholder',
                                    )}
                                    aria-invalid={!!errors.reason}
                                    className={textareaClassName}
                                />
                                <InputError message={errors.reason} />
                            </div>

                            {privileged && (
                                <Alert data-test="privileged-notice">
                                    <ShieldCheck aria-hidden="true" />
                                    <AlertTitle>
                                        {t(
                                            'admin:access.form.privileged_title',
                                        )}
                                    </AlertTitle>
                                    <AlertDescription>
                                        {t(
                                            'admin:access.form.privileged_description',
                                        )}
                                    </AlertDescription>
                                </Alert>
                            )}

                            <div className="flex flex-col-reverse gap-2 sm:flex-row sm:justify-end">
                                <Button variant="outline" asChild>
                                    <Link href={cancelHref}>
                                        {t('common:actions.cancel')}
                                    </Link>
                                </Button>
                                <Button
                                    type="submit"
                                    disabled={processing}
                                    data-test="save-grant-button"
                                >
                                    {t('admin:access.form.submit')}
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

GrantForm.layout = {
    breadcrumbs: [
        {
            title: 'admin:access.title',
            href: GrantController.index(),
        },
    ],
};
