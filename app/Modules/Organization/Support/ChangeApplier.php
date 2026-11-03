<?php

namespace App\Modules\Organization\Support;

use App\Models\User;
use App\Modules\Organization\Models\ChangeRequest;
use App\Modules\Organization\Models\OrgAppointment;
use App\Modules\Organization\Models\OrgPosition;
use App\Modules\Organization\Models\OrgUnit;
use App\Modules\Organization\Models\OrgUnitHistory;
use App\Modules\Platform\Exceptions\DomainRuleViolation;
use Illuminate\Support\Facades\DB;
use LogicException;

/**
 * The effect of TR-286: writes the approved change into the organization, inside the transaction of the Action. Every unit
 * change appends a history row with the version before and after it, so an earlier version is never overwritten (TAC-02).
 */
final class ChangeApplier
{
    public function apply(ChangeRequest $request, User $actor): void
    {
        match ($request->kind) {
            ChangeRequest::CREATE_UNIT => $this->createUnit($request, $actor),
            ChangeRequest::UPDATE_UNIT => $this->updateUnit($request, $actor),
            ChangeRequest::MOVE_UNIT => $this->moveUnit($request, $actor),
            ChangeRequest::DEACTIVATE_UNIT => $this->setStatus($request, $actor, OrgUnit::INACTIVE, OrgUnitHistory::DEACTIVATE),
            ChangeRequest::ACTIVATE_UNIT => $this->setStatus($request, $actor, OrgUnit::ACTIVE, OrgUnitHistory::ACTIVATE),
            ChangeRequest::CREATE_POSITION => $this->createPosition($request, $actor),
            ChangeRequest::APPOINT => $this->appoint($request, $actor),
            ChangeRequest::END_APPOINTMENT => $this->endAppointment($request, $actor),
            default => throw new LogicException("Unknown change kind [{$request->kind}]."),
        };
    }

    private function createUnit(ChangeRequest $request, User $actor): void
    {
        $payload = $request->payload;
        $parent = self::lockedUnit($payload['parent'] ?? null);

        $unit = new OrgUnit([
            'code' => $payload['code'],
            'name' => $payload['name'],
            'short_name' => $payload['short_name'] ?? null,
            'unit_type' => $payload['unit_type'],
            'parent_id' => $parent?->id,
            'status' => OrgUnit::ACTIVE,
            'effective_from' => $request->effective_from,
            'created_by' => $actor->id,
            'updated_by' => $actor->id,
        ]);

        // The path ends with the unit's own public id, which HasUlids sets on creating; so it is filled in before the insert.
        $unit->public_id = $unit->newUniqueId();
        $unit->path = UnitPath::below($parent, $unit->public_id);
        $unit->depth = UnitPath::depth($unit->path);
        $unit->save();

        $request->org_unit_id = $unit->id;

        $this->history($unit, $request, $actor, OrgUnitHistory::CREATE, null, $unit->snapshot());
    }

    private function updateUnit(ChangeRequest $request, User $actor): void
    {
        $unit = self::lockedSubject($request);
        $before = $unit->snapshot();

        $unit->forceFill([
            'name' => $request->payload['name'],
            'short_name' => $request->payload['short_name'] ?? null,
            'unit_type' => $request->payload['unit_type'],
            'updated_by' => $actor->id,
        ])->saveAtVersion($unit->lock_version);

        $type = $before['unit_type'] === $unit->unit_type ? OrgUnitHistory::RENAME : OrgUnitHistory::CORRECT;

        $this->history($unit, $request, $actor, $type, $before, $unit->snapshot());
    }

