<?php

namespace App\Modules\Organization\Http\Controllers;

use App\Models\User;
use App\Modules\Organization\Actions\ApplyChangeRequest;
use App\Modules\Organization\Actions\CorrectChangeRequest;
use App\Modules\Organization\Actions\DecideChangeRequest;
use App\Modules\Organization\Actions\DraftChangeRequest;
use App\Modules\Organization\Actions\RequestDecision;
use App\Modules\Organization\Actions\ReturnChangeRequest;
use App\Modules\Organization\Actions\SubmitChangeRequest;
use App\Modules\Organization\Actions\WithdrawChangeRequest;
use App\Modules\Organization\Http\Requests\ChangeRequestForm;
use App\Modules\Organization\Http\Requests\DecisionForm;
use App\Modules\Organization\Http\Requests\ReasonForm;
use App\Modules\Organization\Http\Requests\VersionForm;
use App\Modules\Organization\Models\ChangeRequest;
use App\Modules\Organization\Models\ChangeRequestTransition;
use App\Modules\Organization\Models\OrgAppointment;
use App\Modules\Organization\Models\OrgPosition;
use App\Modules\Organization\Models\OrgUnit;
use App\Modules\Organization\Providers\OrganizationServiceProvider;
use App\Modules\Organization\Support\BusinessDate;
use App\Modules\Platform\Exceptions\StaleVersionException;
use Closure;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

/**
 * The change requests of PG-505 (WF-029 F1/F2, TR-281 to TR-288). Every route also carries can:admin.org.manage; each
 * method checks the same capability again.
 */
class ChangeRequestController
{
    public function create(Request $request): Response
    {
        Gate::authorize(OrganizationServiceProvider::MANAGE_ORGANIZATION);

        $kind = $request->string('kind')->value();
        $kinds = [...ChangeRequest::KINDS[ChangeRequest::F1], ...ChangeRequest::KINDS[ChangeRequest::F2]];
        $unit = OrgUnit::query()->with('parent')->where('public_id', $request->query('unit'))->first();

        return Inertia::render('admin/organization/request-form', [
            'changeRequest' => null,
            'kind' => in_array($kind, $kinds, true) ? $kind : ($unit === null ? ChangeRequest::CREATE_UNIT : ChangeRequest::UPDATE_UNIT),
            'unit' => $unit === null ? null : self::unitSummary($unit),
            ...$this->options($unit),
        ]);
    }

