<?php

namespace App\Modules\Access\Support;

/**
 * ALLOW, or DENY with a reason key (ACCESS_CONTROL section 1). The reason names the layer that refused, never the subject.
 */
final readonly class AccessDecision
{
    private function __construct(public bool $allowed, public ?string $reasonKey) {}

    public static function allow(): self
    {
        return new self(true, null);
    }

    public static function deny(string $reasonKey): self
    {
        return new self(false, $reasonKey);
    }
}
