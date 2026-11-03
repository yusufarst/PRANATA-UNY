<?php

namespace App\Modules\Access\Queries;

use App\Models\User;
use App\Modules\Access\Support\AccessEvaluator;
use App\Modules\Access\Support\ScopeType;
use App\Modules\Organization\Queries\UnitScope;
use Illuminate\Contracts\Database\Query\Builder;

/**
 * visibleTo($actor) of ACCESS_CONTROL section 1: the same decision as the evaluator, as a SQL condition, so a list, a search,
 * an export and a download see the same rows as the detail page (BR-034, AUTHZ-45).
 *
 * The module names the columns that place a row: its owning unit, its warehouse, its policy family. A grant whose scope has no
 * column here shows nothing (fail closed), and a user without the capability sees no row at all.
 */
final class VisibleTo
{
    /**
     * @template TBuilder of Builder
     *
     * @param  TBuilder  $query
     * @param  array{unit?: string, warehouse?: string, policy_family?: string}  $columns
     * @return TBuilder
     */
    public static function apply(Builder $query, User $user, string $capability, array $columns): Builder
    {
        $evaluator = app(AccessEvaluator::class);

        if ($evaluator->holdsEverywhere($user, $capability)) {
            return $query;
        }

        $units = [];
        $roots = [];
        $warehouses = [];
        $families = [];

        foreach ($evaluator->scopes($user, $capability) as $scope) {
            if ($scope->type === ScopeType::OrgUnit) {
                $units[] = (int) $scope->id;
            } elseif ($scope->type === ScopeType::OrgUnitSubtree) {
                $roots[] = (int) $scope->id;
            } elseif ($scope->type === ScopeType::Warehouse) {
                $warehouses[] = (int) $scope->id;
            } elseif ($scope->type === ScopeType::PolicyFamily) {
                $families[] = (string) $scope->key;
            }
        }

        $unitColumn = $columns['unit'] ?? null;
        $warehouseColumn = $columns['warehouse'] ?? null;
        $familyColumn = $columns['policy_family'] ?? null;

        $clauses = array_filter([
            $unitColumn !== null && $units !== [] ? fn (Builder $where) => $where->orWhereIn($unitColumn, $units) : null,
            $unitColumn !== null && $roots !== []
                ? fn (Builder $where) => $where->orWhere(fn (Builder $subtree) => UnitScope::whereInSubtree($subtree, $unitColumn, $roots))
                : null,
            $warehouseColumn !== null && $warehouses !== [] ? fn (Builder $where) => $where->orWhereIn($warehouseColumn, $warehouses) : null,
            $familyColumn !== null && $families !== [] ? fn (Builder $where) => $where->orWhereIn($familyColumn, $families) : null,
        ]);

        if ($clauses === []) {
            $query->whereRaw('false');

            return $query;
        }

        $query->where(function (Builder $where) use ($clauses): void {
            foreach ($clauses as $clause) {
                $clause($where);
            }
        });

        return $query;
    }
}
