<?php

namespace App\Modules\Access\Support;

use App\Modules\Access\Contracts\ScopedSubject;
use InvalidArgumentException;

/**
 * Where a subject lies, as far as the scopes of a grant can see: its owning unit, its warehouse or its policy family. A
 * module's model gives one through {@see ScopedSubject}, so the evaluator never reads another module's tables.
 */
final readonly class AccessSubject
{
    public function __construct(
        public ?int $unitId = null,
        public ?int $warehouseId = null,
        public ?string $policyFamily = null,
    ) {}

    public static function forUnit(int $unitId): self
    {
        return new self(unitId: $unitId);
    }

    public static function forWarehouse(int $warehouseId): self
    {
        return new self(warehouseId: $warehouseId);
    }

    public static function forPolicyFamily(string $family): self
    {
        return new self(policyFamily: $family);
    }

    /**
     * The subject of a Gate check: none, an AccessSubject, or a model that knows its own scope. Anything else is a mistake
     * of the caller and fails loudly rather than allowing.
     */
    public static function of(mixed $subject): ?self
    {
        return match (true) {
            $subject === null => null,
            $subject instanceof self => $subject,
            $subject instanceof ScopedSubject => $subject->accessSubject(),
            default => throw new InvalidArgumentException('An access check needs an AccessSubject or a ScopedSubject, got '.get_debug_type($subject).'.'),
        };
    }
}
