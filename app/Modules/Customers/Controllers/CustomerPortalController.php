<?php

namespace App\Modules\Customers\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Customers\Models\PortalUser;
use App\Modules\Companies\Models\Company;
use App\Modules\Customers\Models\Customer;
use App\Modules\Queue\Models\Ticket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;

class CustomerPortalController extends Controller
{
    /**
     * Show the customer dashboard.
     */
    public function index()
    {
        $portalUser = Auth::guard('customer')->user();
        
        // Find all customer profiles with the same phone number (company-specific or global)
        $normalizedPhone = PortalUser::normalizePhone($portalUser->phone);
        $customerIds = Customer::withoutGlobalScopes()
            ->where('phone', $normalizedPhone)
            ->pluck('id');
            
        // Active tickets across all companies - specifically bypass TenantScope
        $activeTickets = Ticket::withoutGlobalScopes([\App\Modules\Core\Scopes\TenantScope::class, \Illuminate\Database\Eloquent\SoftDeletingScope::class])
            ->whereIn('customer_id', $customerIds)
            ->whereIn('status', ['waiting', 'called', 'serving'])
            ->with(['company', 'service'])
            ->latest()
            ->get();

        // Favorite companies
        $favorites = $portalUser->favoriteCompanies;

        // Recent history
        $history = Ticket::withoutGlobalScopes([\App\Modules\Core\Scopes\TenantScope::class, \Illuminate\Database\Eloquent\SoftDeletingScope::class])
            ->whereIn('customer_id', $customerIds)
            ->whereIn('status', ['done', 'no_show', 'cancelled'])
            ->with(['company', 'service'])
            ->latest()
            ->limit(10)
            ->get();

        return view('customer.dashboard', ['customer' => $portalUser, 'activeTickets' => $activeTickets, 'favorites' => $favorites, 'history' => $history]);
    }

    /**
     * Show login form.
     */
    public function showLoginForm()
    {
        return view('customer.auth.login');
    }

    /**
     * Handle login.
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::guard('customer')->attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();
            
            $this->autoFavoriteFromSession($request);
            
            return redirect()->intended(route('customer.dashboard'));
        }

        return back()->withErrors([
            'email' => __('auth.failed'),
        ])->onlyInput('email');
    }

    /**
     * Show registration form.
     */
    public function showRegistrationForm()
    {
        return view('customer.auth.register');
    }

