<?php

namespace App\Modules\Access\Support;

/**
 * The scope of a grant (ACCESS_CONTROL section 4): G, U, S, W, K and P in the catalog.
 */
enum ScopeType: string
{
    case Global = 'GLOBAL';
    case OrgUnit = 'ORG_UNIT';
    case OrgUnitSubtree = 'ORG_UNIT_SUBTREE';
    case Warehouse = 'WAREHOUSE';
    case Workspace = 'WORKSPACE';
    case PolicyFamily = 'POLICY_FAMILY';

    public static function fromLetter(string $letter): self
    {
        return match ($letter) {
            'G' => self::Global,
            'U' => self::OrgUnit,
            'S' => self::OrgUnitSubtree,
            'W' => self::Warehouse,
            'K' => self::Workspace,
            'P' => self::PolicyFamily,
        };
    }

    /**
     * Whether a grant of this scope names a unit or a warehouse by scope_id.
     */
    public function needsId(): bool
    {
        return in_array($this, [self::OrgUnit, self::OrgUnitSubtree, self::Warehouse], true);
    }

    /**
     * Whether a grant of this scope covers every subject of its capability.
     */
    public function coversEverything(): bool
    {
        return $this === self::Global || $this === self::Workspace;
    }
}
