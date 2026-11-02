<?php

use App\Http\Middleware\HandleInertiaRequests;
use App\Http\Middleware\SetLocale;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\Response;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->encryptCookies(except: ['sidebar_state']);

        $middleware->web(append: [
            SetLocale::class,
            HandleInertiaRequests::class,
            AddLinkHeadersForPreloadedAssets::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );

        // PG-013. A failure before SetLocale (419, 503) still needs the language of the session, so it is resolved here.
        $exceptions->respond(function (Response $response, Throwable $exception, Request $request): Response {
            $status = $response->getStatusCode();
            $wantsJson = $request->is('api/*') || ($request->expectsJson() && ! $request->hasHeader('X-Inertia'));

            if (! in_array($status, [403, 404, 419, 429, 500, 503], true) || $wantsJson || ($status === 500 && config('app.debug'))) {
                return $response;
            }

            App::setLocale(SetLocale::resolve($request));

            // A failed mutation stays on its own address, which may answer no GET, so recovery leads back to the page it came from.
            $referer = (string) $request->headers->get('referer');
            $sameOrigin = str_starts_with($referer.'/', $request->getSchemeAndHttpHost().'/');
            $returnUrl = $request->isMethod('GET') ? null : ($sameOrigin ? $referer : route('home'));

            return Inertia::render('errors/show', ['status' => $status, 'locale' => App::getLocale(), 'returnUrl' => $returnUrl])
                ->toResponse($request)
                ->setStatusCode($status);
        });
    })->create();
