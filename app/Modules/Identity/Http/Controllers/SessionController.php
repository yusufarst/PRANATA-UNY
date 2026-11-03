<?php

namespace App\Modules\Identity\Http\Controllers;

use App\Modules\Identity\Actions\RevokeSessions;
use App\Modules\Identity\Events\SessionsRevoked;
use App\Modules\Identity\Support\ActiveSessions;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * The active sessions of the signed-in account, part of PG-007 Akun & keamanan (TASK-011 TAC-04). The account ends any
 * other session here; it ends the current one by signing out.
 */
class SessionController
{
    public function index(Request $request): Response
    {
        return Inertia::render('settings/sessions', [
            'sessions' => ActiveSessions::of($request->user(), $request->session()->getId()),
        ]);
    }

    public function revoke(Request $request, string $session, RevokeSessions $revokeSessions): RedirectResponse
    {
        $revokeSessions->handle($request->user(), SessionsRevoked::ONE, $session, $request->session()->getId());

        Inertia::flash('toast', ['type' => 'success', 'message' => __('identity.session_revoked')]);

        return to_route('identity.sessions.index');
    }

    public function revokeOthers(Request $request, RevokeSessions $revokeSessions): RedirectResponse
    {
        $revokeSessions->handle($request->user(), SessionsRevoked::OTHERS, keepSessionId: $request->session()->getId());

        Inertia::flash('toast', ['type' => 'success', 'message' => __('identity.other_sessions_revoked')]);

        return to_route('identity.sessions.index');
    }
}
