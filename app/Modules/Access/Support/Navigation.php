<?php

namespace App\Modules\Access\Support;

use App\Models\User;
use Illuminate\Support\Facades\Route;

/**
 * The workspaces and menu a user may open (ACCESS_CONTROL sections 3 and 8, IX-016). A menu entry appears only when the user
 * holds one of its capabilities somewhere, and a workspace other than Beranda only when it has an entry to open (TASK-014
 * TAC-01). Selecting a workspace grants nothing: every route checks its capability again.
 *
 * A module adds its pages here with the capability that opens them; a workspace without a page yet stays hidden, so the
 * menu never leads to a dead end.
 */
final class Navigation
{
    /**
     * The order of the workspace switcher; Beranda is always there.
     *
     * @var list<string>
     */
    private const WORKSPACES = ['home', 'procurement', 'warehouse', 'asset', 'administration'];

    /**
     * Key => [workspace, route name, capabilities of which one opens the entry].
     *
     * @var array<string, array{string, string, list<string>}>
     */
    private const ENTRIES = [
        'users' => ['administration', 'identity.admin.users.index', ['admin.users.manage']],
        'organization' => ['administration', 'organization.admin.index', ['admin.org.manage']],
        'access' => ['administration', 'access.admin.grants.index', ['admin.access.grant', 'admin.access.approve']],
    ];

    public function __construct(private readonly AccessEvaluator $evaluator) {}

    /**
     * The menu entries the user may open, Beranda first.
     *
     * @return list<array{key: string, workspace: string, href: string}>
     */
    public function menu(User $user): array
    {
        $menu = [['key' => 'home', 'workspace' => 'home', 'href' => route('dashboard', absolute: false)]];

        foreach (self::ENTRIES as $key => [$workspace, $route, $capabilities]) {
            if (Route::has($route) && $this->holdsAny($user, $capabilities)) {
                $menu[] = ['key' => $key, 'workspace' => $workspace, 'href' => route($route, absolute: false)];
            }
        }

        return $menu;
    }

    /**
     * The workspaces with at least one entry of the menu; each opens at its first entry.
     *
     * @param  list<array{key: string, workspace: string, href: string}>  $menu
     * @return list<array{key: string, href: string}>
     */
    public static function workspaces(array $menu): array
    {
        $workspaces = [];

        foreach (self::WORKSPACES as $workspace) {
            foreach ($menu as $entry) {
                if ($entry['workspace'] === $workspace) {
                    $workspaces[] = ['key' => $workspace, 'href' => $entry['href']];

                    break;
                }
            }
        }

        return $workspaces;
    }

    /**
     * @param  list<string>  $capabilities
     */
    private function holdsAny(User $user, array $capabilities): bool
    {
        foreach ($capabilities as $capability) {
            if ($this->evaluator->allows($user, $capability)) {
                return true;
            }
        }

        return false;
    }
}
