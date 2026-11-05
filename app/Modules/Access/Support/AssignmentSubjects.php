<?php

namespace App\Modules\Access\Support;

use App\Models\User;
use App\Modules\Access\Contracts\AssignmentSubjectType;
use InvalidArgumentException;

/**
 * The kinds of object that carry process assignments, one per owning module (ACCESS_CONTROL section 5). A module registers its
 * type when it boots; until then no object of that kind can be assigned, and an assignment of an unknown type is shown by its
 * type and id only.
 */
final class AssignmentSubjects
{
    /**
     * @var array<string, AssignmentSubjectType>
     */
    private array $types = [];

    public function register(AssignmentSubjectType $type): void
    {
        if (! in_array($type->kind(), [
            AssignmentRoles::PACKAGE, AssignmentRoles::CONTRACT, AssignmentRoles::WAREHOUSE_REQUEST,
            AssignmentRoles::RETURN, AssignmentRoles::ASSET_CASE, AssignmentRoles::RECON_CASE,
        ], true)) {
            throw new InvalidArgumentException("Unknown kind of assignment object: {$type->kind()}.");
        }

        $this->types[$type->key()] = $type;
    }

    public function find(string $key): ?AssignmentSubjectType
    {
        return $this->types[$key] ?? null;
    }

    /**
     * @return list<AssignmentSubjectType>
     */
    public function all(): array
    {
        return array_values($this->types);
    }

    /**
     * Whether the actor may assign people on the object: an access administrator everywhere, anyone else by the capability
     * the type names, checked on the object (ACCESS_CONTROL section 5).
     */
    public function mayAssign(User $actor, string $key, int $id): bool
    {
        if ($actor->can('admin.access.grant')) {
            return true;
        }

        $type = $this->find($key);
        $subject = $this->accessSubject($key, $id);

        return $type !== null && $subject !== null && $actor->can($type->assignCapability(), $subject);
    }

    /**
     * The subject of a capability check on an object of a registered type, with its object identity.
     */
    public function accessSubject(string $key, int $id): ?AccessSubject
    {
        $subject = $this->find($key)?->accessSubject($id);

        return $subject === null ? null : new AccessSubject(
            $subject->unitId,
            $subject->warehouseId,
            $subject->policyFamily,
            $key,
            $id,
            $subject->participants,
        );
    }
}
