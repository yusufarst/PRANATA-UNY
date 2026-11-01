<?php

namespace Tests\Fixtures\Modules\Sample\Providers;

use App\Modules\ModuleServiceProvider;
use Tests\Fixtures\Modules\Sample\SampleRecord;
use Tests\Fixtures\Modules\Sample\SampleRecordPolicy;

class SampleServiceProvider extends ModuleServiceProvider
{
    protected array $policies = [
        SampleRecord::class => SampleRecordPolicy::class,
    ];
}
