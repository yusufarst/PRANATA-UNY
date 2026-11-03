<?php

namespace App\Modules\Identity\Providers;

use App\Http\Middleware\SetLocale;
use App\Models\User;
use App\Modules\Identity\Http\Middleware\EnsureSessionIsCurrent;
use App\Modules\Identity\Support\CredentialAuthenticator;
use App\Modules\Identity\Support\LoginLockout;
use App\Modules\Identity\Support\PrivilegedAccounts;
use App\Modules\ModuleServiceProvider;
use Illuminate\Auth\Events\Login;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Laravel\Fortify\Fortify;

class IdentityServiceProvider extends ModuleServiceProvider
{
    /**
     * The capability to create, change and deactivate accounts and to reset them with an administrator's help.
     */
    public const MANAGE_USERS = 'admin.users.manage';

    /**
     * Register the module services.
     */
    public function register(): void
    {
        $this->app->singleton(LoginLockout::class);
        $this->app->singleton(PrivilegedAccounts::class);
    }

    /**
     * Bootstrap the module.
     */
    public function boot(): void
    {
        parent::boot();

        // ACCESS_CONTROL section 4.1. Until the scoped grants of TASK-014 exist, only an active SUPER_ADMIN holds it (OD-10).
        Gate::define(self::MANAGE_USERS, fn (User $user): bool => $user->mayHoldSession() && $user->role_group === User::SUPER_ADMIN);

        // Sign-in by username or email (field "login") with the status gate and the account lock.
        Fortify::authenticateUsing(fn ($request) => $this->app->make(CredentialAuthenticator::class)($request));

        // Fortify would otherwise look the account up by its username field, "login", which is no column.
        Fortify::confirmPasswordsUsing(fn (User $user, ?string $password): bool => Hash::check((string) $password, $user->password));

        // A sign-in, also through the remember cookie, starts the session clock in the saved language and is noted on
        // the account.
        Event::listen(Login::class, function (Login $event): void {
            $request = $this->app->make('request');
            $user = $event->user instanceof User ? $event->user : null;

            if ($request->hasSession()) {
                EnsureSessionIsCurrent::start($request);

                if ($user !== null && in_array($user->locale, SetLocale::SUPPORTED, true)) {
                    $request->session()->put('locale', $user->locale);
                }
            }

            $user?->forceFill(['last_login_at' => now()])->saveQuietly();
        });
    }
}
