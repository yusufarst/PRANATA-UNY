<?php

use Illuminate\Routing\Route as AppRoute;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Route;

/*
 * Routes that need no sign-in. The storage routes come from the framework and validate a signature themselves.
 * The language choice only changes the caller's own session.
 */
const PUBLIC_ROUTES = [
    'home',
    'locale.update',
    'login',
    'login.store',
    'password.email',
    'password.request',
    'password.reset',
    'password.update',
    'storage.local',
    'storage.local.upload',
    'two-factor.login',
    'two-factor.login.store',
];

/*
 * Routes that act only on the signed-in user's own account, so they have no object to authorize.
 */
const OWN_ACCOUNT_ROUTES = [
    'dashboard',
    'logout',
    'password.confirm',
    'password.confirm.store',
    'password.confirmation',
    'profile.destroy',
    'profile.edit',
    'profile.update',
    'security.edit',
    'two-factor.confirm',
    'two-factor.disable',
    'two-factor.enable',
    'two-factor.qr-code',
    'two-factor.recovery-codes',
    'two-factor.regenerate-recovery-codes',
    'two-factor.secret-key',
    'user-password.update',
    'verification.notice',
    'verification.send',
    'verification.verify',
];

/**
 * @return Collection<string, AppRoute>
 */
function namedRoutes(): Collection
{
    return collect(Route::getRoutes()->getRoutes())
        ->filter(fn (AppRoute $route) => $route->getName() !== null)
        ->keyBy(fn (AppRoute $route) => $route->getName());
}

function checksAuthorization(AppRoute $route): bool
{
    foreach ($route->gatherMiddleware() as $middleware) {
        if (is_string($middleware) && str_starts_with($middleware, 'can:')) {
            return true;
        }
    }

    $uses = $route->getAction('uses');

    $reflection = match (true) {
        $uses instanceof Closure => new ReflectionFunction($uses),
        is_string($uses) && str_contains($uses, '@') => new ReflectionMethod(...explode('@', $uses)),
        default => null,
    };

    if ($reflection === null || $reflection->getFileName() === false) {
        return false;
    }

    $source = implode('', array_slice(
        file($reflection->getFileName()),
        $reflection->getStartLine() - 1,
        $reflection->getEndLine() - $reflection->getStartLine() + 1,
    ));

    // Only calls that stop the request count: Gate::allows() and its like merely return a boolean.
    return preg_match('/->authorize\(|Gate::authorize\(/', $source) === 1;
}

test('every named route outside the public list requires sign-in', function () {
    $unprotected = namedRoutes()
        ->reject(fn (AppRoute $route, string $name) => in_array($name, PUBLIC_ROUTES, true))
        ->reject(fn (AppRoute $route) => collect($route->gatherMiddleware())
            ->contains(fn ($middleware) => is_string($middleware) && ($middleware === 'auth' || str_starts_with($middleware, 'auth:'))))
        ->keys();

    expect($unprotected->all())->toBe([]);
});

test('every signed-in route checks authorization or acts on the own account only', function () {
    $unchecked = namedRoutes()
        ->reject(fn (AppRoute $route, string $name) => in_array($name, [...PUBLIC_ROUTES, ...OWN_ACCOUNT_ROUTES], true))
        ->reject(fn (AppRoute $route) => checksAuthorization($route))
        ->keys();

    expect($unchecked->all())->toBe([]);
});

test('the route lists name only routes that exist', function () {
    $names = namedRoutes()->keys()->all();

    expect(array_diff([...PUBLIC_ROUTES, ...OWN_ACCOUNT_ROUTES], $names))->toBe([]);
});
