<?php

namespace App\Modules\Auth\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Auth\Services\RegisterService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class RegisterController extends Controller
{
    protected $registerService;

    public function __construct(RegisterService $registerService)
    {
        $this->registerService = $registerService;
    }

    /**
     * Show the registration form.
     */
    public function showRegistrationForm()
    {
        return view('auth.register');
    }

    /**
     * Handle the registration of a new company and owner.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'company_name' => 'required|string|max:255',
            
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'phone' => 'nullable|string|max:20|unique:users,phone',
            'password' => 'required|string|min:8|confirmed',
            'timezone' => 'nullable|string',
            'plan' => 'nullable|string|exists:plans,slug',
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

        try {
            $result = $this->registerService->registerCompany($request->all());

            if ($request->expectsJson()) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'Company created successfully',
                    'data' => $result
                ], 201);
            }

            // Authenticate the user
            auth()->login($result['user']);

            $redirectParams = [];
            $promoCode = $request->input('promo_code', $request->input('promo', $request->input('coupon', $request->input('code'))));
            if (!empty($promoCode)) {
                $redirectParams['promo'] = $promoCode;
            }

            return redirect()->route('billing.index', $redirectParams)->with('success', 'Welcome to Noubtigo! Your company has been registered.');
        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Could not create company',
                    'error' => $e->getMessage()
                ], 500);
            }

            return redirect()->back()->with('error', 'Could not create company: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Show the Google complete registration form.
     */
    public function showGoogleCompleteForm()
    {
        if (!session()->has('google_user')) {
            return redirect()->route('register');
        }

        return view('auth.google-complete');
    }

    /**
     * Complete the registration for a Google user.
     */
    public function completeGoogleRegistration(Request $request)
    {
        if (!session()->has('google_user')) {
            return redirect()->route('register');
        }

        $validator = Validator::make($request->all(), [
            'company_name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20|unique:users,phone',
            'timezone' => 'nullable|string',
            'plan' => 'nullable|string|exists:plans,slug',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $googleUser = session('google_user');
        
        $data = $request->all();
        $data['first_name'] = $googleUser['first_name'];
        $data['last_name'] = $googleUser['last_name'];
        $data['email'] = $googleUser['email'];
        // Generate a random password since they are logging in with Google
        $data['password'] = \Illuminate\Support\Str::random(16);

        try {
            $result = $this->registerService->registerCompany($data);
            
            // Set google_id on the newly created user
            $user = $result['user'];
            $user->update(['google_id' => $googleUser['google_id']]);

            // Clear session
            session()->forget('google_user');

            // Authenticate the user
            auth()->login($user);

            $redirectParams = [];
            $promoCode = $request->input('promo_code', $request->input('promo', $request->input('coupon', $request->input('code'))));
            if (!empty($promoCode)) {
                $redirectParams['promo'] = $promoCode;
            }

            return redirect()->route('billing.index', $redirectParams)->with('success', 'Welcome to Noubtigo! Your company has been registered with Google.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Could not create company: ' . $e->getMessage())->withInput();
        }
    }
}
