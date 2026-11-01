<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    /** @var list<string> */
    public const SUPPORTED = ['id', 'en'];

    public function handle(Request $request, Closure $next): Response
    {
        App::setLocale(self::resolve($request));

        return $next($request);
    }

    /**
     * The session holds the choice until TASK-010 adds users.locale.
     */
    public static function resolve(Request $request): string
    {
        $locale = $request->hasSession() ? $request->session()->get('locale') : null;

        return is_string($locale) && in_array($locale, self::SUPPORTED, true) ? $locale : App::getLocale();
    }
}
