<?php

namespace App\Modules\Organization\Support;

use App\Models\User;
use App\Modules\Organization\Models\ChangeRequest;
use App\Modules\Organization\Models\OrgAppointment;
use App\Modules\Organization\Models\OrgPosition;
use App\Modules\Organization\Models\OrgUnit;

/**
 * The checks of WF029-S2 (TR-282, TR-284 and again before TR-286): conflicts with the organization in force, duplicate
 * identities and missing ground. A blocking issue keeps the request from going on; any other issue is shown to the reviewer.
 *
 * Each issue code is a translation key under organization.issues.
 */
final class ChangeValidator
{
    /**
     * @return list<array{code: string, blocking: bool}>
     */
    public function issues(ChangeRequest $request): array
    {
        $payload = $request->payload;
        $unit = $request->org_unit_id === null ? null : OrgUnit::query()->find($request->org_unit_id);

        $issues = match ($request->kind) {
            ChangeRequest::CREATE_UNIT => $this->createUnit($payload),
            ChangeRequest::UPDATE_UNIT => $this->updateUnit($unit, $payload),
            ChangeRequest::MOVE_UNIT => $this->moveUnit($unit, $payload),
            ChangeRequest::DEACTIVATE_UNIT => $this->deactivateUnit($unit),
            ChangeRequest::ACTIVATE_UNIT => $this->activateUnit($unit),
            ChangeRequest::CREATE_POSITION => $this->createPosition($unit, $payload),
            ChangeRequest::APPOINT => $this->appoint($unit, $payload),
            ChangeRequest::END_APPOINTMENT => $this->endAppointment($unit, $payload),
            default => [self::blocking('unknown_kind')],
        };

        // Another open request on the same unit may conflict; the reviewer decides (non-blocking).
        if ($request->org_unit_id !== null && $this->hasOtherOpenRequest($request)) {
            $issues[] = self::notice('other_open_request');
        }

        return $issues;
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return list<array{code: string, blocking: bool}>
     */
    private function createUnit(array $payload): array
    {
        $issues = [];

        if (OrgUnit::query()->where('code', $payload['code'] ?? null)->exists()) {
            $issues[] = self::blocking('code_taken');
        }

        $parent = self::unitByPublicId($payload['parent'] ?? null);

        if (($payload['parent'] ?? null) !== null && $parent === null) {
            $issues[] = self::blocking('parent_missing');
        } elseif ($parent !== null && $parent->status !== OrgUnit::ACTIVE) {
            $issues[] = self::blocking('parent_inactive');
        }

        $sameName = OrgUnit::query()
            ->whereRaw('lower(name) = lower(?)', [(string) ($payload['name'] ?? '')])
            ->where('parent_id', $parent?->id)
            ->exists();

        if ($sameName) {
            $issues[] = self::notice('name_duplicate');
        }

        return $issues;
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return list<array{code: string, blocking: bool}>
     */
    private function updateUnit(?OrgUnit $unit, array $payload): array
    {
        if ($unit === null) {
            return [self::blocking('unit_missing')];
        }

        $unchanged = $unit->name === ($payload['name'] ?? null)
            && $unit->short_name === ($payload['short_name'] ?? null)
            && $unit->unit_type === ($payload['unit_type'] ?? null);

        return $unchanged ? [self::blocking('nothing_changes')] : [];
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return list<array{code: string, blocking: bool}>
     */
    private function moveUnit(?OrgUnit $unit, array $payload): array
    {
        if ($unit === null) {
            return [self::blocking('unit_missing')];
        }

        $parent = self::unitByPublicId($payload['parent'] ?? null);

        if (($payload['parent'] ?? null) !== null && $parent === null) {
            return [self::blocking('parent_missing')];
        }

        if ($parent?->id === $unit->parent_id) {
            return [self::blocking('nothing_changes')];
        }

        // A unit can never move below itself: that would make a cycle.
        if ($parent !== null && OrgUnit::query()->whereKey($parent->id)->withinSubtreeOf($unit)->exists()) {
            return [self::blocking('move_into_own_subtree')];
        }

        if ($parent !== null && $parent->status !== OrgUnit::ACTIVE) {
            return [self::blocking('parent_inactive')];
        }

        return [];
    }

    /**
     * @return list<array{code: string, blocking: bool}>
     */
    private function deactivateUnit(?OrgUnit $unit): array
    {
        if ($unit === null) {
            return [self::blocking('unit_missing')];
        }

        if ($unit->status !== OrgUnit::ACTIVE) {
            return [self::blocking('unit_not_active')];
        }

        $issues = [];

        if (OrgUnit::query()->where('parent_id', $unit->id)->where('status', OrgUnit::ACTIVE)->exists()) {
            $issues[] = self::blocking('has_active_children');
        }

        if (OrgAppointment::query()->where('org_unit_id', $unit->id)->where('status', OrgAppointment::ACTIVE)->exists()) {
            $issues[] = self::blocking('has_active_appointments');
        }

        return $issues;
    }

    /**
     * @return list<array{code: string, blocking: bool}>
     */
    private function activateUnit(?OrgUnit $unit): array
    {
        if ($unit === null) {
            return [self::blocking('unit_missing')];
        }

        if ($unit->status === OrgUnit::ACTIVE) {
            return [self::blocking('unit_already_active')];
        }

        if ($unit->parent !== null && $unit->parent->status !== OrgUnit::ACTIVE) {
            return [self::blocking('parent_inactive')];
        }

        return [];
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return list<array{code: string, blocking: bool}>
     */
    private function createPosition(?OrgUnit $unit, array $payload): array
    {
        $issues = [];

        if ($unit === null) {
            $issues[] = self::blocking('unit_missing');
        } elseif ($unit->status !== OrgUnit::ACTIVE) {
            $issues[] = self::blocking('unit_not_active');
        }

        if (OrgPosition::query()->where('code', $payload['code'] ?? null)->exists()) {
            $issues[] = self::blocking('code_taken');
        }

        return $issues;
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return list<array{code: string, blocking: bool}>
     */
    private function appoint(?OrgUnit $unit, array $payload): array
    {
        $issues = [];

        if ($unit === null) {
            $issues[] = self::blocking('unit_missing');
        } elseif ($unit->status !== OrgUnit::ACTIVE) {
            $issues[] = self::blocking('unit_not_active');
        }

        $user = User::query()->where('public_id', $payload['user'] ?? null)->first();

        if ($user === null) {
            $issues[] = self::blocking('person_missing');
        } elseif ($user->status === User::DEACTIVATED || $user->role_group !== User::INTERNAL) {
            // Positions are held by staff accounts only (ACCESS_CONTROL section 2).
            $issues[] = self::blocking('person_not_eligible');
        }

        $position = OrgPosition::query()->where('code', $payload['position'] ?? null)->first();

        if ($position === null) {
            $issues[] = self::blocking('position_missing');
        } elseif ($position->status !== OrgPosition::ACTIVE) {
            $issues[] = self::blocking('position_inactive');
        }

        if (blank($payload['decree_ref'] ?? null)) {
            $issues[] = self::blocking('decree_missing');
        }

        if ($user !== null && $position !== null) {
            $held = OrgAppointment::query()
                ->where('user_id', $user->id)
                ->where('position_id', $position->id)
                ->where('status', OrgAppointment::ACTIVE)
                ->exists();

            if ($held) {
                $issues[] = self::blocking('already_appointed');
            }
        }

        return $issues;
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return list<array{code: string, blocking: bool}>
     */
    private function endAppointment(?OrgUnit $unit, array $payload): array
    {
        $appointment = OrgAppointment::query()->whereKey($payload['appointment'] ?? null)->first();

        if ($appointment === null || $appointment->org_unit_id !== $unit?->id) {
            return [self::blocking('appointment_missing')];
        }

        if ($appointment->status !== OrgAppointment::ACTIVE) {
            return [self::blocking('appointment_not_active')];
        }

        return [];
    }

    private function hasOtherOpenRequest(ChangeRequest $request): bool
    {
        return ChangeRequest::query()
            ->where('org_unit_id', $request->org_unit_id)
            ->whereKeyNot($request->id)
            ->whereIn('status', [ChangeRequest::VALIDATED, ChangeRequest::AWAITING_DECISION, ChangeRequest::APPROVED])
            ->exists();
    }

    private static function unitByPublicId(mixed $publicId): ?OrgUnit
    {
        return is_string($publicId) ? OrgUnit::query()->where('public_id', $publicId)->first() : null;
    }

    /**
     * @return array{code: string, blocking: bool}
     */
    private static function blocking(string $code): array
    {
        return ['code' => $code, 'blocking' => true];
    }

    /**
     * @return array{code: string, blocking: bool}
     */
    private static function notice(string $code): array
    {
        return ['code' => $code, 'blocking' => false];
    }
}
