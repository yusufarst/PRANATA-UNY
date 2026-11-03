<?php

namespace App\Modules\Organization\Queries;

use Illuminate\Contracts\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

/**
 * The public reads of the unit tree for the access scopes of other modules (ARCHITECTURE section 5 rule 2): whether a unit
 * exists, whether it lies in the subtree of another, and a query condition for "in the subtree of one of these units". The
 * subtree is the ltree path, GiST-indexed (DATA_MODEL section 6).
 */
final class UnitScope
{
    public static function exists(int $unitId): bool
    {
        return DB::table('org_units')->where('id', $unitId)->exists();
    }

    /**
     * Whether the unit is the root itself or lies anywhere below it.
     */
    public static function isWithin(int $unitId, int $rootId): bool
    {
        return (bool) DB::scalar(
            'select exists (select from org_units u join org_units r on u.path <@ r.path where u.id = ? and r.id = ?)',
            [$unitId, $rootId],
        );
    }

    /**
     * Keep the rows whose unit column holds one of the roots or a unit below one of them.
     *
     * @template TBuilder of Builder
     *
     * @param  TBuilder  $query
     * @param  list<int>  $rootIds
     * @return TBuilder
     */
    public static function whereInSubtree(Builder $query, string $column, array $rootIds): Builder
    {
        if ($rootIds === []) {
            $query->whereRaw('false');

            return $query;
        }

        $query->whereIn($column, fn ($units) => $units
            ->from('org_units', 'scope_unit')
            ->select('scope_unit.id')
            ->whereExists(fn ($roots) => $roots
                ->from('org_units', 'scope_root')
                ->whereRaw('scope_unit.path <@ scope_root.path')
                ->whereIn('scope_root.id', $rootIds)));

        return $query;
    }
}
