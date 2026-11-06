<?php

namespace App\Modules\Files\Data;

/**
 * A temporary copy of stored bytes whose size and SHA-256 matched the file object while it was made. Only such a copy is
 * ever sent or read by the application (COMMAND_AND_API_CONTRACTS section 7). The caller deletes it once it is sent.
 */
final readonly class VerifiedCopy
{
    public function __construct(public string $path, public int $sizeBytes, public string $sha256) {}
}
