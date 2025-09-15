<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;

class AutoFlashSuccess
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        // Only act on redirect responses in the web context
        if ($response instanceof RedirectResponse) {
            $session = $request->session();

            // If there are validation errors or an explicit message already set, do nothing
            $hasErrors = $session->has('errors') && ($session->get('errors')->any() ?? false);
            $hasMessage = $session->has('success') || $session->has('error') || $session->has('warning') || $session->has('info') || $session->has('status');

            if (!$hasErrors && !$hasMessage) {
                // Determine intent based on HTTP method (consider method spoofing)
                $method = strtoupper($request->input('_method', $request->method()));
                if ($method === 'DELETE') {
                    $session->flash('success', __('Deleted successfully.'));
                } elseif (in_array($method, ['POST', 'PUT', 'PATCH'], true)) {
                    $session->flash('success', __('Saved successfully.'));
                }
            }
        }

        return $response;
    }
}
