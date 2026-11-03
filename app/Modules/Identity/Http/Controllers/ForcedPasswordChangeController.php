<?php

namespace App\Modules\Identity\Http\Controllers;

use App\Models\User;
use App\Modules\Identity\Actions\ChangePassword;
use App\Modules\Identity\Events\PasswordChanged;
use App\Modules\Identity\Http\Requests\ForcedPasswordChangeRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Password;
use Inertia\Inertia;
use Inertia\Response;

/**
 * The password change an account must make before anything else (SECURITY_MODEL section 3, TASK-010 TAC-04).
 */
class ForcedPasswordChangeController
{
    public function edit(Request $request): Response|RedirectResponse
    {
        if (! $this->mustChange($request)) {
            return redirect()->route('dashboard');
        }

        return Inertia::render('auth/change-password', [
            'passwordRules' => Password::defaults()->toPasswordRulesString(),
        ]);
    }

    public function update(ForcedPasswordChangeRequest $request, ChangePassword $changePassword): RedirectResponse
    {
        if (! $this->mustChange($request)) {
            return redirect()->route('dashboard');
        }

        $changePassword->handle($request->user(), $request->string('password')->value(), PasswordChanged::FORCED, $request->session()->getId());

        // A new id for the session that stays; the old row goes with it.
        $request->session()->regenerate(true);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('auth.password_changed')]);

        return redirect()->intended(route('dashboard', absolute: false));
    }

    private function mustChange(Request $request): bool
    {
        $user = $request->user();

        return $user instanceof User && $user->must_change_password;
    }
}
