<?php

namespace App\Http\Controllers;

use App\Http\Middleware\SetLocale;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class LocaleController extends Controller
{
    public function __invoke(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'locale' => ['required', 'string', Rule::in(SetLocale::SUPPORTED)],
        ]);

        $request->session()->put('locale', $validated['locale']);

        // A signed-in user keeps the choice for the next sign-in, on any device.
        $user = $request->user();

        if ($user instanceof User && $user->locale !== $validated['locale']) {
            $user->forceFill(['locale' => $validated['locale']])->save();
        }

        return back();
    }
}
