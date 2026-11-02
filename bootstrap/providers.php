<?php

use App\Modules\Audit\Providers\AuditServiceProvider;
use App\Modules\Platform\Providers\PlatformServiceProvider;
use App\Providers\AppServiceProvider;
use App\Providers\FortifyServiceProvider;

return [
    AppServiceProvider::class,
    FortifyServiceProvider::class,
    PlatformServiceProvider::class,
    AuditServiceProvider::class,
];
