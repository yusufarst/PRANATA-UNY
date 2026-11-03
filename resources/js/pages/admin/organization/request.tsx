import { Form, Head, Link, usePage } from '@inertiajs/react';
import { ArrowLeft, FlaskConical, Pencil, ShieldAlert } from 'lucide-react';
import { useState } from 'react';
import { useTranslation } from 'react-i18next';
import ChangeRequestController from '@/actions/App/Modules/Organization/Http/Controllers/ChangeRequestController';
import OrganizationController from '@/actions/App/Modules/Organization/Http/Controllers/OrganizationController';
import type {
    RequestStatus,
    RequestSummary,
    UnitSummary,
} from '@/components/admin/organization';
import {
    RequestStatusBadge,
    SimulationBadge,
    textareaClassName,
    useDate,
} from '@/components/admin/organization';
import { useDateTime } from '@/components/admin/users';
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
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import type { Command } from '@/lib/command';
import { useCommand } from '@/lib/command';

type Issue = { code: string; blocking: boolean };

type Props = {
    changeRequest: RequestSummary & {
        payload: Record<string, string | number | null>;
        scope: Partial<
            Record<
                'parent' | 'user' | 'position' | 'appointment',
                string | null
            >
        >;
        reason: string;
        source_ref: string | null;
        validation: { checked_at: string; issues: Issue[] } | null;
        decision_ref: string | null;
        closed_reason: string | null;
        created_by: string | null;
        decided_by: string | null;
        decided_at: string | null;
        applied_at: string | null;
        lock_version: number;
    };
    unit: UnitSummary | null;
    transitions: {
        id: number;
        transition_code: string;
        from_state: RequestStatus | null;
        to_state: RequestStatus;
        actor: string | null;
        acted_at: string;
        reason: string | null;
        is_simulation: boolean;
    }[];
    /** The business date of the server (Asia/Jakarta). */
    today: string;
    /** TAC-04: whether TR-285 may be recorded now, and whether it would be a simulation. */
    decision: { available: boolean; simulation: boolean };
    can: {
        edit: boolean;
        submit: boolean;
        return: boolean;
        escalate: boolean;
        decide: boolean;
        apply: boolean;
        withdraw: boolean;
    };
};

// The payload fields in the order they are shown, each labelled by admin:organization.fields.<field>.
const payloadFields = [
    'code',
    'name',
    'short_name',
    'unit_type',
    'parent',
    'description',
    'user',
    'position',
    'decree_ref',
    'effective_to',
    'appointment',
] as const;

const sectionClassName =
    'rounded-lg border border-border bg-card p-4 shadow-card sm:p-6';

