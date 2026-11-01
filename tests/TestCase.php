<?php

namespace Tests;

use App\Modules\Platform\Support\DatabaseResetGuard;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Laravel\Fortify\Features;
use Tests\Support\DatabaseGuard;

abstract class TestCase extends BaseTestCase
{
    /**
     * Creates the application, refusing to go on outside a PRANATA test database.
     */
    public function createApplication()
    {
        $app = parent::createApplication();

        $connection = $app['config']->get('database.default');

        DatabaseGuard::assertSafe(
            $app->environment(),
            DatabaseResetGuard::databaseName($app['config']->get("database.connections.{$connection}")),
        );

        return $app;
    }

    protected function skipUnlessFortifyHas(string $feature, ?string $message = null): void
    {
        if (! Features::enabled($feature)) {
            $this->markTestSkipped($message ?? "Fortify feature [{$feature}] is not enabled.");
        }
    }
}
