import { Form, Head, Link, router } from '@inertiajs/react';
import type { ReactNode } from 'react';
import { useTranslation } from 'react-i18next';
import ChangeRequestController from '@/actions/App/Modules/Organization/Http/Controllers/ChangeRequestController';
import OrganizationController from '@/actions/App/Modules/Organization/Http/Controllers/OrganizationController';
import type {
    ChangeKind,
    Option,
    UnitSummary,
} from '@/components/admin/organization';
import {
    localToday,
    staffKinds,
    textareaClassName,
    unitKinds,
} from '@/components/admin/organization';
import { selectClassName } from '@/components/admin/users';
import { ConflictDialog } from '@/components/app/ConflictDialog';
import { PageHeader } from '@/components/app/page-header';
import InputError from '@/components/input-error';
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { useCommand } from '@/lib/command';

type Payload = Partial<{
    code: string | null;
    name: string | null;
    short_name: string | null;
    unit_type: string | null;
    parent: string | null;
    description: string | null;
    user: string | null;
    position: string | null;
    decree_ref: string | null;
    effective_to: string | null;
    appointment: number | null;
}>;

type Props = {
    /** The request to correct, or null for a new one. */
    changeRequest: {
        public_id: string;
        status: string;
        payload: Payload;
        reason: string;
        source_ref: string | null;
        proposed_effective_from: string;
        lock_version: number;
    } | null;
    kind: ChangeKind;
    /** The unit the change is about; for a new unit, the suggested parent. */
    unit: UnitSummary | null;
    options: {
        units: (Option & { depth: number })[];
        unitTypes: string[];
        people: Option[];
        positions: Option[];
        appointments: { value: number; label: string }[];
    };
};

