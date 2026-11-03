<?php

namespace App\Modules\Identity\Providers;

use App\Models\User;
use App\Modules\Identity\Http\Middleware\EnsureSessionIsCurrent;
use App\Modules\Identity\Support\CredentialAuthenticator;
use App\Modules\Identity\Support\LoginLockout;
use App\Modules\ModuleServiceProvider;
use Illuminate\Auth\Events\Login;
use Illuminate\Support\Facades\Event;
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

        // A sign-in, also through the remember cookie, starts the session clock and is noted on the account.
        Event::listen(Login::class, function (Login $event): void {
            $request = $this->app->make('request');

            if ($request->hasSession()) {
                EnsureSessionIsCurrent::start($request);
            }

            if ($event->user instanceof User) {
                $event->user->forceFill(['last_login_at' => now()])->saveQuietly();
            }
        });
    }
}
