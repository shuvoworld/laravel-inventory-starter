<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Get locale from session, fallback to browser preference, then default
        $locale = Session::get('locale');

        if (!$locale) {
            // Try to get from browser's preferred language
            $browserLocale = $request->getPreferredLanguage(['en', 'bn']);
            $locale = $browserLocale ?: config('app.locale', 'en');
        }

        // Validate locale
        $availableLocales = ['en', 'bn'];
        if (!in_array($locale, $availableLocales)) {
            $locale = 'en';
        }

        // Set the application locale
        App::setLocale($locale);

        // Store in session for persistence
        Session::put('locale', $locale);

        return $next($request);
    }
}