    /**
     * Handle registration.
     */
    public function register(Request $request)
    {
        $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:portal_users,email'],
            'phone' => ['required', 'string', 'max:20'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $portalUser = PortalUser::create([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'phone' => $request->phone,
            'password' => Hash::make($request->password),
            'locale' => app()->getLocale(),
        ]);

        Auth::guard('customer')->login($portalUser);

        $this->autoFavoriteFromSession($request);

        return redirect()->route('customer.dashboard');
    }

    /**
     * Auto-favorite company if it exists in session.
     */
    protected function autoFavoriteFromSession(Request $request)
    {
        $companyId = $request->session()->get('last_tracking_company_id');
        if ($companyId) {
            $portalUser = Auth::guard('customer')->user();
            if ($portalUser && !$portalUser->favoriteCompanies()->where('companies.id', $companyId)->exists()) {
                $portalUser->favoriteCompanies()->attach($companyId);
            }
        }
    }

    /**
     * Logout.
     */
    public function logout(Request $request)
    {
        Auth::guard('customer')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('landing');
    }

    /**
     * Toggle favorite company.
     */
    public function toggleFavorite(Company $company)
    {
        $portalUser = Auth::guard('customer')->user();
        $portalUser->favoriteCompanies()->toggle($company->id);
        
        return back();
    }

    /**
     * Link a guest ticket to the logged-in customer.
     */
    public function syncTicket(Request $request)
    {
        $ticketId = $request->session()->get('tracking_ticket_id');
        if (!$ticketId) {
            return back()->with('error', 'No active ticket found in session.');
        }

        $portalUser = Auth::guard('customer')->user();
        $ticket = Ticket::withoutGlobalScopes()->find($ticketId);

        if ($ticket && !$ticket->customer_id) {
            // Find or Create a profile for this company to link the ticket properly
            // Or just link to the first matching phone profile
            $profile = Customer::withoutGlobalScopes()
                ->where('company_id', $ticket->company_id)
                ->where('phone', PortalUser::normalizePhone($portalUser->phone))
                ->first();

            if (!$profile) {
                $profile = Customer::create([
                    'company_id' => $ticket->company_id,
                    'first_name' => $portalUser->first_name,
                    'last_name' => $portalUser->last_name,
                    'phone' => $portalUser->phone,
                ]);
            }

            $ticket->customer_id = $profile->id;
            $ticket->save();

            // Award loyalty points to the Portal Account
            $portalUser->increment('points', 10);
            
            // Also auto-favorite the company of this ticket
            if (!$portalUser->favoriteCompanies()->where('companies.id', $ticket->company_id)->exists()) {
                $portalUser->favoriteCompanies()->attach($ticket->company_id);
            }

            return back()->with('success', __('ui.saved_to_account'));
        }

        return back();
    }

    /**
     * Add company to favorites by its unique code.
     */
    public function addCompanyByCode(Request $request)
    {
        $request->validate([
            'company_code' => ['required', 'string', 'max:10'],
        ]);

        $company = Company::where('code', strtoupper($request->company_code))->first();

        if (!$company) {
            return back()->withErrors(['company_code' => __('ui.invalid_company_code') ?? 'Invalid company code.']);
        }

        $portalUser = Auth::guard('customer')->user();
        
        if ($portalUser->favoriteCompanies()->where('companies.id', $company->id)->exists()) {
            return back()->with('info', __('ui.already_in_favorites') ?? 'Company already in favorites.');
        }

        $portalUser->favoriteCompanies()->attach($company->id);

        return back()->with('success', __('ui.company_added_successfully') ?? 'Company added to favorites!');
    }

    /**
     * Directly track a company from favorites.
     * If an active ticket exists, show status. Otherwise, show hub.
     */
    public function trackCompany(Company $company)
    {
        $portalUser = Auth::guard('customer')->user();
        
        $customerIds = Customer::withoutGlobalScopes()
            ->where('phone', PortalUser::normalizePhone($portalUser->phone))
            ->pluck('id');

        $ticket = Ticket::withoutGlobalScopes()
            ->whereIn('customer_id', $customerIds)
            ->where('company_id', $company->id)
            ->whereIn('status', ['waiting', 'called', 'serving'])
            ->latest()
            ->first();
            
        if ($ticket) {
            session(['tracking_ticket_id' => $ticket->id]);
            return redirect()->route('queue.track.status');
        }
        
        return redirect()->route('queue.track.hub', $company->secure_public_token);
    }

    /**
     * Set session for a specific ticket and show status.
     */
    public function trackTicket(Ticket $ticket)
    {
        $portalUser = Auth::guard('customer')->user();
        
        // Find all profile IDs for this phone
        $customerIds = Customer::withoutGlobalScopes()
            ->where('phone', PortalUser::normalizePhone($portalUser->phone))
            ->pluck('id')
            ->toArray();

        // Security check: ticket must belong to one of the profiles for this phone
        if (!in_array($ticket->customer_id, $customerIds)) {
            abort(403);
        }
        
        session(['tracking_ticket_id' => $ticket->id]);
        return redirect()->route('queue.track.status');
    }

    /**
     * Update customer profile.
     */
    public function updateProfile(Request $request)
    {
        $portalUser = Auth::guard('customer')->user();
        
        $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:portal_users,email,' . $portalUser->id],
            'phone' => ['required', 'string', 'max:20', 'unique:portal_users,phone,' . $portalUser->id],
        ]);

        $portalUser->update($request->only('first_name', 'last_name', 'email', 'phone'));

        return back()->with('success', __('ui.profile_updated') ?? 'Profile updated successfully!');
    }

    /**
     * Remove company from favorites.
     */
    public function removeFavorite(Company $company)
    {
        $portalUser = Auth::guard('customer')->user();
        $portalUser->favoriteCompanies()->detach($company->id);
        
        return back()->with('success', __('ui.company_removed') ?? 'Company removed from favorites.');
    }
}
