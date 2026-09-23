<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class PasswordChangeController extends Controller
{
    /**
     * Show the force password change view.
     */
    public function show()
    {
        // If they somehow get here but don't need to change password, redirect.
        if (!auth()->user()->requires_password_change) {
            return redirect()->route('dashboard');
        }

        return view('auth.force-password-change');
    }

    /**
     * Handle the password update.
     */
    public function update(Request $request)
    {
        $request->validate([
            'password' => ['required', 'string', 'min:6', 'confirmed'],
        ]);

        $user = auth()->user();

        $user->update([
            'password' => Hash::make($request->password),
            'requires_password_change' => false,
        ]);

        return redirect()->route('dashboard')->with('success', 'Password successfully changed. Welcome!');
    }
}
