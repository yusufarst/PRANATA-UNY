<?php

namespace App\Modules\Identity\Http\Middleware;

use App\Models\User;
use App\Modules\Identity\Support\TwoFactorPolicy;
use Closure;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\Response;

/**
 * Sends an account that must have TOTP but has none to its setup on the security page before Administration (SEC-03,
 * TASK-012 TAC-02). Route middleware "two-factor"; later modules put it on their decision actions too (SEC-21).
 */
class EnsureTwoFactorIsEnabled
{
    public function __construct(private readonly TwoFactorPolicy $policy) {}

    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user instanceof User || ! $this->policy->isMissingFor($user)) {
            return $next($request);
        }

        if ($request->expectsJson() && ! $request->hasHeader('X-Inertia')) {
            abort(403);
        }

        Inertia::flash('toast', ['type' => 'warning', 'message' => __('identity.two_factor_required')]);

        // 303, so the browser follows a POST, PUT or DELETE with a GET instead of sending it again.
        return redirect()->route('security.edit', status: 303);
    }
}