export default function RequestShow({
    changeRequest: request,
    unit,
    transitions,
    today,
    decision,
    can,
}: Props) {
    const { t } = useTranslation();
    const date = useDate();
    const dateTime = useDateTime();
    const command = useCommand();
    // A refused step answers with the reason under _domain (COMMAND_AND_API_CONTRACTS section 5).
    const { errors } = usePage().props;
    const [reasonAction, setReasonAction] = useState<
        'return' | 'withdraw' | null
    >(null);
    const [deciding, setDeciding] = useState(false);
    const none = t('admin:organization.none');
    const issues = request.validation?.issues ?? [];
    const title = request.label
        ? `${t(`admin:organization.kinds.${request.kind}`)} · ${request.label}`
        : t(`admin:organization.kinds.${request.kind}`);

    const backHref = unit
        ? OrganizationController.index({ query: { unit: unit.public_id } })
        : OrganizationController.index();

    const shown = (field: (typeof payloadFields)[number]): string | null => {
        if (!(field in request.payload)) {
            return null;
        }

        const value = request.payload[field];

        if (field === 'parent') {
            return value === null
                ? t('admin:organization.fields.no_parent')
                : (request.scope.parent ?? String(value));
        }

        if (
            field === 'user' ||
            field === 'position' ||
            field === 'appointment'
        ) {
            return (
                request.scope[field] ?? (value === null ? none : String(value))
            );
        }

        if (field === 'effective_to') {
            return date(value === null ? null : String(value)) ?? none;
        }

        return value === null ? none : String(value);
    };

    const details: [string, string][] = [
        ...(unit
            ? [
                  [t('admin:organization.fields.unit'), unit.name] as [
                      string,
                      string,
                  ],
              ]
            : []),
        ...payloadFields.flatMap((field) => {
            const value = shown(field);

            return value === null
                ? []
                : [
                      [
                          t(
                              field === 'parent'
                                  ? 'admin:organization.fields.parent_public_id'
                                  : `admin:organization.fields.${field}`,
                          ),
                          value,
                      ] as [string, string],
                  ];
        }),
        [t('admin:organization.fields.reason'), request.reason],
        [t('admin:organization.fields.source_ref'), request.source_ref ?? none],
        [
            t('admin:organization.fields.proposed_effective_from'),
            date(request.proposed_effective_from) ?? none,
        ],
        ...(request.effective_from
            ? [
                  [
                      t('admin:organization.fields.effective_from'),
                      date(request.effective_from) ?? none,
                  ] as [string, string],
              ]
            : []),
        ...(request.decision_ref
            ? [
                  [
                      t('admin:organization.fields.decision_ref'),
                      request.decision_ref,
                  ] as [string, string],
              ]
            : []),
        ...(request.closed_reason
            ? [
                  [
                      t('admin:organization.fields.closed_reason'),
                      request.closed_reason,
                  ] as [string, string],
              ]
            : []),
        [t('admin:organization.fields.created_by'), request.created_by ?? none],
    ];

    // The one main step of the current state (INTERACTION_PATTERNS section 2).
    const primary = can.submit ? (
        <VersionCommand
            form={ChangeRequestController.submit.form(request.public_id)}
            command={command}
            lockVersion={request.lock_version}
            label={t('admin:organization.actions.submit')}
            test="submit-request-button"
        />
    ) : can.escalate ? (
        <VersionCommand
            form={ChangeRequestController.escalate.form(request.public_id)}
            command={command}
            lockVersion={request.lock_version}
            label={t('admin:organization.actions.escalate')}
            test="escalate-request-button"
        />
    ) : can.decide ? (
        <Button
            onClick={() => setDeciding(true)}
            data-test="decide-request-button"
        >
            {t('admin:organization.actions.decide')}
        </Button>
    ) : can.apply ? (
        <VersionCommand
            form={ChangeRequestController.apply.form(request.public_id)}
            command={command}
            lockVersion={request.lock_version}
            label={t('admin:organization.actions.apply')}
            test="apply-request-button"
        />
    ) : null;

    const secondary = (can.edit || can.return || can.withdraw) && (
        <>
            {can.edit && (
                <Button variant="outline" asChild>
                    <Link
                        href={ChangeRequestController.edit(request.public_id)}
                    >
                        <Pencil aria-hidden="true" />
                        {t('admin:organization.actions.edit')}
                    </Link>
                </Button>
            )}
            {can.return && (
                <Button
                    variant="outline"
                    onClick={() => setReasonAction('return')}
                    data-test="return-request-button"
                >
                    {t('admin:organization.actions.return')}
                </Button>
            )}
            {can.withdraw && (
                <Button
                    variant="outline"
                    className="text-danger"
                    onClick={() => setReasonAction('withdraw')}
                    data-test="withdraw-request-button"
                >
                    {t('admin:organization.actions.withdraw')}
                </Button>
            )}
        </>
    );

    const failure = errors._domain ?? errors._idempotency;

    return (
        <>
            <Head title={title} />
            <div className="mx-auto flex w-full max-w-4xl flex-1 flex-col gap-6 p-4 sm:p-6 lg:p-8">
                <div>
                    <Button variant="ghost" size="sm" asChild className="-ml-2">
                        <Link href={backHref}>
                            <ArrowLeft aria-hidden="true" />
                            {t(
                                unit
                                    ? 'admin:organization.back_to_unit'
                                    : 'admin:organization.back_to_tree',
                            )}
                        </Link>
                    </Button>
                </div>

                <PageHeader
                    title={title}
                    description={t(
                        `admin:organization.status_hints.${request.status}`,
                    )}
                    status={
                        <span className="flex flex-wrap gap-2">
                            <RequestStatusBadge status={request.status} />
                            {request.is_simulation && <SimulationBadge />}
                        </span>
                    }
                    secondaryActions={secondary}
                    action={primary}
                />

                {failure && (
                    <Alert className="border-danger" data-test="request-error">
                        <ShieldAlert aria-hidden="true" />
                        <AlertTitle>
                            {t('admin:organization.failed')}
                        </AlertTitle>
                        <AlertDescription>{failure}</AlertDescription>
                    </Alert>
                )}

                {request.status === 'AWAITING_DECISION' &&
                    !decision.available && (
                        <Alert data-test="policy-blocked">
                            <ShieldAlert aria-hidden="true" />
                            <AlertTitle>
                                {t('admin:organization.policy.blocked_title')}
                            </AlertTitle>
                            <AlertDescription>
                                {t(
                                    'admin:organization.policy.blocked_description',
                                )}
                            </AlertDescription>
                        </Alert>
                    )}

                {request.status === 'AWAITING_DECISION' &&
                    decision.available &&
                    decision.simulation && (
                        <Alert data-test="policy-simulation">
                            <FlaskConical aria-hidden="true" />
                            <AlertTitle>
                                {t(
                                    'admin:organization.policy.simulation_title',
                                )}
                            </AlertTitle>
                            <AlertDescription>
                                {t(
                                    'admin:organization.policy.simulation_description',
                                )}
                            </AlertDescription>
                        </Alert>
                    )}

                {request.status === 'APPROVED' &&
                    !can.apply &&
                    request.effective_from && (
                        <Alert>
                            <AlertDescription>
                                {t('admin:organization.not_yet_effective', {
                                    date: date(request.effective_from),
                                })}
                            </AlertDescription>
                        </Alert>
                    )}

                {request.validation && (
                    <section
                        aria-labelledby="request-issues"
                        className={sectionClassName}
                        data-test="request-issues"
                    >
                        <h2
                            id="request-issues"
                            className="mb-1 text-lg font-semibold text-foreground"
                        >
                            {t('admin:organization.validation.section')}
                        </h2>
                        <p className="mb-4 text-sm text-muted-foreground">
                            {t('admin:organization.validation.checked_at', {
                                time: dateTime(request.validation.checked_at),
                            })}
                        </p>
                        {issues.length === 0 ? (
                            <p className="text-sm text-foreground">
                                {t('admin:organization.validation.clear')}
                            </p>
                        ) : (
                            <ul className="grid gap-2">
                                {issues.map((issue) => (
                                    <li
                                        key={issue.code}
                                        className="grid gap-1 rounded-md border border-border p-3"
                                    >
                                        <span
                                            className={
                                                issue.blocking
                                                    ? 'text-sm font-medium text-danger'
                                                    : 'text-sm font-medium text-warning'
                                            }
                                        >
                                            {t(
                                                issue.blocking
                                                    ? 'admin:organization.validation.blocking'
                                                    : 'admin:organization.validation.notice',
                                            )}
                                        </span>
                                        <span className="text-sm text-foreground">
                                            {t(
                                                `admin:organization.issues.${issue.code}`,
                                            )}
                                        </span>
                                    </li>
                                ))}
                            </ul>
                        )}
                    </section>
                )}

                <section
                    aria-labelledby="request-details"
                    className={sectionClassName}
                >
                    <h2
                        id="request-details"
                        className="mb-4 text-lg font-semibold text-foreground"
                    >
                        {t('admin:organization.request_details')}
                    </h2>
                    <dl className="grid gap-4 sm:grid-cols-2">
                        {details.map(([label, value]) => (
                            <div key={label} className="grid gap-1">
                                <dt className="text-sm text-muted-foreground">
                                    {label}
                                </dt>
                                <dd className="break-words whitespace-pre-line text-foreground">
                                    {value}
                                </dd>
                            </div>
                        ))}
                    </dl>
                </section>

                <section
                    aria-labelledby="request-transitions"
                    className={sectionClassName}
                    data-test="request-transitions"
                >
                    <h2
                        id="request-transitions"
                        className="mb-4 text-lg font-semibold text-foreground"
                    >
                        {t('admin:organization.transitions.section')}
                    </h2>
                    <ol className="grid gap-3">
                        {transitions.map((transition) => (
                            <li
                                key={transition.id}
                                className="grid gap-1 rounded-md border border-border p-3"
                            >
                                <span className="flex flex-wrap items-center gap-2">
                                    <span className="font-medium text-foreground">
                                        {t(
                                            `admin:organization.transitions.codes.${transition.transition_code}`,
                                        )}
                                    </span>
                                    {transition.is_simulation && (
                                        <SimulationBadge />
                                    )}
                                </span>
                                <span className="text-sm text-muted-foreground">
                                    {t('admin:organization.transitions.meta', {
                                        to: t(
                                            `admin:organization.request_status.${transition.to_state}`,
                                        ),
                                        name: transition.actor ?? none,
                                        time: dateTime(transition.acted_at),
                                    })}
                                </span>
                                {transition.reason && (
                                    <span className="text-sm break-words text-foreground">
                                        {transition.reason}
                                    </span>
                                )}
                            </li>
                        ))}
                    </ol>
                </section>
            </div>

            <Dialog
                open={reasonAction !== null}
                onOpenChange={(open) => !open && setReasonAction(null)}
            >
                <DialogContent>
                    <DialogTitle>
                        {t(
                            reasonAction === 'return'
                                ? 'admin:organization.return.title'
                                : 'admin:organization.withdraw.title',
                        )}
                    </DialogTitle>
                    <DialogDescription>
                        {t(
                            reasonAction === 'return'
                                ? 'admin:organization.return.description'
                                : 'admin:organization.withdraw.description',
                        )}
                    </DialogDescription>
                    <Form
                        {...(reasonAction === 'return'
                            ? ChangeRequestController.returnToDraft.form(
                                  request.public_id,
                              )
                            : ChangeRequestController.withdraw.form(
                                  request.public_id,
                              ))}
                        {...command.visit}
                        onSuccess={() => {
                            command.visit.onSuccess();
                            setReasonAction(null);
                        }}
                        options={{ preserveScroll: true }}
                        className="grid gap-4"
                    >
                        {({ processing, errors: formErrors }) => (
                            <>
                                <InputError
                                    message={
                                        formErrors._idempotency ??
                                        formErrors._domain
                                    }
                                />
                                <input
                                    type="hidden"
                                    name="lock_version"
                                    value={request.lock_version}
                                />
                                <div className="grid gap-2">
                                    <Label htmlFor="action_reason">
                                        {t('admin:organization.fields.reason')}
                                    </Label>
                                    <textarea
                                        id="action_reason"
                                        name="reason"
                                        required
                                        minLength={5}
                                        maxLength={2000}
                                        rows={3}
                                        aria-invalid={!!formErrors.reason}
                                        className={textareaClassName}
                                    />
                                    <InputError message={formErrors.reason} />
                                </div>
                                <DialogFooter className="gap-2">
                                    <Button
                                        type="button"
                                        variant="secondary"
                                        onClick={() => setReasonAction(null)}
                                    >
                                        {t('common:actions.cancel')}
                                    </Button>
                                    <Button
                                        type="submit"
                                        variant={
                                            reasonAction === 'withdraw'
                                                ? 'destructive'
                                                : 'default'
                                        }
                                        disabled={processing}
                                        data-test="confirm-reason-button"
                                    >
                                        {t(
                                            reasonAction === 'return'
                                                ? 'admin:organization.return.confirm'
                                                : 'admin:organization.withdraw.confirm',
                                        )}
                                    </Button>
                                </DialogFooter>
                            </>
                        )}
                    </Form>
                </DialogContent>
            </Dialog>

            <Dialog open={deciding} onOpenChange={setDeciding}>
                <DialogContent>
                    <DialogTitle>
                        {t('admin:organization.decision.title')}
                    </DialogTitle>
                    <DialogDescription>
                        {t(
                            decision.simulation
                                ? 'admin:organization.decision.simulation_description'
                                : 'admin:organization.decision.description',
                        )}
                    </DialogDescription>
                    <Form
                        {...ChangeRequestController.decide.form(
                            request.public_id,
                        )}
                        {...command.visit}
                        onSuccess={() => {
                            command.visit.onSuccess();
                            setDeciding(false);
                        }}
                        options={{ preserveScroll: true }}
                        className="grid gap-4"
                    >
                        {({ processing, errors: formErrors }) => (
                            <>
                                <InputError
                                    message={
                                        formErrors._idempotency ??
                                        formErrors._domain
                                    }
                                />
                                <input
                                    type="hidden"
                                    name="lock_version"
                                    value={request.lock_version}
                                />
                                <fieldset className="grid gap-2">
                                    <legend className="mb-2 text-sm font-medium">
                                        {t(
                                            'admin:organization.decision.choice',
                                        )}
                                    </legend>
                                    {(['APPROVE', 'REJECT'] as const).map(
                                        (choice) => (
                                            <label
                                                key={choice}
                                                className="flex min-h-11 items-center gap-3 rounded-md border border-border px-3 text-sm"
                                            >
                                                <input
                                                    type="radio"
                                                    name="decision"
                                                    value={choice}
                                                    required
                                                    defaultChecked={
                                                        choice === 'APPROVE'
                                                    }
                                                    data-test={`decision-${choice.toLowerCase()}`}
                                                />
                                                {t(
                                                    `admin:organization.decision.${choice}`,
                                                )}
                                            </label>
                                        ),
                                    )}
                                    <InputError message={formErrors.decision} />
                                </fieldset>
                                <div className="grid gap-2">
                                    <Label htmlFor="effective_from">
                                        {t(
                                            'admin:organization.fields.effective_from',
                                        )}
                                    </Label>
                                    <Input
                                        id="effective_from"
                                        name="effective_from"
                                        type="date"
                                        min={today}
                                        defaultValue={
                                            request.proposed_effective_from >=
                                            today
                                                ? request.proposed_effective_from
                                                : today
                                        }
                                        aria-describedby="effective_from-hint"
                                        aria-invalid={
                                            !!formErrors.effective_from
                                        }
                                    />
                                    <p
                                        id="effective_from-hint"
                                        className="text-sm text-muted-foreground"
                                    >
                                        {t(
                                            'admin:organization.decision.effective_hint',
                                        )}
                                    </p>
                                    <InputError
                                        message={formErrors.effective_from}
                                    />
                                </div>
                                <div className="grid gap-2">
                                    <Label htmlFor="decision_ref">
                                        {t(
                                            'admin:organization.fields.decision_ref',
                                        )}
                                    </Label>
                                    <Input
                                        id="decision_ref"
                                        name="decision_ref"
                                        required
                                        maxLength={150}
                                        autoComplete="off"
                                        placeholder={t(
                                            'admin:organization.decision.ref_placeholder',
                                        )}
                                        aria-invalid={!!formErrors.decision_ref}
                                    />
                                    <InputError
                                        message={formErrors.decision_ref}
                                    />
                                </div>
                                <div className="grid gap-2">
                                    <Label htmlFor="decision_reason">
                                        {t(
                                            'admin:organization.decision.reason',
                                        )}
                                    </Label>
                                    <textarea
                                        id="decision_reason"
                                        name="reason"
                                        maxLength={2000}
                                        rows={3}
                                        aria-invalid={!!formErrors.reason}
                                        className={textareaClassName}
                                    />
                                    <InputError message={formErrors.reason} />
                                </div>
                                <DialogFooter className="gap-2">
                                    <Button
                                        type="button"
                                        variant="secondary"
                                        onClick={() => setDeciding(false)}
                                    >
                                        {t('common:actions.cancel')}
                                    </Button>
                                    <Button
                                        type="submit"
                                        disabled={processing}
                                        data-test="confirm-decision-button"
                                    >
                                        {t(
                                            'admin:organization.decision.confirm',
                                        )}
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
 * A transition that needs only the version the user saw: submit (TR-282), escalate (TR-284) or apply (TR-286).
 */
function VersionCommand({
    form,
    command,
    lockVersion,
    label,
    test,
}: {
    form: ReturnType<typeof ChangeRequestController.submit.form>;
    command: Command;
    lockVersion: number;
    label: string;
    test: string;
}) {
    return (
        <Form {...form} {...command.visit} options={{ preserveScroll: true }}>
            {({ processing }) => (
                <>
                    <input
                        type="hidden"
                        name="lock_version"
                        value={lockVersion}
                    />
                    <Button
                        type="submit"
                        disabled={processing}
                        data-test={test}
                    >
                        {label}
                    </Button>
                </>
            )}
        </Form>
    );
}

RequestShow.layout = {
    breadcrumbs: [
        {
            title: 'admin:organization.title',
            href: OrganizationController.index(),
        },
    ],
};
