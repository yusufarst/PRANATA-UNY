<?php

namespace App\Modules\Organization\Support;

use App\Modules\Organization\Models\OrgUnit;

/**
 * The ltree path of a unit: the public ids from the root down, which are lower-case ULIDs and so valid ltree labels.
 */
final class UnitPath
{
    public static function below(?OrgUnit $parent, string $publicId): string
    {
        return $parent === null ? $publicId : $parent->path.'.'.$publicId;
    }

    public static function depth(string $path): int
    {
        return substr_count($path, '.');
    }
}
