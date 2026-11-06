<?php

namespace App\Modules\Files\Support;

use App\Modules\Files\Contracts\FileOwnerType;
use InvalidArgumentException;

/**
 * The kinds of row that carry files, one per owning table. A module registers its type when it boots; a link to an unknown
 * type can neither be shown nor downloaded, so the database alone never grants a read (fail closed).
 */
final class FileOwnerTypes
{
    /**
     * @var array<string, FileOwnerType>
     */
    private array $types = [];

    public function register(FileOwnerType $type): void
    {
        if (preg_match('/^[a-z][a-z0-9_]*\z/', $type->ownerType()) !== 1) {
            throw new InvalidArgumentException("[{$type->ownerType()}] is not a table name.");
        }

        $this->types[$type->ownerType()] = $type;
    }

    public function find(string $ownerType): ?FileOwnerType
    {
        return $this->types[$ownerType] ?? null;
    }

    /**
     * @return list<FileOwnerType>
     */
    public function all(): array
    {
        return array_values($this->types);
    }
}
