<?php

namespace App\Http\Controllers\Auth;

use App\Actions\Auth\LoginUserWithGoogle;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Laravel\Socialite\Facades\Socialite;

class GoogleLoginController extends Controller
{
    /**
     * Redirect the user to the Google authentication page.
     */
    public function redirect(): RedirectResponse
    {
        return Socialite::driver('google')->redirect();
    }

    /**
     * Obtain the user information from Google.
     */
    public function callback(LoginUserWithGoogle $loginUserWithGoogle): RedirectResponse
    {
        try {
            $googleUser = Socialite::driver('google')->user();
        } catch (\Exception $e) {
            return redirect()->route('login')->withErrors([
                'email' => 'Error al autenticarse con Google.',
            ]);
        }

        $loginUserWithGoogle->handle($googleUser);

        return redirect()->intended(route('dashboard', absolute: false));
    }
}
