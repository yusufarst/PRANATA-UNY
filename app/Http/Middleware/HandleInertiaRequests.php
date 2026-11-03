<?php

namespace App\Http\Middleware;

use App\Modules\Access\Support\Navigation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $user = $request->user();
        $menu = $user === null ? [] : app(Navigation::class)->menu($user);

        return [
            ...parent::share($request),
            'name' => config('app.name'),
            'auth' => [
                'user' => $user,
                // The menu and the actions mirror the capabilities only; every route checks them again (ACCESS_CONTROL
                // section 8). Read on every request, so a revoked grant leaves the menu at the next one (TAC-04).
                'can' => [
                    'manageUsers' => $user?->can('admin.users.manage') ?? false,
                    'manageOrganization' => $user?->can('admin.org.manage') ?? false,
                    'grantAccess' => $user?->can('admin.access.grant') ?? false,
                    'approveAccess' => $user?->can('admin.access.approve') ?? false,
                ],
            ],
            'sidebarOpen' => ! $request->hasCookie('sidebar_state') || $request->cookie('sidebar_state') === 'true',
            'locale' => App::getLocale(),
            // TAC-01: a workspace and a menu entry appear only with a grant that opens them.
            'menu' => $menu,
            'workspaces' => Navigation::workspaces($menu),
        ];
    }
}
