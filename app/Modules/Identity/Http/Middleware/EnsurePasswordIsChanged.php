<?php

namespace App\Modules\Identity\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Holds an account that must change its password to the change page until it has (SECURITY_MODEL section 3, TAC-04).
 *
 * Only the change itself, signing out and the language choice stay open; every other request is sent to the page.
 */
class EnsurePasswordIsChanged
{
    private const OPEN_ROUTES = ['identity.password.edit', 'identity.password.update', 'logout', 'locale.update'];

    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user instanceof User || ! $user->must_change_password || $request->routeIs(...self::OPEN_ROUTES)) {
            return $next($request);
        }

        if ($request->expectsJson() && ! $request->hasHeader('X-Inertia')) {
            abort(403);
        }

        // 303, so the browser follows a PUT, PATCH or DELETE with a GET instead of sending it again.
        return redirect()->route('identity.password.edit', status: 303);
    }
}
