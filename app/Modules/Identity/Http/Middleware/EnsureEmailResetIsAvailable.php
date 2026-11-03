<?php

namespace App\Modules\Identity\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Without mail that goes out, the self-service reset by email does not exist (SECURITY_MODEL section 3.1, TASK-011 TAC-05):
 * its pages and commands answer 404, and the sign-in page offers no link to them.
 */
class EnsureEmailResetIsAvailable
{
    public const ROUTES = ['password.request', 'password.email', 'password.reset', 'password.update'];

    public static function available(): bool
    {
        return (bool) config('auth.email_reset');
    }

    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! self::available() && $request->routeIs(...self::ROUTES)) {
            abort(404);
        }

        return $next($request);
    }
}
