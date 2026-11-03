<?php

namespace App\Modules\Identity\Http\Middleware;

use App\Models\User;
use App\Modules\Identity\Events\SessionEnded;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Ends a signed-in session that has run out (SECURITY_MODEL section 4, SEC-05).
 *
 * A session ends after config('session.idle_minutes') without a request, or the SUPER_ADMIN idle minutes, and
 * after config('session.absolute_minutes') since sign-in in any case. An account that may no longer hold a
 * session loses it on its next request, whatever cookie it brings. Ending it also drops the remember cookie.
 */
class EnsureSessionIsCurrent
{
    public const SIGNED_IN_AT = 'identity.signed_in_at';

    public const LAST_ACTIVITY_AT = 'identity.last_activity_at';

    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user instanceof User) {
            return $next($request);
        }

        $session = $request->session();
        $now = now()->getTimestamp();
        $signedInAt = (int) $session->get(self::SIGNED_IN_AT, $now);
        $lastActivityAt = (int) $session->get(self::LAST_ACTIVITY_AT, $now);

        $reason = match (true) {
            ! $user->mayHoldSession() => SessionEnded::ACCOUNT_STATUS,
            $now - $signedInAt > (int) config('session.absolute_minutes') * 60 => SessionEnded::ABSOLUTE,
            $now - $lastActivityAt > $this->idleMinutes($user) * 60 => SessionEnded::IDLE,
            default => null,
        };

        if ($reason !== null) {
            return $this->end($request, $user, $reason);
        }

        $session->put(self::SIGNED_IN_AT, $signedInAt);
        $session->put(self::LAST_ACTIVITY_AT, $now);

        return $next($request);
    }

    /**
     * Start the clock of a new signed-in session.
     */
    public static function start(Request $request): void
    {
        $now = now()->getTimestamp();

        $request->session()->put(self::SIGNED_IN_AT, $now);
        $request->session()->put(self::LAST_ACTIVITY_AT, $now);
    }

    private function idleMinutes(User $user): int
    {
        return (int) config($user->role_group === User::SUPER_ADMIN ? 'session.super_admin_idle_minutes' : 'session.idle_minutes');
    }

    private function end(Request $request, User $user, string $reason): Response
    {
        SessionEnded::dispatch($user, $reason);

        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        // The sign-in page keeps the language the session had.
        $request->session()->put('locale', App::getLocale());

        if ($request->expectsJson() && ! $request->hasHeader('X-Inertia')) {
            abort(401);
        }

        // 303, so the browser follows a PUT, PATCH or DELETE with a GET instead of sending it again.
        return redirect()->guest(route('login'), 303)->with('status', __('auth.session_ended'));
    }
}
