<?php

namespace App\Modules;

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use ReflectionClass;

/**
 * Loads the routes, migrations and policies of one module.
 *
 * A module lives in app/Modules/<Module>, with its provider in Providers/.
 */
abstract class ModuleServiceProvider extends ServiceProvider
{
    /**
     * The object policies of this module.
     *
     * @var array<class-string, class-string>
     */
    protected array $policies = [];

    /**
     * Bootstrap the module.
     */
    public function boot(): void
    {
        $root = $this->moduleRoot();

        if (! $this->app->routesAreCached() && is_file($root.'/routes.php')) {
            Route::middleware('web')->group($root.'/routes.php');
        }

        $this->loadMigrationsFrom($root.'/database/migrations');

        foreach ($this->policies as $model => $policy) {
            Gate::policy($model, $policy);
        }
    }

    /**
     * Get the folder of this module.
     */
    protected function moduleRoot(): string
    {
        return dirname((string) (new ReflectionClass($this))->getFileName(), 2);
    }
}
