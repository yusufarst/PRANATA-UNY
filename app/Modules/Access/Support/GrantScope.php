<?php

namespace App\Modules\Access\Support;

/**
 * The scope of one grant in force: a unit or warehouse id, a policy family key, or neither.
 */
final readonly class GrantScope
{
    public function __construct(
        public ScopeType $type,
        public ?int $id = null,
        public ?string $key = null,
    ) {}
}
