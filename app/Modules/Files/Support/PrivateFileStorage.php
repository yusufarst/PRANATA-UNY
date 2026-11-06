<?php

namespace App\Modules\Files\Support;

use App\Modules\Files\Data\StoredBytes;
use App\Modules\Files\Exceptions\FileAlreadyExistsException;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Filesystem\FilesystemManager;
use Illuminate\Support\Str;
use RuntimeException;

/**
 * The write-once private storage of ADR-0009 behind a Flysystem disk (local in V1, object storage later). Bytes go to a new
 * random path with exclusive creation and are never overwritten, moved or deleted by this service: it has no such
 * operation. Size and SHA-256 are computed from the bytes read back from the stored path, so what the database records is
 * what the disk holds (DATA_MODEL section 12).
 *
 * Reads for the application go through {@see FileIntegrity}, which verifies them first; this class reads for that service only.
 */
final class PrivateFileStorage
{
    private const CHUNK = 1024 * 1024;

    public function __construct(private readonly FilesystemManager $filesystems, private readonly string $disk) {}

    public function disk(): string
    {
        return $this->disk;
    }

    /**
     * Store the bytes of a local file (an upload's temporary file) at a new random path and give what was stored.
     */
    public function writeNew(string $sourcePath): StoredBytes
    {
        $source = fopen($sourcePath, 'rb');

        if ($source === false) {
            throw new RuntimeException('The uploaded bytes could not be read.');
        }

        try {
            $path = $this->newPath();
            $this->writeAt($path, $source);
        } finally {
            fclose($source);
        }

        return $this->measure($path);
    }

    /**
     * Write a stream to this path, which must not exist yet. On a local disk the file is created exclusively, so two writers of
     * the same path never both succeed; another driver refuses a path that already exists before writing.
     *
     * @param  resource  $stream
     */
    public function writeAt(string $path, $stream): void
    {
        $disk = $this->filesystem();
        $local = $this->localPath($disk, $path);

        if ($local === null) {
            if ($disk->exists($path)) {
                throw FileAlreadyExistsException::at($this->disk, $path);
            }

            if (! $disk->writeStream($path, $stream)) {
                throw new RuntimeException("The bytes could not be written to [{$path}].");
            }

            return;
        }

        $folder = dirname($local);

        if (! is_dir($folder) && ! @mkdir($folder, 0750, true) && ! is_dir($folder)) {
            throw new RuntimeException("The folder of [{$path}] could not be created.");
        }

        // 'x': create the file, and fail if it exists (the exclusive creation of DATA_MODEL section 12).
        $target = @fopen($local, 'xb');

        if ($target === false) {
            if (file_exists($local)) {
                throw FileAlreadyExistsException::at($this->disk, $path);
            }

            throw new RuntimeException("The file [{$path}] could not be created.");
        }

        try {
            if (stream_copy_to_stream($stream, $target) === false) {
                throw new RuntimeException("The bytes could not be written to [{$path}].");
            }
        } finally {
            fclose($target);
        }
    }

    /**
     * The size and SHA-256 of the bytes at the path, read from the disk.
     */
    public function measure(string $path): StoredBytes
    {
        $stream = $this->readStream($path);

        if ($stream === null) {
            throw new RuntimeException("The stored file [{$path}] could not be read back.");
        }

        try {
            [$size, $sha256] = self::digest($stream);
        } finally {
            fclose($stream);
        }

        return new StoredBytes($this->disk, $path, $size, $sha256);
    }

    /**
     * The stored bytes as a read stream, or null when the path holds no file.
     *
     * @return resource|null
     */
    public function readStream(string $path)
    {
        $disk = $this->filesystem();

        if (! $disk->exists($path)) {
            return null;
        }

        $stream = $disk->readStream($path);

        return is_resource($stream) ? $stream : null;
    }

    /**
     * The size and SHA-256 of a stream, read to its end.
     *
     * @param  resource  $stream
     * @param  resource|null  $copy  a stream the bytes are written to as they are read
     * @return array{int, string}
     */
    public static function digest($stream, $copy = null): array
    {
        $context = hash_init('sha256');
        $size = 0;

        while (! feof($stream)) {
            $chunk = fread($stream, self::CHUNK);

            if ($chunk === false) {
                throw new RuntimeException('The stored bytes could not be read.');
            }

            if ($chunk === '') {
                continue;
            }

            hash_update($context, $chunk);
            $size += strlen($chunk);

            if ($copy !== null && fwrite($copy, $chunk) !== strlen($chunk)) {
                throw new RuntimeException('The verified copy could not be written.');
            }
        }

        return [$size, hash_final($context)];
    }

    private function filesystem(): Filesystem
    {
        return $this->filesystems->disk($this->disk);
    }

    /**
     * A random path nobody can guess: the month for operators, then 128 random bits.
     */
    private function newPath(): string
    {
        return now()->format('Y/m').'/'.Str::lower(bin2hex(random_bytes(16)));
    }

    /**
     * The absolute path on a local disk, or null for another driver.
     */
    private function localPath(Filesystem $disk, string $path): ?string
    {
        if (config("filesystems.disks.{$this->disk}.driver") !== 'local' || ! $disk instanceof FilesystemAdapter) {
            return null;
        }

        return $disk->path($path);
    }
}
