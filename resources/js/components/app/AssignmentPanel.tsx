import { Form, usePage } from '@inertiajs/react';
import { EllipsisVertical, ShieldAlert, UserPlus } from 'lucide-react';
import { useState } from 'react';
import { useTranslation } from 'react-i18next';
import AssignmentController from '@/actions/App/Modules/Access/Http/Controllers/AssignmentController';
import { selectClassName, useDateTime } from '@/components/admin/users';
import { ConflictDialog } from '@/components/app/ConflictDialog';
import { StatusBadge } from '@/components/app/status-badge';
import type { StatusCategory } from '@/components/app/status-badge';
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
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { useCommand } from '@/lib/command';

export const assignmentStatuses = ['ACTIVE', 'ENDED', 'REVOKED'] as const;

export type AssignmentStatus = (typeof assignmentStatuses)[number];

export const assignmentStatusCategory: Record<
    AssignmentStatus,
    StatusCategory
> = {
    ACTIVE: 'completed',
    ENDED: 'draft',
    REVOKED: 'rejected',
};

export type AssignmentRow = {
    public_id: string;
    assignment_role: string;
    decision: boolean;
    user: { public_id: string; username: string; name: string };
    status: AssignmentStatus;
    upcoming: boolean;
    effective_from: string;
    effective_to: string | null;
    decree_ref: string | null;
    sod_rules: string[];
    assigned_by: string | null;
    closed_by: string | null;
    closed_at: string | null;
    close_reason: string | null;
    lock_version: number;
    can_close: boolean;
};

export type AssignmentPanelProps = {
    subject: { type: string; reference: string; label: string };
    roles: { value: string; decision: boolean }[];
    assignments: AssignmentRow[];
    suggestions: {
        username: string;
        name: string;
        position_name: string;
        unit_name: string;
        decree_ref: string | null;
    }[];
    can: { assign: boolean };
    today: string;
};

type Closing = { row: AssignmentRow; status: 'ENDED' | 'REVOKED' };

const textareaClassName =
    'w-full min-w-0 rounded-md border border-input bg-transparent px-3 py-2 text-base shadow-xs outline-none placeholder:text-muted-foreground focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50 aria-invalid:border-destructive md:text-sm';

/** The status of an assignment, with "from <date>" while an active one waits for its first day. */
export function AssignmentBadge({ row }: { row: AssignmentRow }) {
    const { t } = useTranslation();
    const dateTime = useDateTime();

    return row.upcoming ? (
        <StatusBadge
            category="waiting"
            label={t('admin:assignments.upcoming', {
                date: dateTime(row.effective_from),
            })}
        />
    ) : (
        <StatusBadge
            category={assignmentStatusCategory[row.status]}
            label={t(`admin:assignments.status.${row.status}`)}
        />
    );
}

/**
 * The assignments on one object (ACCESS_CONTROL section 5): who holds which role, the history, and "Tugaskan" as the single
 * primary action for a user who may assign there. A module page renders it with the props of AssignmentPanel.php for its
 * own object; the server checks every command again.
 *
 * A suggestion from an appointment only fills in the form (TAC-04).
 */
