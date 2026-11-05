<?php

namespace App\Modules\Access\Support;

use App\Modules\Access\Contracts\ScopedSubject;
use InvalidArgumentException;

/**
 * Where a subject lies, as far as the scopes of a grant can see: its owning unit, its warehouse or its policy family. A
 * module's model gives one through {@see ScopedSubject}, so the evaluator never reads another module's tables.
 *
 * An object that carries process assignments also names itself by its assignment type and id, so a capability marked
 * "Penugasan" can find the assignments on it (TASK-015). Its participants are the people who already took part in it, by
 * name, such as the requester of a request: the separation-of-duties rules compare the actor with them (SOD-04 to SOD-09).
 */
final readonly class AccessSubject
{
    /**
     * @param  array<string, int|list<int>>  $participants
     */
    public function __construct(
        public ?int $unitId = null,
        public ?int $warehouseId = null,
        public ?string $policyFamily = null,
        public ?string $objectType = null,
        public ?int $objectId = null,
        public array $participants = [],
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

    public function hasObject(): bool
    {
        return $this->objectType !== null && $this->objectId !== null;
    }

    /**
     * The same subject with the people who took part in it, as the action about to happen knows them.
     *
     * @param  array<string, int|list<int>>  $participants
     */
    public function withParticipants(array $participants): self
    {
        return new self($this->unitId, $this->warehouseId, $this->policyFamily, $this->objectType, $this->objectId, [...$this->participants, ...$participants]);
    }

    /**
     * The ids of the people taking part under one of the names.
     *
     * @param  list<string>  $names
     * @return list<int>
     */
    public function participantIds(array $names): array
    {
        $ids = [];

        foreach ($names as $name) {
            foreach ((array) ($this->participants[$name] ?? []) as $id) {
                $ids[] = (int) $id;
            }
        }

        return $ids;
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
