<?php

namespace App\Modules\Auth\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class LoginController extends Controller
{
    /**
     * Show the login form.
     */
    public function showLoginForm()
    {
        return view('auth.login');
    }

    /**
     * Handle the login request.
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'login' => 'required|string',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'status' => 'error',
                    'errors' => $validator->errors()
                ], 422);
            }
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $loginType = filter_var($request->input('login'), FILTER_VALIDATE_EMAIL) ? 'email' : 'username';

        $credentials = [
            $loginType => $request->input('login'),
            'password' => $request->input('password')
        ];

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            Auth::logoutOtherDevices($request->password);
            
            $user = Auth::user();
            
            if ($request->expectsJson()) {
                $token = $user->createToken('auth_token')->plainTextToken;
                return response()->json([
                    'status' => 'success',
                    'message' => 'Logged in successfully',
                    'token' => $token,
                    'user' => $user
                ]);
            }

            return redirect()->intended(route('dashboard'))
                ->with('success', 'Logged in successfully!');
        }

        if ($request->expectsJson()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid credentials'
            ], 401);
        }

        return redirect()->back()
            ->withErrors(['login' => 'The provided credentials do not match our records.'])
            ->withInput();
    }

    /**
     * Handle logout.
     */
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        if ($request->expectsJson()) {
            return response()->json(['message' => 'Logged out successfully']);
        }

        return redirect('/login')->with('success', 'Logged out successfully!');
    }
}