export default function RequestForm({
    changeRequest,
    kind,
    unit,
    options,
}: Props) {
    const { t } = useTranslation();
    const command = useCommand();
    const creating = changeRequest === null;
    const payload: Payload = changeRequest?.payload ?? {};
    const title = t(
        creating
            ? 'admin:organization.form.create_title'
            : 'admin:organization.form.edit_title',
    );

    // Without a unit only a new top or child unit can be proposed; an inactive unit can only be activated again.
    const offered: ChangeKind[] =
        unit === null
            ? ['CREATE_UNIT']
            : unit.status === 'ACTIVE'
              ? [
                    ...unitKinds.filter((item) => item !== 'ACTIVATE_UNIT'),
                    ...staffKinds,
                ]
              : ['ACTIVATE_UNIT'];
    const kinds = offered.includes(kind) ? offered : [kind, ...offered];

    const subject = kind === 'CREATE_UNIT' ? null : unit;

    // Cancel goes back to where the form was opened from: the request, or the unit and its detail.
    const cancelHref = !creating
        ? ChangeRequestController.show(changeRequest.public_id)
        : OrganizationController.index({
              query: unit ? { unit: unit.public_id } : {},
          });

    const chooseKind = (next: string) => {
        router.get(
            ChangeRequestController.create.url({
                query: unit
                    ? { kind: next, unit: unit.public_id }
                    : { kind: next },
            }),
            {},
            { replace: true },
        );
    };

    return (
        <>
            <Head title={title} />
            <div className="mx-auto flex w-full max-w-3xl flex-1 flex-col gap-6 p-4 sm:p-6 lg:p-8">
                <PageHeader
                    title={title}
                    description={
                        subject
                            ? t('admin:organization.form.for_unit', {
                                  name: subject.name,
                              })
                            : t('admin:organization.form.intro')
                    }
                />

                <Form
                    {...(creating
                        ? ChangeRequestController.store.form()
                        : ChangeRequestController.update.form(
                              changeRequest.public_id,
                          ))}
                    {...command.visit}
                    options={{ preserveScroll: true }}
                    className="flex flex-col gap-6 rounded-lg border border-border bg-card p-4 shadow-card sm:p-6"
                >
                    {({ processing, errors }) => (
                        <>
                            {Object.keys(errors).length > 0 && (
                                <Alert
                                    className="border-danger"
                                    data-test="form-error-summary"
                                >
                                    <AlertTitle>
                                        {t(
                                            'admin:organization.form.error_summary',
                                        )}
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

                            {creating ? (
                                <>
                                    <input
                                        type="hidden"
                                        name="kind"
                                        value={kind}
                                    />
                                    {subject && (
                                        <input
                                            type="hidden"
                                            name="unit"
                                            value={subject.public_id}
                                        />
                                    )}
                                    <Field
                                        id="kind"
                                        label={t(
                                            'admin:organization.fields.kind',
                                        )}
                                        hint={t(
                                            `admin:organization.kind_hints.${kind}`,
                                        )}
                                    >
                                        <select
                                            id="kind"
                                            value={kind}
                                            onChange={(event) =>
                                                chooseKind(event.target.value)
                                            }
                                            aria-describedby="kind-hint"
                                            className={selectClassName}
                                            data-test="change-kind"
                                        >
                                            {kinds.map((item) => (
                                                <option key={item} value={item}>
                                                    {t(
                                                        `admin:organization.kinds.${item}`,
                                                    )}
                                                </option>
                                            ))}
                                        </select>
                                    </Field>
                                </>
                            ) : (
                                <>
                                    <input
                                        type="hidden"
                                        name="lock_version"
                                        value={changeRequest.lock_version}
                                    />
                                    <p className="text-sm text-muted-foreground">
                                        {t(
                                            'admin:organization.form.kind_fixed',
                                            {
                                                kind: t(
                                                    `admin:organization.kinds.${kind}`,
                                                ),
                                            },
                                        )}
                                    </p>
                                </>
                            )}

                            <ScopeFields
                                kind={kind}
                                payload={payload}
                                creating={creating}
                                unit={unit}
                                options={options}
                                errors={errors}
                            />

                            <Field
                                id="reason"
                                label={t('admin:organization.fields.reason')}
                                error={errors.reason}
                            >
                                <textarea
                                    id="reason"
                                    name="reason"
                                    required
                                    minLength={5}
                                    maxLength={2000}
                                    rows={3}
                                    defaultValue={changeRequest?.reason ?? ''}
                                    placeholder={t(
                                        'admin:organization.fields.reason_placeholder',
                                    )}
                                    aria-invalid={!!errors.reason}
                                    className={textareaClassName}
                                />
                            </Field>

                            <Field
                                id="source_ref"
                                label={t(
                                    'admin:organization.fields.source_ref',
                                )}
                                hint={t(
                                    'admin:organization.fields.source_ref_hint',
                                )}
                                error={errors.source_ref}
                            >
                                <Input
                                    id="source_ref"
                                    name="source_ref"
                                    maxLength={150}
                                    autoComplete="off"
                                    defaultValue={
                                        changeRequest?.source_ref ?? ''
                                    }
                                    aria-describedby="source_ref-hint"
                                    aria-invalid={!!errors.source_ref}
                                />
                            </Field>

                            <Field
                                id="proposed_effective_from"
                                label={t(
                                    'admin:organization.fields.proposed_effective_from',
                                )}
                                hint={t(
                                    'admin:organization.fields.proposed_effective_from_hint',
                                )}
                                error={errors.proposed_effective_from}
                            >
                                <Input
                                    id="proposed_effective_from"
                                    name="proposed_effective_from"
                                    type="date"
                                    required
                                    defaultValue={
                                        changeRequest?.proposed_effective_from ??
                                        localToday()
                                    }
                                    aria-describedby="proposed_effective_from-hint"
                                    aria-invalid={
                                        !!errors.proposed_effective_from
                                    }
                                />
                            </Field>

                            <div className="flex flex-col-reverse gap-2 sm:flex-row sm:justify-end">
                                <Button variant="outline" asChild>
                                    <Link href={cancelHref}>
                                        {t('common:actions.cancel')}
                                    </Link>
                                </Button>
                                <Button
                                    type="submit"
                                    disabled={processing}
                                    data-test="save-request-button"
                                >
                                    {t('admin:organization.form.save')}
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

function Field({
    id,
    label,
    hint,
    error,
    children,
}: {
    id: string;
    label: string;
    hint?: string;
    error?: string;
    children: ReactNode;
}) {
    return (
        <div className="grid gap-2">
            <Label htmlFor={id}>{label}</Label>
            {children}
            {hint && (
                <p id={`${id}-hint`} className="text-sm text-muted-foreground">
                    {hint}
                </p>
            )}
            <InputError message={error} />
        </div>
    );
}

/**
 * The fields of the change itself, by kind (WF-029 TR-281). Deactivating or activating a unit needs no field: the unit and
 * the reason say it all.
 */
function ScopeFields({
    kind,
    payload,
    creating,
    unit,
    options,
    errors,
}: {
    kind: ChangeKind;
    payload: Payload;
    creating: boolean;
    unit: UnitSummary | null;
    options: Props['options'];
    errors: Partial<Record<string, string>>;
}) {
    const { t } = useTranslation();

    const text = (
        field: 'code' | 'name' | 'short_name' | 'decree_ref',
        settings: {
            required?: boolean;
            maxLength: number;
            fallback?: string | null;
            hint?: string;
            mono?: boolean;
        },
    ) => (
        <Field
            id={field}
            label={t(`admin:organization.fields.${field}`)}
            hint={settings.hint}
            error={errors[field]}
        >
            <Input
                id={field}
                name={field}
                required={settings.required}
                maxLength={settings.maxLength}
                autoComplete="off"
                spellCheck={settings.mono ? false : undefined}
                className={settings.mono ? 'font-mono' : undefined}
                defaultValue={payload[field] ?? settings.fallback ?? ''}
                aria-describedby={settings.hint ? `${field}-hint` : undefined}
                aria-invalid={!!errors[field]}
            />
        </Field>
    );

    const unitType = (
        <Field
            id="unit_type"
            label={t('admin:organization.fields.unit_type')}
            hint={t('admin:organization.fields.unit_type_hint')}
            error={errors.unit_type}
        >
            <Input
                id="unit_type"
                name="unit_type"
                required
                maxLength={40}
                pattern="[A-Z][A-Z0-9_]*"
                autoComplete="off"
                spellCheck={false}
                list="unit-types"
                className="font-mono"
                defaultValue={
                    payload.unit_type ??
                    (kind === 'UPDATE_UNIT' ? unit?.unit_type : '') ??
                    ''
                }
                aria-describedby="unit_type-hint"
                aria-invalid={!!errors.unit_type}
            />
            <datalist id="unit-types">
                {options.unitTypes.map((type) => (
                    <option key={type} value={type} />
                ))}
            </datalist>
        </Field>
    );

    const parent = (fallback: string) => (
        <Field
            id="parent"
            label={t('admin:organization.fields.parent_public_id')}
            hint={t('admin:organization.fields.parent_hint')}
            error={errors.parent}
        >
            <select
                id="parent"
                name="parent"
                defaultValue={
                    payload.parent !== undefined
                        ? (payload.parent ?? '')
                        : fallback
                }
                aria-describedby="parent-hint"
                aria-invalid={!!errors.parent}
                className={selectClassName}
            >
                <option value="">
                    {t('admin:organization.fields.no_parent')}
                </option>
                {options.units.map((option) => (
                    <option key={option.value} value={option.value}>
                        {`${'— '.repeat(Math.min(option.depth, 8))}${option.label}`}
                    </option>
                ))}
            </select>
        </Field>
    );

    const choice = (
        field: 'user' | 'position' | 'appointment',
        choices: { value: string | number; label: string }[],
    ) => (
        <Field
            id={field}
            label={t(`admin:organization.fields.${field}`)}
            error={errors[field]}
        >
            <select
                id={field}
                name={field}
                required
                defaultValue={payload[field] ?? ''}
                aria-invalid={!!errors[field]}
                className={selectClassName}
            >
                <option value="" disabled>
                    {t('admin:organization.fields.choose')}
                </option>
                {choices.map((option) => (
                    <option key={option.value} value={option.value}>
                        {option.label}
                    </option>
                ))}
            </select>
        </Field>
    );

    switch (kind) {
        case 'CREATE_UNIT':
            return (
                <>
                    {text('code', {
                        required: true,
                        maxLength: 40,
                        hint: t('admin:organization.fields.code_hint'),
                        mono: true,
                    })}
                    {text('name', { required: true, maxLength: 200 })}
                    {text('short_name', { maxLength: 80 })}
                    {unitType}
                    {parent(creating ? (unit?.public_id ?? '') : '')}
                </>
            );
        case 'UPDATE_UNIT':
            return (
                <>
                    {text('name', {
                        required: true,
                        maxLength: 200,
                        fallback: unit?.name,
                    })}
                    {text('short_name', {
                        maxLength: 80,
                        fallback: unit?.short_name,
                    })}
                    {unitType}
                </>
            );
        case 'MOVE_UNIT':
            return parent(unit?.parent ?? '');
        case 'CREATE_POSITION':
            return (
                <>
                    {text('code', {
                        required: true,
                        maxLength: 60,
                        hint: t('admin:organization.fields.position_code_hint'),
                        mono: true,
                    })}
                    {text('name', { required: true, maxLength: 200 })}
                    <Field
                        id="description"
                        label={t('admin:organization.fields.description')}
                        error={errors.description}
                    >
                        <textarea
                            id="description"
                            name="description"
                            maxLength={2000}
                            rows={3}
                            defaultValue={payload.description ?? ''}
                            aria-invalid={!!errors.description}
                            className={textareaClassName}
                        />
                    </Field>
                </>
            );
        case 'APPOINT':
            return (
                <>
                    {choice('user', options.people)}
                    {choice('position', options.positions)}
                    {text('decree_ref', {
                        required: true,
                        maxLength: 150,
                        hint: t('admin:organization.fields.decree_ref_hint'),
                    })}
                    <Field
                        id="effective_to"
                        label={t('admin:organization.fields.effective_to')}
                        hint={t('admin:organization.fields.effective_to_hint')}
                        error={errors.effective_to}
                    >
                        <Input
                            id="effective_to"
                            name="effective_to"
                            type="date"
                            defaultValue={payload.effective_to ?? ''}
                            aria-describedby="effective_to-hint"
                            aria-invalid={!!errors.effective_to}
                        />
                    </Field>
                    <p className="text-sm text-muted-foreground">
                        {t('admin:organization.appointments.no_permission')}
                    </p>
                </>
            );
        case 'END_APPOINTMENT':
            return options.appointments.length === 0 ? (
                <p className="text-sm text-muted-foreground">
                    {t('admin:organization.appointments.none_active')}
                </p>
            ) : (
                choice('appointment', options.appointments)
            );
        default:
            return null;
    }
}

RequestForm.layout = {
    breadcrumbs: [
        {
            title: 'admin:organization.title',
            href: OrganizationController.index(),
        },
    ],
};
