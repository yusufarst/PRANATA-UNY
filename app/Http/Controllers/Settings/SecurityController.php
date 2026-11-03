<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\PasswordUpdateRequest;
use App\Http\Requests\Settings\TwoFactorAuthenticationRequest;
use App\Modules\Identity\Actions\ChangePassword;
use App\Modules\Identity\Events\PasswordChanged;
use App\Modules\Identity\Support\TwoFactorPolicy;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\Rules\Password;
use Inertia\Inertia;
use Inertia\Response;
use Laravel\Fortify\Features;

class SecurityController extends Controller
{
    /**
     * Show the user's security settings page.
     */
    public function edit(TwoFactorAuthenticationRequest $request, TwoFactorPolicy $twoFactorPolicy): Response
    {
        $props = [
            'canManageTwoFactor' => Features::canManageTwoFactorAuthentication(),
            'passwordRules' => Password::defaults()->toPasswordRulesString(),
        ];

        if (Features::canManageTwoFactorAuthentication()) {
            $request->ensureStateIsValid();

            $props['twoFactorEnabled'] = $request->user()->hasEnabledTwoFactorAuthentication();
            $props['requiresConfirmation'] = Features::optionEnabled(Features::twoFactorAuthentication(), 'confirm');
            $props['twoFactorRequired'] = $twoFactorPolicy->isRequiredFor($request->user());
        }

        return Inertia::render('settings/security', $props);
    }

    /**
     * Update the user's password.
     */
    public function update(PasswordUpdateRequest $request, ChangePassword $changePassword): RedirectResponse
    {
        $changePassword->handle($request->user(), $request->string('password')->value(), PasswordChanged::SETTINGS, $request->session()->getId());

        // A new id for the session that stays; the old row goes with it.
        $request->session()->regenerate(true);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('auth.password_changed')]);

        return back();
    }
}