    /**
     * The whole subtree takes the new path prefix in one statement (TAC-01). The moved unit and its subtree are locked first,
     * so a move that races this one waits and then validates against the new tree.
     */
    private function moveUnit(ChangeRequest $request, User $actor): void
    {
        $unit = self::lockedSubject($request);
        $parent = self::lockedUnit($request->payload['parent'] ?? null);
        $before = $unit->snapshot();

        OrgUnit::query()->withinSubtreeOf($unit)->lockForUpdate()->get(['id']);

        $newPath = UnitPath::below($parent, $unit->public_id);

        DB::update(
            'update org_units set path = ?::ltree || subpath(path, nlevel(?::ltree)), depth = nlevel(?::ltree) - 1 + nlevel(path) - nlevel(?::ltree), updated_at = now(), updated_by = ?'
            .' where path <@ ?::ltree and id <> ?',
            [$newPath, $unit->path, $newPath, $unit->path, $actor->id, $unit->path, $unit->id],
        );

        $unit->forceFill([
            'parent_id' => $parent?->id,
            'path' => $newPath,
            'depth' => UnitPath::depth($newPath),
            'updated_by' => $actor->id,
        ])->saveAtVersion($unit->lock_version);

        $unit->setRelation('parent', $parent);

        $this->history($unit, $request, $actor, OrgUnitHistory::MOVE, $before, $unit->snapshot());
    }

    private function setStatus(ChangeRequest $request, User $actor, string $status, string $type): void
    {
        $unit = self::lockedSubject($request);
        $before = $unit->snapshot();

        $unit->forceFill(['status' => $status, 'updated_by' => $actor->id])->saveAtVersion($unit->lock_version);

        $this->history($unit, $request, $actor, $type, $before, $unit->snapshot());
    }

    private function createPosition(ChangeRequest $request, User $actor): void
    {
        OrgPosition::query()->create([
            'code' => $request->payload['code'],
            'name' => $request->payload['name'],
            'description' => $request->payload['description'] ?? null,
            'org_unit_id' => $request->org_unit_id,
            'status' => OrgPosition::ACTIVE,
            'created_by' => $actor->id,
            'updated_by' => $actor->id,
        ]);
    }

    private function appoint(ChangeRequest $request, User $actor): void
    {
        $payload = $request->payload;
        $user = User::query()->where('public_id', $payload['user'])->firstOrFail();
        $position = OrgPosition::query()->where('code', $payload['position'])->firstOrFail();
        $effectiveTo = $payload['effective_to'] ?? null;

        if ($effectiveTo !== null && $effectiveTo <= $request->effective_from?->toDateString()) {
            throw DomainRuleViolation::because('organization.appointment_ends_before_start');
        }

        OrgAppointment::query()->create([
            'user_id' => $user->id,
            'position_id' => $position->id,
            'org_unit_id' => $request->org_unit_id,
            'effective_from' => $request->effective_from,
            'effective_to' => $effectiveTo,
            'decree_ref' => $payload['decree_ref'],
            'status' => OrgAppointment::ACTIVE,
            'change_request_id' => $request->id,
            'created_by' => $actor->id,
            'updated_by' => $actor->id,
        ]);
    }

    private function endAppointment(ChangeRequest $request, User $actor): void
    {
        $appointment = OrgAppointment::query()->whereKey($request->payload['appointment'])->lockForUpdate()->firstOrFail();

        if ($request->effective_from?->toDateString() <= $appointment->effective_from->toDateString()) {
            throw DomainRuleViolation::because('organization.appointment_ends_before_start');
        }

        $appointment->forceFill([
            'status' => OrgAppointment::ENDED,
            'effective_to' => $request->effective_from,
            'updated_by' => $actor->id,
        ])->save();
    }

    /**
     * @param  array<string, mixed>|null  $before
     * @param  array<string, mixed>  $after
     */
    private function history(OrgUnit $unit, ChangeRequest $request, User $actor, string $type, ?array $before, array $after): void
    {
        $unit->history()->create([
            'change_type' => $type,
            'before' => $before,
            'after' => $after,
            'effective_from' => $request->effective_from,
            'decision_ref' => $request->decision_ref,
            'change_request_id' => $request->id,
            'is_simulation' => $request->is_simulation,
            'changed_by' => $actor->id,
            'changed_at' => now(),
        ]);
    }

    private static function lockedSubject(ChangeRequest $request): OrgUnit
    {
        return OrgUnit::query()->with('parent')->lockForUpdate()->findOrFail($request->org_unit_id);
    }

    private static function lockedUnit(mixed $publicId): ?OrgUnit
    {
        return is_string($publicId) ? OrgUnit::query()->where('public_id', $publicId)->lockForUpdate()->firstOrFail() : null;
    }
}
