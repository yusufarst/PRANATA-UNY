<?php

namespace App\Modules\Identity\Providers;

use App\Modules\Identity\Support\CredentialAuthenticator;
use App\Modules\Identity\Support\LoginLockout;
use App\Modules\ModuleServiceProvider;
use Laravel\Fortify\Fortify;

class IdentityServiceProvider extends ModuleServiceProvider
{
    /**
     * Register the module services.
     */
    public function register(): void
    {
        $this->app->singleton(LoginLockout::class);
    }

    /**
     * Bootstrap the module.
     */
    public function boot(): void
    {
        parent::boot();

        // Sign-in by username or email (field "login") with the status gate and the account lock.
        Fortify::authenticateUsing(fn ($request) => $this->app->make(CredentialAuthenticator::class)($request));
    }
}
