<?php

namespace App\Modules\Access\Providers;

use App\Models\User;
use App\Modules\Access\Support\AccessEvaluator;
use App\Modules\Access\Support\AccessSubject;
use App\Modules\Access\Support\CapabilityCatalog;
use App\Modules\Identity\Support\PrivilegedAccounts;
use App\Modules\ModuleServiceProvider;
use App\Modules\Organization\Support\BusinessDate;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class AccessServiceProvider extends ModuleServiceProvider
{
    public function boot(): void
    {
        parent::boot();

        // Every capability of the catalog is a Gate ability decided by the evaluator, so can:…, Gate::authorize() and
        // $user->can() all ask the same layers (ACCESS_CONTROL section 1). An object passes as an AccessSubject or a
        // ScopedSubject; without one the question is whether the user holds the capability anywhere.
        foreach (CapabilityCatalog::all() as $capability) {
            Gate::define($capability, fn (User $user, mixed $subject = null): bool => $this->app->make(AccessEvaluator::class)
                ->allows($user, $capability, AccessSubject::of($subject)));
        }

        // The holder of a privileged grant in force is a privileged account: TOTP and a second administrator for an assisted
        // reset (ACCESS_CONTROL section 9, SECURITY_MODEL section 3.1).
        $this->app->make(PrivilegedAccounts::class)->extend(fn (User $user): bool => DB::table('access_grants')
            ->where('user_id', $user->id)
            ->where('status', 'ACTIVE')
            ->where('requires_approval', true)
            ->where('effective_from', '<=', BusinessDate::today()->toDateString())
            ->where(fn ($query) => $query->whereNull('effective_to')->orWhere('effective_to', '>', BusinessDate::today()->toDateString()))
            ->exists());
    }
}
