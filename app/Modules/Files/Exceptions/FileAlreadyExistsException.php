<?php

namespace App\Modules\Files\Exceptions;

use RuntimeException;

/**
 * The private storage was asked to write to a path that already holds bytes. Stored bytes are never overwritten
 * (DATA_MODEL section 12): different content is always a new file at a new path.
 */
final class FileAlreadyExistsException extends RuntimeException
{
    public static function at(string $disk, string $path): self
    {
        return new self("Path [{$path}] on disk [{$disk}] already holds a file; stored bytes are never overwritten.");
    }
}
