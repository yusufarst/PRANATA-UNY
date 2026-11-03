<?php

namespace App\Providers;

use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureDefaults();
    }

    /**
     * Configure default behaviors for production-ready applications.
     */
    protected function configureDefaults(): void
    {
        Date::use(CarbonImmutable::class);

        DB::prohibitDestructiveCommands(
            app()->isProduction(),
        );

        // AICWDF-COMPAT-8 (SECURITY_MODEL section 3) everywhere; the leaked-password check calls an outside service and stays off.
        Password::defaults(fn (): Password => Password::min((int) config('auth.password_min_length'))
            ->mixedCase()
            ->letters()
            ->numbers()
            ->symbols(),
        );
    }
}
