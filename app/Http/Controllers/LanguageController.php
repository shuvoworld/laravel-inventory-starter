<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\App;

class LanguageController extends Controller
{
    /**
     * Switch the application language
     */
    public function switch(Request $request, $locale)
    {
        // Validate the locale
        $availableLocales = ['en', 'bn'];

        if (!in_array($locale, $availableLocales)) {
            abort(400, 'Invalid locale');
        }

        // Store the locale in session
        Session::put('locale', $locale);

        // Set the application locale
        App::setLocale($locale);

        // Redirect back to the previous page
        return redirect()->back()->with('success', __('common.language') . ' changed successfully');
    }

    /**
     * Get the current locale
     */
    public function current()
    {
        return response()->json([
            'current' => app()->getLocale(),
            'available' => [
                'en' => 'English',
                'bn' => 'বাংলা'
            ]
        ]);
    }
}