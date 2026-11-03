<?php

namespace App\Modules\Organization\Support;

use Carbon\CarbonImmutable;

/**
 * Business dates are Asia/Jakarta dates (DATA_MODEL section 1).
 */
final class BusinessDate
{
    public const TIMEZONE = 'Asia/Jakarta';

    public static function today(): CarbonImmutable
    {
        return CarbonImmutable::now(self::TIMEZONE)->startOfDay();
    }
}
