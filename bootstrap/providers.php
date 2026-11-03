<?php

use App\Modules\Audit\Providers\AuditServiceProvider;
use App\Modules\Identity\Providers\IdentityServiceProvider;
use App\Modules\Organization\Providers\OrganizationServiceProvider;
use App\Modules\Platform\Providers\PlatformServiceProvider;
use App\Providers\AppServiceProvider;
use App\Providers\FortifyServiceProvider;

return [
    AppServiceProvider::class,
    FortifyServiceProvider::class,
    PlatformServiceProvider::class,
    AuditServiceProvider::class,
    IdentityServiceProvider::class,
    OrganizationServiceProvider::class,
];
