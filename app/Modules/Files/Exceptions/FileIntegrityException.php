<?php

namespace App\Modules\Files\Exceptions;

use App\Modules\Files\Models\FileObject;
use RuntimeException;

/**
 * The stored bytes of a file are missing or differ from the identity of its file object (SEC-22, DI-33). Nothing of the file
 * is sent or read when this is raised; the caller records the integrity incident.
 */
final class FileIntegrityException extends RuntimeException
{
    public const MISSING = 'MISSING';

    public const MISMATCH = 'MISMATCH';

    /**
     * @param  array{size_bytes: int|null, sha256: string|null}  $found
     */
    private function __construct(
        // Exception::$file is the source file of the throw, so the file object has its own name.
        public readonly FileObject $fileObject,
        public readonly string $reason,
        public readonly array $found,
        string $message,
    ) {
        parent::__construct($message);
    }

    public static function missing(FileObject $file): self
    {
        return new self($file, self::MISSING, ['size_bytes' => null, 'sha256' => null],
            "File {$file->public_id} is missing from disk [{$file->disk}] at its stored path.");
    }

    public static function mismatch(FileObject $file, int $sizeBytes, string $sha256): self
    {
        return new self($file, self::MISMATCH, ['size_bytes' => $sizeBytes, 'sha256' => $sha256],
            "File {$file->public_id} on disk differs from its recorded identity (size {$sizeBytes} vs {$file->size_bytes}).");
    }
}
