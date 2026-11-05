<?php

namespace App\Modules\Organization\Queries;

use App\Modules\Organization\Support\BusinessDate;
use Illuminate\Support\Facades\DB;

/**
 * The public read of appointments for other modules (ARCHITECTURE section 5 rule 2). An appointment only suggests: it never
 * grants a permission and never becomes an assignment by itself (OD-28, ACCESS_CONTROL section 5).
 */
final class Appointments
{
    /**
     * The people appointed today to a position of the unit or of a unit above it, nearest unit first, as suggestions for an
     * assignment on an object of that unit. The unit of an appointment is its own, else the unit of its position.
     *
     * @return list<array{user_id: int, position_code: string, position_name: string, unit_code: string, unit_name: string, decree_ref: string|null}>
     */
    public static function suggestionsFor(int $unitId): array
    {
        $today = BusinessDate::today()->toDateString();

        return array_values(DB::table('org_appointments as a')
            ->join('org_positions as p', 'p.id', '=', 'a.position_id')
            ->join('org_units as u', 'u.id', '=', DB::raw('coalesce(a.org_unit_id, p.org_unit_id)'))
            ->join('users', 'users.id', '=', 'a.user_id')
            ->whereRaw('(select target.path from org_units target where target.id = ?) <@ u.path', [$unitId])
            ->where('a.status', 'ACTIVE')
            ->where('p.status', 'ACTIVE')
            ->where('users.status', 'ACTIVE')
            ->where('a.effective_from', '<=', $today)
            ->where(fn ($query) => $query->whereNull('a.effective_to')->orWhere('a.effective_to', '>', $today))
            ->orderByDesc('u.depth')
            ->orderBy('p.name')
            ->orderBy('a.id')
            ->limit(50)
            ->get(['a.user_id', 'p.code as position_code', 'p.name as position_name', 'u.code as unit_code', 'u.name as unit_name', 'a.decree_ref'])
            ->map(fn (object $row): array => [
                'user_id' => (int) $row->user_id,
                'position_code' => (string) $row->position_code,
                'position_name' => (string) $row->position_name,
                'unit_code' => (string) $row->unit_code,
                'unit_name' => (string) $row->unit_name,
                'decree_ref' => $row->decree_ref === null ? null : (string) $row->decree_ref,
            ])
            ->all());
    }
}
