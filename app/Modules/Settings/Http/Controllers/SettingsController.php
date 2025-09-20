<?php

namespace App\Modules\Settings\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Settings\Models\Settings;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\Storage;

/**
 * Controller for managing store settings.
 */
class SettingsController extends Controller
{
    public function index(): View
    {
        $settings = [
            'store' => [
                'store_name' => Settings::get('store_name', ''),
                'store_description' => Settings::get('store_description', ''),
                'store_address' => Settings::get('store_address', ''),
                'store_city' => Settings::get('store_city', ''),
                'store_state' => Settings::get('store_state', ''),
                'store_postal_code' => Settings::get('store_postal_code', ''),
                'store_country' => Settings::get('store_country', ''),
                'store_phone' => Settings::get('store_phone', ''),
                'store_email' => Settings::get('store_email', ''),
                'store_website' => Settings::get('store_website', ''),
                'store_logo' => Settings::get('store_logo', ''),
                'store_currency' => Settings::get('store_currency', 'USD'),
                'store_tax_rate' => Settings::get('store_tax_rate', '0'),
            ],
            'business' => [
                'business_registration' => Settings::get('business_registration', ''),
                'tax_id' => Settings::get('tax_id', ''),
                'bank_name' => Settings::get('bank_name', ''),
                'bank_account' => Settings::get('bank_account', ''),
            ]
        ];

        return view('settings::index', compact('settings'));
    }

    public function update(Request $request): RedirectResponse
    {
        $request->validate([
            'store_name' => 'nullable|string|max:255',
            'store_description' => 'nullable|string',
            'store_address' => 'nullable|string',
            'store_city' => 'nullable|string|max:255',
            'store_state' => 'nullable|string|max:255',
            'store_postal_code' => 'nullable|string|max:20',
            'store_country' => 'nullable|string|max:255',
            'store_phone' => 'nullable|string|max:20',
            'store_email' => 'nullable|email|max:255',
            'store_website' => 'nullable|url|max:255',
            'store_currency' => 'nullable|string|max:10',
            'store_tax_rate' => 'nullable|numeric|min:0|max:100',
            'business_registration' => 'nullable|string|max:255',
            'tax_id' => 'nullable|string|max:255',
            'bank_name' => 'nullable|string|max:255',
            'bank_account' => 'nullable|string|max:255',
            'store_logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        // Handle logo upload
        if ($request->hasFile('store_logo')) {
            // Delete old logo
            $oldLogo = Settings::get('store_logo');
            if ($oldLogo && Storage::disk('public')->exists($oldLogo)) {
                Storage::disk('public')->delete($oldLogo);
            }

            // Store new logo
            $logoPath = $request->file('store_logo')->store('logos', 'public');
            Settings::set('store_logo', $logoPath, 'string', 'store');
        }

        // Update all settings
        foreach ($request->except(['_token', 'store_logo']) as $key => $value) {
            $type = in_array($key, ['store_tax_rate']) ? 'float' : 'string';
            $group = str_starts_with($key, 'business_') || in_array($key, ['tax_id', 'bank_name', 'bank_account']) ? 'business' : 'store';
            Settings::set($key, $value, $type, $group);
        }

        return redirect()->route('modules.settings.index')->with('success', 'Settings updated successfully.');
    }
}
