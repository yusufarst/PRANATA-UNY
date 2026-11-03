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

    public static function idOf(string $publicId): ?int
    {
        $id = DB::table('org_units')->where('public_id', $publicId)->value('id');

        return $id === null ? null : (int) $id;
    }

    /**
     * The active units in tree order, to choose the unit of a scope.
     *
     * @return list<array{public_id: string, code: string, name: string, depth: int}>
     */
    public static function choices(): array
    {
        return DB::table('org_units')
            ->where('status', 'ACTIVE')
            ->orderBy('path')
            ->get(['public_id', 'code', 'name', 'depth'])
            ->map(fn (object $unit): array => [
                'public_id' => (string) $unit->public_id,
                'code' => (string) $unit->code,
                'name' => (string) $unit->name,
                'depth' => (int) $unit->depth,
            ])
            ->values()
            ->all();
    }

    /**
     * The code and name of each unit, by id, to show the scope of a grant.
     *
     * @param  list<int>  $unitIds
     * @return array<int, array{public_id: string, code: string, name: string}>
     */
    public static function labels(array $unitIds): array
    {
        if ($unitIds === []) {
            return [];
        }

        $labels = [];

        foreach (DB::table('org_units')->whereIn('id', $unitIds)->get(['id', 'public_id', 'code', 'name']) as $unit) {
            $labels[(int) $unit->id] = [
                'public_id' => (string) $unit->public_id,
                'code' => (string) $unit->code,
                'name' => (string) $unit->name,
            ];
        }

        return $labels;
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
