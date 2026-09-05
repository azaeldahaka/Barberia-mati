<?php

namespace App\Actions\Auth;

use App\Actions\Roles\AssignDefaultRoleToUser;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Laravel\Socialite\Contracts\User as SocialiteUser;

class LoginUserWithGoogle
{
    /**
     * Handle the action.
     */
    public function handle(SocialiteUser $googleUser): void
    {
        $user = User::where('google_id', $googleUser->getId())
            ->orWhere('email', $googleUser->getEmail())
            ->first();

        if ($user) {
            // Update the google_id if it's missing (e.g., they registered normally before)
            if (! $user->google_id) {
                $user->update(['google_id' => $googleUser->getId()]);
            }
        } else {
            // Create a new user
            $user = User::create([
                'name' => $googleUser->getName(),
                'email' => $googleUser->getEmail(),
                'google_id' => $googleUser->getId(),
                'password' => bcrypt(Str::random(16)), // Required field, random for security
            ]);

            // Assign default role
            app(AssignDefaultRoleToUser::class)->handle($user);
        }

        // Login the user
        Auth::login($user);
    }
}
