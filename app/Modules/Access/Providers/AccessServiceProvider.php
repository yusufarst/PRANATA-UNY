<?php

namespace App\Modules\Access\Providers;

use App\Models\User;
use App\Modules\Access\Models\ProcessAssignment;
use App\Modules\Access\Support\AccessEvaluator;
use App\Modules\Access\Support\AccessSubject;
use App\Modules\Access\Support\AssignmentRoles;
use App\Modules\Access\Support\AssignmentSubjects;
use App\Modules\Access\Support\CapabilityCatalog;
use App\Modules\Access\Support\Separation\SeparationOfDuties;
use App\Modules\Identity\Support\PrivilegedAccounts;
use App\Modules\ModuleServiceProvider;
use App\Modules\Organization\Support\BusinessDate;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class AccessServiceProvider extends ModuleServiceProvider
{
    /**
     * Opens PG-503: a user who may give grants or approve them. Not a capability of the catalog, only the union of two.
     */
    public const ADMINISTER_ACCESS = 'access.administer';

    /**
     * Opens PG-504: a user who may assign people on every kind of object. Assigning on one object is checked there.
     */
    public const ADMINISTER_ASSIGNMENTS = 'access.administer_assignments';

    /**
     * Assigning people on one object, or ending their assignment there: Gate::authorize(ASSIGN, [$type, $id]).
     */
    public const ASSIGN = 'access.assign';

    public function register(): void
    {
        // A module registers its kinds of object and its separation rules once when it boots.
        $this->app->singleton(AssignmentSubjects::class);
        $this->app->singleton(SeparationOfDuties::class);
    }

    public function boot(): void
    {
        parent::boot();

        Gate::define(self::ADMINISTER_ASSIGNMENTS, fn (User $user): bool => $user->can('admin.access.grant'));

        Gate::define(self::ASSIGN, fn (User $user, string $type, int $id): bool => $this->app->make(AssignmentSubjects::class)
            ->mayAssign($user, $type, $id));

        Gate::define(self::ADMINISTER_ACCESS, fn (User $user): bool => $user->can('admin.access.grant') || $user->can('admin.access.approve'));

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

        // So is the holder of a decision role that has not ended (SEC-21, AUTHZ-24): their assisted reset needs a second
        // administrator, also while an assignment waits for its first day.
        $this->app->make(PrivilegedAccounts::class)->extend(fn (User $user): bool => ProcessAssignment::query()
            ->where('user_id', $user->id)
            ->where('status', ProcessAssignment::ACTIVE)
            ->whereIn('assignment_role', AssignmentRoles::decisionRoles())
            ->where(fn ($query) => $query->whereNull('effective_to')->orWhere('effective_to', '>', now()))
            ->exists());
    }
}
