<?php

namespace App\Modules\Organization\Http\Controllers;

use App\Modules\Organization\Models\ChangeRequest;
use App\Modules\Organization\Models\OrgAppointment;
use App\Modules\Organization\Models\OrgPosition;
use App\Modules\Organization\Models\OrgUnit;
use App\Modules\Organization\Models\OrgUnitHistory;
use App\Modules\Organization\Providers\OrganizationServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

/**
 * PG-505 Organisasi (INFORMATION_ARCHITECTURE section 3.6): the searchable unit tree, and the selected unit with its history,
 * positions, appointments and change requests. The selected unit is in the URL (?unit=), so back and reload keep it.
 */
class OrganizationController
{
    public function index(Request $request): Response
    {
        Gate::authorize(OrganizationServiceProvider::MANAGE_ORGANIZATION);

        $units = OrgUnit::query()
            ->with('parent:id,public_id')
            ->orderBy('path')
            ->get()
            ->map(fn (OrgUnit $unit): array => [
                'public_id' => $unit->public_id,
                'code' => $unit->code,
                'name' => $unit->name,
                'short_name' => $unit->short_name,
                'unit_type' => $unit->unit_type,
                'parent' => $unit->parent?->public_id,
                'depth' => $unit->depth,
                'status' => $unit->status,
            ])
            ->all();

        $selected = OrgUnit::query()->with('parent')->where('public_id', $request->query('unit'))->first();

        return Inertia::render('admin/organization/index', [
            'units' => $units,
            'selected' => $selected === null ? null : $this->detail($selected),
            'openRequests' => ChangeRequest::query()
                ->with('unit:id,public_id,name')
                ->whereIn('status', ChangeRequest::OPEN)
                ->latest('id')
                ->limit(50)
                ->get()
                ->map(fn (ChangeRequest $changeRequest): array => ChangeRequestController::summary($changeRequest))
                ->all(),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function detail(OrgUnit $unit): array
    {
        $ancestors = OrgUnit::query()
            ->whereRaw('path @> ?::ltree', [$unit->path])
            ->whereKeyNot($unit->id)
            ->orderBy('depth')
            ->get(['public_id', 'name']);

        return [
            'unit' => [
                ...$unit->snapshot(),
                'public_id' => $unit->public_id,
                'parent_name' => $unit->parent?->name,
                'depth' => $unit->depth,
                'lock_version' => $unit->lock_version,
            ],
            'ancestors' => $ancestors->map(fn (OrgUnit $ancestor): array => [
                'public_id' => $ancestor->public_id,
                'name' => $ancestor->name,
            ])->all(),
            'history' => $unit->history()
                ->with('changer:id,name')
                ->orderByDesc('changed_at')
                ->orderByDesc('id')
                ->get()
                ->map(fn (OrgUnitHistory $entry): array => [
                    'id' => $entry->id,
                    'change_type' => $entry->change_type,
                    'before' => $entry->before,
                    'after' => $entry->after,
                    'effective_from' => $entry->effective_from->toDateString(),
                    'decision_ref' => $entry->decision_ref,
                    'is_simulation' => $entry->is_simulation,
                    'changed_by' => $entry->changer?->name,
                    'changed_at' => $entry->changed_at->toIso8601String(),
                ])
                ->all(),
            'positions' => OrgPosition::query()
                ->where('org_unit_id', $unit->id)
                ->orderBy('name')
                ->get()
                ->map(fn (OrgPosition $position): array => [
                    'code' => $position->code,
                    'name' => $position->name,
                    'description' => $position->description,
                    'status' => $position->status,
                ])
                ->all(),
            'appointments' => OrgAppointment::query()
                ->with(['user:id,name,username', 'position:id,code,name'])
                ->where('org_unit_id', $unit->id)
                ->orderByRaw("status = 'ACTIVE' desc")
                ->orderByDesc('effective_from')
                ->get()
                ->map(fn (OrgAppointment $appointment): array => [
                    'id' => $appointment->id,
                    'person' => $appointment->user->name,
                    'username' => $appointment->user->username,
                    'position' => $appointment->position->name,
                    'position_code' => $appointment->position->code,
                    'effective_from' => $appointment->effective_from->toDateString(),
                    'effective_to' => $appointment->effective_to?->toDateString(),
                    'decree_ref' => $appointment->decree_ref,
                    // The decree file lives on the evidence page of the appointment (PG-012, TASK-016).
                    'has_evidence' => $appointment->evidence_file_id !== null,
                    'status' => $appointment->status,
                ])
                ->all(),
            'requests' => ChangeRequest::query()
                ->where('org_unit_id', $unit->id)
                ->latest('id')
                ->limit(50)
                ->get()
                ->map(fn (ChangeRequest $changeRequest): array => ChangeRequestController::summary($changeRequest))
                ->all(),
        ];
    }
}