export function AssignmentPanel({ panel }: { panel: AssignmentPanelProps }) {
    const { t } = useTranslation();
    const dateTime = useDateTime();
    const command = useCommand();
    const { errors } = usePage().props;
    const [assigning, setAssigning] = useState(false);
    const [username, setUsername] = useState('');
    const [decreeRef, setDecreeRef] = useState('');
    const [closing, setClosing] = useState<Closing | null>(null);
    const none = t('admin:assignments.fields.none');
    const active = panel.assignments.filter((row) => row.status === 'ACTIVE');
    const history = panel.assignments.filter((row) => row.status !== 'ACTIVE');
    // A refused command answers with its reason under _domain; the dialogs show their own while open.
    const failure =
        assigning || closing !== null
            ? undefined
            : (errors._domain ?? errors._idempotency);

    const openAssign = (suggestion?: AssignmentPanelProps['suggestions'][0]) => {
        setUsername(suggestion?.username ?? '');
        setDecreeRef(suggestion?.decree_ref ?? '');
        setAssigning(true);
    };

    const period = (row: AssignmentRow) =>
        row.effective_to === null
            ? t('admin:assignments.period_open', {
                  from: dateTime(row.effective_from),
              })
            : t('admin:assignments.period', {
                  from: dateTime(row.effective_from),
                  to: dateTime(row.effective_to),
              });

    const role = (row: AssignmentRow) => (
        <span className="flex flex-wrap items-center gap-2">
            <span className="font-mono text-sm">{row.assignment_role}</span>
            {row.decision && (
                <span className="rounded-md bg-brand-950 px-2 py-0.5 text-xs text-text-on-brand">
                    {t('admin:assignments.decision')}
                </span>
            )}
        </span>
    );

    const details = (row: AssignmentRow) => (
        <dl className="grid gap-2 text-sm sm:grid-cols-2">
            <div className="grid gap-0.5">
                <dt className="text-muted-foreground">
                    {t('admin:assignments.fields.period')}
                </dt>
                <dd>{period(row)}</dd>
            </div>
            <div className="grid gap-0.5">
                <dt className="text-muted-foreground">
                    {t('admin:assignments.fields.decree_ref')}
                </dt>
                <dd className="break-words">{row.decree_ref ?? none}</dd>
            </div>
            <div className="grid gap-0.5">
                <dt className="text-muted-foreground">
                    {t('admin:assignments.fields.assigned_by')}
                </dt>
                <dd>{row.assigned_by ?? none}</dd>
            </div>
            <div className="grid gap-0.5">
                <dt className="text-muted-foreground">
                    {t('admin:assignments.fields.sod_rules')}
                </dt>
                <dd className="font-mono">
                    {row.sod_rules.length > 0 ? row.sod_rules.join(', ') : none}
                </dd>
            </div>
            {row.closed_at && (
                <>
                    <div className="grid gap-0.5">
                        <dt className="text-muted-foreground">
                            {t('admin:assignments.fields.closed_by')}
                        </dt>
                        <dd>
                            {`${row.closed_by ?? none} · ${dateTime(row.closed_at)}`}
                        </dd>
                    </div>
                    <div className="grid gap-0.5">
                        <dt className="text-muted-foreground">
                            {t('admin:assignments.fields.close_reason')}
                        </dt>
                        <dd className="break-words">
                            {row.close_reason ?? none}
                        </dd>
                    </div>
                </>
            )}
        </dl>
    );

    const item = (row: AssignmentRow) => (
        <li
            key={row.public_id}
            className="flex flex-col gap-3 rounded-lg border border-border p-4"
            data-test="assignment-row"
        >
            <div className="flex flex-wrap items-start justify-between gap-3">
                <div className="min-w-0 space-y-1">
                    <p className="font-medium text-foreground">
                        {row.user.name}
                    </p>
                    <p className="font-mono text-sm text-muted-foreground">
                        {row.user.username}
                    </p>
                    {role(row)}
                </div>
                <div className="flex items-center gap-2">
                    <AssignmentBadge row={row} />
                    {row.can_close && (
                        <DropdownMenu>
                            <DropdownMenuTrigger asChild>
                                <Button
                                    variant="outline"
                                    size="icon"
                                    aria-label={t('admin:assignments.panel.more')}
                                    data-test="assignment-more"
                                >
                                    <EllipsisVertical aria-hidden="true" />
                                </Button>
                            </DropdownMenuTrigger>
                            <DropdownMenuContent align="end">
                                <DropdownMenuItem
                                    onSelect={() =>
                                        setClosing({ row, status: 'ENDED' })
                                    }
                                >
                                    {t('admin:assignments.panel.end')}
                                </DropdownMenuItem>
                                <DropdownMenuItem
                                    className="text-danger"
                                    onSelect={() =>
                                        setClosing({ row, status: 'REVOKED' })
                                    }
                                >
                                    {t('admin:assignments.panel.revoke')}
                                </DropdownMenuItem>
                            </DropdownMenuContent>
                        </DropdownMenu>
                    )}
                </div>
            </div>
            {details(row)}
        </li>
    );

    const closeText = closing?.status === 'REVOKED' ? 'revoke' : 'end';

    return (
        <section
            aria-labelledby="assignment-panel"
            className="flex flex-col gap-6 rounded-lg border border-border bg-card p-4 shadow-card sm:p-6"
            data-test="assignment-panel"
        >
            <div className="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                <div className="space-y-1">
                    <h2
                        id="assignment-panel"
                        className="text-lg font-semibold text-foreground"
                    >
                        {t('admin:assignments.panel.title')}
                    </h2>
                    {!panel.can.assign && (
                        <p className="text-sm text-muted-foreground">
                            {t('admin:assignments.panel.no_permission')}
                        </p>
                    )}
                </div>
                {panel.can.assign && (
                    <Button
                        onClick={() => openAssign()}
                        data-test="assign-button"
                    >
                        <UserPlus aria-hidden="true" />
                        {t('admin:assignments.panel.assign')}
                    </Button>
                )}
            </div>

            {failure && (
                <Alert className="border-danger" data-test="assignment-error">
                    <ShieldAlert aria-hidden="true" />
                    <AlertTitle>{t('admin:assignments.failed')}</AlertTitle>
                    <AlertDescription>{failure}</AlertDescription>
                </Alert>
            )}

            <div className="flex flex-col gap-3">
                <h3 className="font-medium text-foreground">
                    {t('admin:assignments.panel.active')}
                </h3>
                {active.length === 0 ? (
                    <p className="text-sm text-muted-foreground">
                        {t('admin:assignments.panel.empty')}
                        {panel.can.assign &&
                            ` ${t('admin:assignments.panel.empty_action')}`}
                    </p>
                ) : (
                    <ul className="grid gap-3">{active.map(item)}</ul>
                )}
            </div>

            {panel.suggestions.length > 0 && (
                <div
                    className="flex flex-col gap-3"
                    data-test="assignment-suggestions"
                >
                    <div className="space-y-1">
                        <h3 className="font-medium text-foreground">
                            {t('admin:assignments.suggestions.title')}
                        </h3>
                        <p className="text-sm text-muted-foreground">
                            {t('admin:assignments.suggestions.intro')}
                        </p>
                    </div>
                    <ul className="grid gap-3 sm:grid-cols-2">
                        {panel.suggestions.map((suggestion) => (
                            <li
                                key={`${suggestion.username}-${suggestion.position_name}`}
                                className="flex flex-col gap-2 rounded-lg border border-dashed border-border p-4"
                            >
                                <span className="font-medium text-foreground">
                                    {suggestion.name}
                                </span>
                                <span className="text-sm text-muted-foreground">
                                    {`${suggestion.position_name} · ${suggestion.unit_name}`}
                                </span>
                                {suggestion.decree_ref && (
                                    <span className="text-sm text-muted-foreground">
                                        {t('admin:assignments.suggestions.decree', {
                                            ref: suggestion.decree_ref,
                                        })}
                                    </span>
                                )}
                                <Button
                                    variant="outline"
                                    size="sm"
                                    className="self-start"
                                    onClick={() => openAssign(suggestion)}
                                >
                                    {t('admin:assignments.suggestions.use')}
                                </Button>
                            </li>
                        ))}
                    </ul>
                </div>
            )}

            <div className="flex flex-col gap-3">
                <h3 className="font-medium text-foreground">
                    {t('admin:assignments.panel.history')}
                </h3>
                {history.length === 0 ? (
                    <p className="text-sm text-muted-foreground">
                        {t('admin:assignments.panel.history_empty')}
                    </p>
                ) : (
                    <ul className="grid gap-3">{history.map(item)}</ul>
                )}
            </div>

            <Dialog open={assigning} onOpenChange={setAssigning}>
                <DialogContent className="max-h-[90vh] overflow-y-auto">
                    <DialogTitle>{t('admin:assignments.form.title')}</DialogTitle>
                    <DialogDescription>
                        {t('admin:assignments.form.intro')}
                    </DialogDescription>
                    <Form
                        {...AssignmentController.store.form()}
                        {...command.visit}
                        onSuccess={() => {
                            command.visit.onSuccess();
                            setAssigning(false);
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
                                    name="subject_type"
                                    value={panel.subject.type}
                                />
                                <input
                                    type="hidden"
                                    name="subject"
                                    value={panel.subject.reference}
                                />
                                <div className="grid gap-2">
                                    <Label htmlFor="assignment_role">
                                        {t(
                                            'admin:assignments.fields.assignment_role',
                                        )}
                                    </Label>
                                    <select
                                        id="assignment_role"
                                        name="assignment_role"
                                        required
                                        defaultValue=""
                                        aria-invalid={!!errors.assignment_role}
                                        className={selectClassName}
                                    >
                                        <option value="" disabled>
                                            {t('admin:assignments.form.choose')}
                                        </option>
                                        {panel.roles.map((option) => (
                                            <option
                                                key={option.value}
                                                value={option.value}
                                            >
                                                {option.decision
                                                    ? `${option.value} · ${t('admin:assignments.decision')}`
                                                    : option.value}
                                            </option>
                                        ))}
                                    </select>
                                    <InputError
                                        message={errors.assignment_role}
                                    />
                                </div>
                                <div className="grid gap-2">
                                    <Label htmlFor="assignment_username">
                                        {t('admin:assignments.fields.username')}
                                    </Label>
                                    <Input
                                        id="assignment_username"
                                        name="username"
                                        required
                                        maxLength={60}
                                        autoComplete="off"
                                        value={username}
                                        onChange={(event) =>
                                            setUsername(event.target.value)
                                        }
                                        aria-invalid={!!errors.username}
                                        aria-describedby="assignment_username_hint"
                                    />
                                    <p
                                        id="assignment_username_hint"
                                        className="text-sm text-muted-foreground"
                                    >
                                        {t('admin:assignments.form.username_hint')}
                                    </p>
                                    <InputError message={errors.username} />
                                </div>
                                <div className="grid gap-4 sm:grid-cols-2">
                                    <div className="grid gap-2">
                                        <Label htmlFor="assignment_effective_from">
                                            {t(
                                                'admin:assignments.fields.effective_from',
                                            )}
                                        </Label>
                                        <Input
                                            id="assignment_effective_from"
                                            name="effective_from"
                                            type="date"
                                            required
                                            min={panel.today}
                                            defaultValue={panel.today}
                                            aria-invalid={
                                                !!errors.effective_from
                                            }
                                        />
                                        <InputError
                                            message={errors.effective_from}
                                        />
                                    </div>
                                    <div className="grid gap-2">
                                        <Label htmlFor="assignment_effective_to">
                                            {t(
                                                'admin:assignments.fields.effective_to',
                                            )}
                                        </Label>
                                        <Input
                                            id="assignment_effective_to"
                                            name="effective_to"
                                            type="date"
                                            min={panel.today}
                                            aria-invalid={!!errors.effective_to}
                                            aria-describedby="assignment_effective_to_hint"
                                        />
                                        <p
                                            id="assignment_effective_to_hint"
                                            className="text-sm text-muted-foreground"
                                        >
                                            {t(
                                                'admin:assignments.form.effective_to_hint',
                                            )}
                                        </p>
                                        <InputError
                                            message={errors.effective_to}
                                        />
                                    </div>
                                </div>
                                <div className="grid gap-2">
                                    <Label htmlFor="assignment_decree_ref">
                                        {t(
                                            'admin:assignments.fields.decree_ref',
                                        )}
                                    </Label>
                                    <Input
                                        id="assignment_decree_ref"
                                        name="decree_ref"
                                        maxLength={150}
                                        value={decreeRef}
                                        onChange={(event) =>
                                            setDecreeRef(event.target.value)
                                        }
                                        aria-invalid={!!errors.decree_ref}
                                        aria-describedby="assignment_decree_ref_hint"
                                    />
                                    <p
                                        id="assignment_decree_ref_hint"
                                        className="text-sm text-muted-foreground"
                                    >
                                        {t(
                                            'admin:assignments.form.decree_ref_hint',
                                        )}
                                    </p>
                                    <InputError message={errors.decree_ref} />
                                </div>
                                <DialogFooter className="gap-2">
                                    <Button
                                        type="button"
                                        variant="secondary"
                                        onClick={() => setAssigning(false)}
                                    >
                                        {t('common:actions.cancel')}
                                    </Button>
                                    <Button
                                        type="submit"
                                        disabled={processing}
                                        data-test="confirm-assign-button"
                                    >
                                        {t('admin:assignments.form.submit')}
                                    </Button>
                                </DialogFooter>
                            </>
                        )}
                    </Form>
                </DialogContent>
            </Dialog>

            <Dialog
                open={closing !== null}
                onOpenChange={(open) => !open && setClosing(null)}
            >
                <DialogContent>
                    <DialogTitle>
                        {t(`admin:assignments.close.${closeText}_title`)}
                    </DialogTitle>
                    <DialogDescription>
                        {t(`admin:assignments.close.${closeText}_description`)}
                    </DialogDescription>
                    {closing && (
                        <Form
                            {...AssignmentController.close.form(
                                closing.row.public_id,
                            )}
                            {...command.visit}
                            onSuccess={() => {
                                command.visit.onSuccess();
                                setClosing(null);
                            }}
                            options={{ preserveScroll: true }}
                            className="grid gap-4"
                        >
                            {({ processing, errors }) => (
                                <>
                                    <InputError
                                        message={
                                            errors._idempotency ??
                                            errors._domain
                                        }
                                    />
                                    <input
                                        type="hidden"
                                        name="status"
                                        value={closing.status}
                                    />
                                    <input
                                        type="hidden"
                                        name="lock_version"
                                        value={closing.row.lock_version}
                                    />
                                    <div className="grid gap-2">
                                        <Label htmlFor="close_reason">
                                            {t(
                                                'admin:assignments.fields.close_reason',
                                            )}
                                        </Label>
                                        <textarea
                                            id="close_reason"
                                            name="reason"
                                            required
                                            minLength={5}
                                            maxLength={500}
                                            rows={3}
                                            placeholder={t(
                                                `admin:assignments.close.${closeText}_placeholder`,
                                            )}
                                            aria-invalid={!!errors.reason}
                                            className={textareaClassName}
                                        />
                                        <InputError message={errors.reason} />
                                    </div>
                                    <DialogFooter className="gap-2">
                                        <Button
                                            type="button"
                                            variant="secondary"
                                            onClick={() => setClosing(null)}
                                        >
                                            {t('common:actions.cancel')}
                                        </Button>
                                        <Button
                                            type="submit"
                                            variant="destructive"
                                            disabled={processing}
                                            data-test="confirm-close-assignment-button"
                                        >
                                            {t(
                                                `admin:assignments.close.${closeText}_confirm`,
                                            )}
                                        </Button>
                                    </DialogFooter>
                                </>
                            )}
                        </Form>
                    )}
                </DialogContent>
            </Dialog>
            <ConflictDialog command={command} />
        </section>
    );
}
