<?php

namespace App\Http\Controllers;

use App\Services\HelpCenterService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;

class HelpCenterController extends Controller
{
    protected HelpCenterService $helpService;

    public function __construct(HelpCenterService $helpService)
    {
        $this->helpService = $helpService;
    }

    /**
     * Display Help Center Landing Page
     */
    public function index(Request $request)
    {
        $locale = App::getLocale();
        $user = Auth::user();
        $roleName = $user ? strtolower($user->role->name ?? 'admin') : 'customer';

        if ($user && $user->is_system_admin) {
            $roleName = 'admin';
        }

        $categories = $this->helpService->getCategoriesWithArticles($locale, $roleName);
        $searchQuery = $request->get('q');
        $searchResults = [];

        if ($searchQuery) {
            $searchResults = $this->helpService->search($searchQuery, $locale, $roleName);
        }

        return view('help.index', compact('categories', 'searchQuery', 'searchResults'));
    }

    /**
     * Display Article Details Page
     */
    public function show(string $categorySlug, string $articleSlug)
    {
        $locale = App::getLocale();
        $user = Auth::user();
        $roleName = $user ? strtolower($user->role->name ?? 'admin') : 'customer';

        if ($user && $user->is_system_admin) {
            $roleName = 'admin';
        }

        $article = $this->helpService->getArticle($categorySlug, $articleSlug, $locale, $roleName);

        if (!$article) {
            abort(404, 'Help Article Not Found or Access Restricted');
        }

        return view('help.show', compact('article'));
    }

    /**
     * API Contextual Help for Offcanvas Drawer
     */
    public function contextual(Request $request)
    {
        $locale = App::getLocale();
        $routeName = $request->get('route_name', 'dashboard');
        $user = Auth::user();
        $roleName = $user ? strtolower($user->role->name ?? 'admin') : 'customer';

        if ($user && $user->is_system_admin) {
            $roleName = 'admin';
        }

        $article = $this->helpService->getContextualArticle($routeName, $locale, $roleName);

        return response()->json([
            'success' => true,
            'article' => $article
        ]);
    }

    /**
     * Ajax Search for Help Articles
     */
    public function search(Request $request)
    {
        $locale = App::getLocale();
        $query = $request->get('q', '');
        $user = Auth::user();
        $roleName = $user ? strtolower($user->role->name ?? 'admin') : 'customer';

        $results = $this->helpService->search($query, $locale, $roleName);

        return response()->json([
            'success' => true,
            'results' => $results
        ]);
    }

    /**
     * Mark onboarding tour as completed for current user session
     */
    public function completeOnboarding(Request $request)
    {
        $user = Auth::user();
        if ($user) {
            session(['onboarding_tour_completed_' . $user->id => true]);
        } else {
            session(['onboarding_tour_completed_guest' => true]);
        }

        return response()->json(['success' => true]);
    }
}
