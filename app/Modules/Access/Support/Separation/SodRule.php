<?php

namespace App\Modules\Access\Support\Separation;

use InvalidArgumentException;

/**
 * One separation-of-duties rule as data (ACCESS_CONTROL section 6), so a policy version can bring its own without code for each
 * rule (TAC-05). Three kinds cover the rules of the catalog:
 *
 * - exclusive_roles: nobody holds one of `roles` and one of `other_roles` on the same object (SOD-01 to SOD-03);
 * - distinct_participants: the actor of one of `actions`, or the holder of one of `roles`, is none of the `participants` of
 *   the object, such as its requester (SOD-04 to SOD-07, SOD-09);
 * - excluded_role_groups: no account of one of `role_groups` holds one of `roles` (SOD-08).
 *
 * The source is SYSTEM for a control of the system, or the policy family and version a rule came from. A rule of a policy that
 * is not in force officially is a simulation (INV-022): it is still enforced, since a separation can never be waived, and its
 * result says so.
 */
final readonly class SodRule
{
    public const EXCLUSIVE_ROLES = 'exclusive_roles';

    public const DISTINCT_PARTICIPANTS = 'distinct_participants';

    public const EXCLUDED_ROLE_GROUPS = 'excluded_role_groups';

    public const SYSTEM = 'SYSTEM';

    /**
     * @param  list<string>  $roles
     * @param  list<string>  $otherRoles
     * @param  list<string>  $actions
     * @param  list<string>  $participants
     * @param  list<string>  $roleGroups
     * @param  array<string, string>  $description  by locale, for a rule a policy brings; a system rule is translated
     */
    public function __construct(
        public string $id,
        public string $kind,
        public array $roles = [],
        public array $otherRoles = [],
        public array $actions = [],
        public array $participants = [],
        public array $roleGroups = [],
        public string $source = self::SYSTEM,
        public bool $simulation = false,
        public array $description = [],
    ) {
        if (! in_array($kind, [self::EXCLUSIVE_ROLES, self::DISTINCT_PARTICIPANTS, self::EXCLUDED_ROLE_GROUPS], true)) {
            throw new InvalidArgumentException("Unknown kind of separation rule: {$kind}.");
        }

        if (preg_match('/^[A-Z][A-Z0-9_-]*\z/', $id) !== 1) {
            throw new InvalidArgumentException("A separation rule needs an id such as SOD-01, got {$id}.");
        }

        $complete = match ($kind) {
            self::EXCLUSIVE_ROLES => $roles !== [] && $otherRoles !== [],
            self::DISTINCT_PARTICIPANTS => $participants !== [] && ($actions !== [] || $roles !== []),
            self::EXCLUDED_ROLE_GROUPS => $roleGroups !== [] && $roles !== [],
        };

        if (! $complete) {
            throw new InvalidArgumentException("The separation rule {$id} misses what its kind compares.");
        }
    }

    /**
     * A rule as a policy version states it.
     *
     * @param  array<string, mixed>  $rule
     */
    public static function fromArray(array $rule, string $source, bool $simulation): self
    {
        $list = static fn (string $key): array => array_values(array_map('strval', (array) ($rule[$key] ?? [])));
        $description = $rule['description'] ?? [];

        return new self(
            id: (string) ($rule['id'] ?? ''),
            kind: (string) ($rule['kind'] ?? ''),
            roles: $list('roles'),
            otherRoles: $list('other_roles'),
            actions: $list('actions'),
            participants: $list('participants'),
            roleGroups: $list('role_groups'),
            source: $source,
            simulation: $simulation,
            description: is_array($description) ? array_map('strval', $description) : ['id' => (string) $description],
        );
    }

    public function explanation(): string
    {
        if ($this->description !== []) {
            return $this->description[app()->getLocale()] ?? $this->description['id'] ?? reset($this->description);
        }

        $text = __("access.sod_rules.{$this->id}");

        return is_string($text) ? $text : $this->id;
    }
}
