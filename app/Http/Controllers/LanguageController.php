<?php

namespace App\Http\Controllers;

use App\Models\Language;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LanguageController extends Controller
{
    /**
     * Get all active languages.
     */
    public function index()
    {
        $languages = Language::where('is_active', true)->get();
        return response()->json($languages);
    }

    /**
     * Switch the current language.
     */
    public function switch(Request $request)
    {
        $request->validate([
            'locale' => 'required|string|exists:languages,code',
        ]);

        $locale = $request->input('locale');

        if (Auth::check()) {
            $user = Auth::user();
            $user->locale = $locale;
            $user->save();
        }

        session(['locale' => $locale]);

        return redirect()->back();
    }
}
