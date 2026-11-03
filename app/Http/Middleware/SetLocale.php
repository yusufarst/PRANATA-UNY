<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    /** @var list<string> */
    public const SUPPORTED = ['id', 'en'];

    public function handle(Request $request, Closure $next): Response
    {
        // Resolve the signed-in user here, where the session has started, so resolve() sees the saved language.
        $request->user();

        App::setLocale(self::resolve($request));

        return $next($request);
    }

    /**
     * The session's choice wins; sign-in copies the saved language (users.locale) into it, so a choice made in one
     * session does not switch another that is open. A signed-in user without a choice in the session gets the saved one.
     *
     * Only a user the guard has already resolved counts, so an error page never queries a database that may be down.
     */
    public static function resolve(Request $request): string
    {
        $locale = $request->hasSession() ? $request->session()->get('locale') : null;

        if (is_string($locale) && in_array($locale, self::SUPPORTED, true)) {
            return $locale;
        }

        $user = Auth::hasUser() ? Auth::user() : null;

        return $user instanceof User && in_array($user->locale, self::SUPPORTED, true) ? $user->locale : App::getLocale();
    }
}
