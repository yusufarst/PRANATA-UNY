<?php

namespace App\Modules\Files\Data;

/**
 * What the private storage wrote: the random path the bytes went to, and the size and SHA-256 the server computed from the
 * bytes it read back from that path (DATA_MODEL section 12). Values from the client never reach here.
 */
final readonly class StoredBytes
{
    public function __construct(
        public string $disk,
        public string $path,
        public int $sizeBytes,
        public string $sha256,
    ) {}
}
