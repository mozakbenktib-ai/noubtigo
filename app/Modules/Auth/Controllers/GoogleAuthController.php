<?php

namespace App\Modules\Auth\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;

class GoogleAuthController extends Controller
{
    /**
     * Redirect the user to the Google authentication page.
     */
    public function redirect()
    {
        return Socialite::driver('google')->redirect();
    }

    /**
     * Obtain the user information from Google.
     */
    public function callback()
    {
        try {
            $googleUser = Socialite::driver('google')->user();
        } catch (\Exception $e) {
            return redirect('/login')->with('error', 'Google authentication failed. Please try again.');
        }

        // Check if a user with this email or google_id already exists
        $user = User::where('google_id', $googleUser->id)->orWhere('email', $googleUser->email)->first();

        if ($user) {
            // Update google_id if not set
            if (!$user->google_id) {
                $user->update(['google_id' => $googleUser->id]);
            }

            Auth::login($user);
            return redirect()->intended(route('dashboard'))->with('success', 'Logged in successfully with Google!');
        }

        // New user - redirect to complete registration with company details
        // Split name into first and last
        $nameParts = explode(' ', $googleUser->name, 2);
        $firstName = $nameParts[0];
        $lastName = $nameParts[1] ?? '';

        session([
            'google_user' => [
                'google_id' => $googleUser->id,
                'email' => $googleUser->email,
                'first_name' => $firstName,
                'last_name' => $lastName,
            ]
        ]);

        return redirect()->route('register.google-complete');
    }
}
