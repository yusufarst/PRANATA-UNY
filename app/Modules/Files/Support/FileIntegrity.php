<?php

namespace App\Modules\Files\Support;

use App\Modules\Files\Data\VerifiedCopy;
use App\Modules\Files\Exceptions\FileIntegrityException;
use App\Modules\Files\Models\FileObject;
use RuntimeException;
use Throwable;

/**
 * The only way the application reads stored bytes (COMMAND_AND_API_CONTRACTS section 7, SEC-22): a download, a preview, a
 * package assembly or a conversion first copies the bytes while hashing them, compares size and SHA-256 with the file object,
 * and gets the copy only when both match. Missing or changed bytes raise {@see FileIntegrityException}; nothing of the file
 * leaves, and the caller records the incident. The file is never replaced or released here.
 */
final class FileIntegrity
{
    public function __construct(private readonly PrivateFileStorage $storage) {}

    /**
     * A temporary copy of the verified bytes, for streaming; the caller deletes it after sending.
     *
     * @throws FileIntegrityException
     */
    public function verifiedCopy(FileObject $file): VerifiedCopy
    {
        $this->assertDisk($file);
        $stream = $this->storage->readStream($file->path);

        if ($stream === null) {
            throw FileIntegrityException::missing($file);
        }

        $path = tempnam(sys_get_temp_dir(), 'pranata-verified-');

        if ($path === false) {
            throw new RuntimeException('No temporary file for the verified copy.');
        }

        $copy = fopen($path, 'wb');

        if ($copy === false) {
            throw new RuntimeException('The temporary file for the verified copy could not be opened.');
        }

        // Whatever goes wrong, a copy that is not handed over is deleted.
        try {
            try {
                [$size, $sha256] = PrivateFileStorage::digest($stream, $copy);
            } finally {
                fclose($copy);
                fclose($stream);
            }

            if ($size !== $file->size_bytes || $sha256 !== $file->sha256) {
                throw FileIntegrityException::mismatch($file, $size, $sha256);
            }
        } catch (Throwable $exception) {
            @unlink($path);

            throw $exception;
        }

        return new VerifiedCopy($path, $size, $sha256);
    }

    /**
     * The verified bytes in memory, for a server-side read such as a package assembly.
     *
     * @throws FileIntegrityException
     */
    public function read(FileObject $file): string
    {
        $copy = $this->verifiedCopy($file);

        try {
            $bytes = file_get_contents($copy->path);
        } finally {
            @unlink($copy->path);
        }

        if ($bytes === false) {
            throw new RuntimeException('The verified copy could not be read.');
        }

        return $bytes;
    }

    /**
     * Recompute size and SHA-256 of the stored bytes and compare them, without keeping a copy (the daily job).
     *
     * @throws FileIntegrityException
     */
    public function check(FileObject $file): void
    {
        $this->assertDisk($file);
        $stream = $this->storage->readStream($file->path);

        if ($stream === null) {
            throw FileIntegrityException::missing($file);
        }

        try {
            [$size, $sha256] = PrivateFileStorage::digest($stream);
        } finally {
            fclose($stream);
        }

        if ($size !== $file->size_bytes || $sha256 !== $file->sha256) {
            throw FileIntegrityException::mismatch($file, $size, $sha256);
        }
    }

    private function assertDisk(FileObject $file): void
    {
        if ($file->disk !== $this->storage->disk()) {
            throw new RuntimeException("File {$file->public_id} lives on disk [{$file->disk}], not on the configured [{$this->storage->disk()}].");
        }
    }
}