    public function store(ChangeRequestForm $request, DraftChangeRequest $draft): RedirectResponse
    {
        Gate::authorize(OrganizationServiceProvider::MANAGE_ORGANIZATION);

        $unit = $request->kind() === ChangeRequest::CREATE_UNIT
            ? null
            : OrgUnit::query()->where('public_id', $request->string('unit')->value())->firstOrFail();

        $created = $draft->handle($request->user(), $unit, ['kind' => $request->kind(), ...$request->details()]);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('organization.request_saved')]);

        return to_route('organization.admin.requests.show', $created->public_id);
    }

    public function show(Request $request, ChangeRequest $changeRequest, DecideChangeRequest $decide): Response
    {
        Gate::authorize(OrganizationServiceProvider::MANAGE_ORGANIZATION);

        return Inertia::render('admin/organization/request', $this->detail($changeRequest, $decide));
    }

    public function edit(ChangeRequest $changeRequest): Response|RedirectResponse
    {
        Gate::authorize(OrganizationServiceProvider::MANAGE_ORGANIZATION);

        if (! in_array($changeRequest->status, [ChangeRequest::DRAFT, ChangeRequest::VALIDATED], true)) {
            return to_route('organization.admin.requests.show', $changeRequest->public_id);
        }

        return Inertia::render('admin/organization/request-form', $this->form($changeRequest));
    }

    public function update(ChangeRequestForm $request, ChangeRequest $changeRequest, CorrectChangeRequest $correct): RedirectResponse
    {
        Gate::authorize(OrganizationServiceProvider::MANAGE_ORGANIZATION);

        try {
            $correct->handle($request->user(), $changeRequest, $request->details(), $request->integer('lock_version'));
        } catch (StaleVersionException $exception) {
            throw $exception->showing('admin/organization/request-form', $this->form($changeRequest->fresh() ?? $changeRequest));
        }

        return $this->done($changeRequest, 'organization.request_saved');
    }

    public function submit(VersionForm $request, ChangeRequest $changeRequest, SubmitChangeRequest $submit, DecideChangeRequest $decide): RedirectResponse
    {
        Gate::authorize(OrganizationServiceProvider::MANAGE_ORGANIZATION);

        $this->stale(fn () => $submit->handle($request->user(), $changeRequest, $request->integer('lock_version')), $changeRequest, $decide);

        return $this->done($changeRequest, 'organization.request_submitted');
    }

    public function returnToDraft(ReasonForm $request, ChangeRequest $changeRequest, ReturnChangeRequest $return, DecideChangeRequest $decide): RedirectResponse
    {
        Gate::authorize(OrganizationServiceProvider::MANAGE_ORGANIZATION);

        $this->stale(fn () => $return->handle(
            $request->user(),
            $changeRequest,
            trim($request->string('reason')->value()),
            $request->integer('lock_version'),
        ), $changeRequest, $decide);

        return $this->done($changeRequest, 'organization.request_returned');
    }

    public function escalate(VersionForm $request, ChangeRequest $changeRequest, RequestDecision $escalate, DecideChangeRequest $decide): RedirectResponse
    {
        Gate::authorize(OrganizationServiceProvider::MANAGE_ORGANIZATION);

        $this->stale(fn () => $escalate->handle($request->user(), $changeRequest, $request->integer('lock_version')), $changeRequest, $decide);

        return $this->done($changeRequest, 'organization.request_escalated');
    }

    public function decide(DecisionForm $request, ChangeRequest $changeRequest, DecideChangeRequest $decide): RedirectResponse
    {
        Gate::authorize(OrganizationServiceProvider::MANAGE_ORGANIZATION);

        $this->stale(fn () => $decide->handle(
            $request->user(),
            $changeRequest,
            $request->details(),
            $request->integer('lock_version'),
        ), $changeRequest, $decide);

        return $this->done($changeRequest, 'organization.request_decided');
    }

    public function apply(VersionForm $request, ChangeRequest $changeRequest, ApplyChangeRequest $apply, DecideChangeRequest $decide): RedirectResponse
    {
        Gate::authorize(OrganizationServiceProvider::MANAGE_ORGANIZATION);

        $applied = $this->stale(fn () => $apply->handle($request->user(), $changeRequest, $request->integer('lock_version')), $changeRequest, $decide);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('organization.request_applied')]);

        // Success goes to the unit and its history (TASK-013 navigation contract).
        $unit = OrgUnit::query()->find($applied->org_unit_id);

        return $unit === null
            ? to_route('organization.admin.requests.show', $changeRequest->public_id)
            : to_route('organization.admin.index', ['unit' => $unit->public_id]);
    }

    public function withdraw(ReasonForm $request, ChangeRequest $changeRequest, WithdrawChangeRequest $withdraw, DecideChangeRequest $decide): RedirectResponse
    {
        Gate::authorize(OrganizationServiceProvider::MANAGE_ORGANIZATION);

        $this->stale(fn () => $withdraw->handle(
            $request->user(),
            $changeRequest,
            trim($request->string('reason')->value()),
            $request->integer('lock_version'),
        ), $changeRequest, $decide);

        return $this->done($changeRequest, 'organization.request_withdrawn');
    }

    /**
     * @return array<string, mixed>
     */
    public static function summary(ChangeRequest $changeRequest): array
    {
        return [
            'public_id' => $changeRequest->public_id,
            'family' => $changeRequest->family,
            'kind' => $changeRequest->kind,
            'status' => $changeRequest->status,
            'unit' => $changeRequest->unit?->name,
            'label' => $changeRequest->payload['name'] ?? $changeRequest->payload['code'] ?? null,
            'proposed_effective_from' => $changeRequest->proposed_effective_from->toDateString(),
            'effective_from' => $changeRequest->effective_from?->toDateString(),
            'is_simulation' => $changeRequest->is_simulation,
            'created_at' => $changeRequest->created_at?->toIso8601String(),
        ];
    }

    /**
     * Run a transition; a version conflict answers with this request's latest detail and the conflict dialog (IX-002).
     *
     * @param  Closure(): ChangeRequest  $transition
     */
    private function stale(Closure $transition, ChangeRequest $changeRequest, DecideChangeRequest $decide): ChangeRequest
    {
        try {
            return $transition();
        } catch (StaleVersionException $exception) {
            throw $exception->showing('admin/organization/request', $this->detail($changeRequest->fresh() ?? $changeRequest, $decide));
        }
    }

    private function done(ChangeRequest $changeRequest, string $message): RedirectResponse
    {
        Inertia::flash('toast', ['type' => 'success', 'message' => __($message)]);

        return to_route('organization.admin.requests.show', $changeRequest->public_id);
    }

    /**
     * @return array<string, mixed>
     */
    private function detail(ChangeRequest $changeRequest, DecideChangeRequest $decide): array
    {
        $changeRequest->loadMissing(['unit.parent', 'creator:id,name', 'decider:id,name']);
        $status = $changeRequest->status;
        $decision = $decide->availability();
        $today = BusinessDate::today()->toDateString();

        return [
            'changeRequest' => [
                ...self::summary($changeRequest),
                'payload' => $changeRequest->payload,
                'scope' => $this->scopeLabels($changeRequest),
                'reason' => $changeRequest->reason,
                'source_ref' => $changeRequest->source_ref,
                'validation' => $changeRequest->validation,
                'decision_ref' => $changeRequest->decision_ref,
                'closed_reason' => $changeRequest->closed_reason,
                'created_by' => $changeRequest->creator?->name,
                'decided_by' => $changeRequest->decider?->name,
                'decided_at' => $changeRequest->decided_at?->toIso8601String(),
                'applied_at' => $changeRequest->applied_at?->toIso8601String(),
                'lock_version' => $changeRequest->lock_version,
            ],
            'unit' => $changeRequest->unit === null ? null : self::unitSummary($changeRequest->unit),
            'transitions' => $changeRequest->transitions()
                ->with('actor:id,name')
                ->orderBy('acted_at')
                ->orderBy('id')
                ->get()
                ->map(fn (ChangeRequestTransition $transition): array => [
                    'id' => $transition->id,
                    'transition_code' => $transition->transition_code,
                    'from_state' => $transition->from_state,
                    'to_state' => $transition->to_state,
                    'actor' => $transition->actor?->name,
                    'acted_at' => $transition->acted_at->toIso8601String(),
                    'reason' => $transition->reason,
                    'is_simulation' => $transition->is_simulation,
                ])
                ->all(),
            'today' => $today,
            // TAC-04: the decision is offered only while it may be recorded; the reason is shown otherwise.
            'decision' => [
                'available' => $decision !== null,
                'simulation' => $decision === true,
            ],
            'can' => [
                'edit' => in_array($status, [ChangeRequest::DRAFT, ChangeRequest::VALIDATED], true),
                'submit' => $status === ChangeRequest::DRAFT,
                'return' => $status === ChangeRequest::VALIDATED,
                'escalate' => $status === ChangeRequest::VALIDATED && ! $changeRequest->hasBlockingIssues(),
                'decide' => $status === ChangeRequest::AWAITING_DECISION && $decision !== null,
                'apply' => $status === ChangeRequest::APPROVED && $changeRequest->effective_from?->toDateString() <= $today,
                'withdraw' => in_array($status, ChangeRequest::OPEN, true),
            ],
        ];
    }

    /**
     * Names for the ids in the payload, so the page shows what the request changes.
     *
     * @return array<string, string|null>
     */
    private function scopeLabels(ChangeRequest $changeRequest): array
    {
        $payload = $changeRequest->payload;
        $labels = [];

        if (array_key_exists('parent', $payload)) {
            $labels['parent'] = is_string($payload['parent'])
                ? OrgUnit::query()->where('public_id', $payload['parent'])->value('name')
                : null;
        }

        if (isset($payload['user'])) {
            $labels['user'] = User::query()->where('public_id', $payload['user'])->value('name');
        }

        if (isset($payload['position'])) {
            $labels['position'] = OrgPosition::query()->where('code', $payload['position'])->value('name');
        }

        if (isset($payload['appointment'])) {
            $appointment = OrgAppointment::query()->with(['user:id,name', 'position:id,name'])->find($payload['appointment']);
            $labels['appointment'] = $appointment === null ? null : "{$appointment->user->name} — {$appointment->position->name}";
        }

        return $labels;
    }

    /**
     * @return array<string, mixed>
     */
    private function form(ChangeRequest $changeRequest): array
    {
        $changeRequest->loadMissing('unit.parent');

        return [
            'changeRequest' => [
                'public_id' => $changeRequest->public_id,
                'status' => $changeRequest->status,
                'payload' => $changeRequest->payload,
                'reason' => $changeRequest->reason,
                'source_ref' => $changeRequest->source_ref,
                'proposed_effective_from' => $changeRequest->proposed_effective_from->toDateString(),
                'lock_version' => $changeRequest->lock_version,
            ],
            'kind' => $changeRequest->kind,
            'unit' => $changeRequest->unit === null ? null : self::unitSummary($changeRequest->unit),
            ...$this->options($changeRequest->unit),
        ];
    }

    /**
     * The choices of the form: units for a parent, unit types already in use, staff for an appointment, the positions and
     * active appointments of the unit.
     *
     * @return array<string, mixed>
     */
    private function options(?OrgUnit $unit): array
    {
        return [
            'options' => [
                'units' => OrgUnit::query()
                    ->where('status', OrgUnit::ACTIVE)
                    ->when($unit !== null, fn ($query) => $query->whereRaw('not (path <@ ?::ltree)', [$unit?->path]))
                    ->orderBy('path')
                    ->get(['public_id', 'name', 'code', 'depth'])
                    ->map(fn (OrgUnit $option): array => [
                        'value' => $option->public_id,
                        'label' => "{$option->name} ({$option->code})",
                        'depth' => $option->depth,
                    ])
                    ->all(),
                'unitTypes' => OrgUnit::query()->distinct()->orderBy('unit_type')->pluck('unit_type')->all(),
                'people' => $unit === null ? [] : User::query()
                    ->where('role_group', User::INTERNAL)
                    ->where('status', '!=', User::DEACTIVATED)
                    ->orderBy('name')
                    ->get(['public_id', 'name', 'username'])
                    ->map(fn (User $user): array => ['value' => $user->public_id, 'label' => "{$user->name} ({$user->username})"])
                    ->all(),
                'positions' => OrgPosition::query()
                    ->where('status', OrgPosition::ACTIVE)
                    ->orderBy('name')
                    ->get(['code', 'name'])
                    ->map(fn (OrgPosition $position): array => ['value' => $position->code, 'label' => "{$position->name} ({$position->code})"])
                    ->all(),
                'appointments' => $unit === null ? [] : OrgAppointment::query()
                    ->with(['user:id,name', 'position:id,name'])
                    ->where('org_unit_id', $unit->id)
                    ->where('status', OrgAppointment::ACTIVE)
                    ->get()
                    ->map(fn (OrgAppointment $appointment): array => [
                        'value' => $appointment->id,
                        'label' => "{$appointment->user->name} — {$appointment->position->name}",
                    ])
                    ->all(),
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private static function unitSummary(OrgUnit $unit): array
    {
        return [
            'public_id' => $unit->public_id,
            'code' => $unit->code,
            'name' => $unit->name,
            'short_name' => $unit->short_name,
            'unit_type' => $unit->unit_type,
            'parent' => $unit->parent?->public_id,
            'parent_name' => $unit->parent?->name,
            'status' => $unit->status,
        ];
    }
}
