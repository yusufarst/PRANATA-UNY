<?php

namespace App\Modules\Access\Contracts;

use App\Modules\Access\Support\AccessSubject;
use App\Modules\Access\Support\AssignmentRoles;

/**
 * One kind of object that carries process assignments, as its owning module registers it (ACCESS_CONTROL section 5), such as
 * the packages of Procurement. Access never reads the module's tables: it asks this contract for the object, its scope and
 * its label.
 */
interface AssignmentSubjectType
{
    /**
     * The subject_type of process_assignments, such as procurement.package.
     */
    public function key(): string;

    /**
     * The kind of object of the role catalog ({@see AssignmentRoles}), such as package.
     */
    public function kind(): string;

    /**
     * The capability that lets a user assign people on an object of this type, checked on the object itself, such as
     * procurement.package.manage. An access administrator (admin.access.grant) may assign on every type.
     */
    public function assignCapability(): string;

    /**
     * The object by its public reference, or null when there is none.
     */
    public function idOf(string $reference): ?int;

    /**
     * The scope and participants of the object, for the evaluator; null when it does not exist.
     */
    public function accessSubject(int $id): ?AccessSubject;

    /**
     * What the pages show for the object: its public reference and a readable label.
     *
     * @return array{reference: string, label: string}|null
     */
    public function describe(int $id): ?array;
}
