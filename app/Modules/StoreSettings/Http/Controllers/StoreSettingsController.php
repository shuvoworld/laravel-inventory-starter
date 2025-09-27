<?php

namespace App\Modules\StoreSettings\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\StoreSettings\Models\StoreSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class StoreSettingsController extends Controller
{
    public function index()
    {
        $settings = StoreSetting::getAllGrouped();
        $groups = StoreSetting::getGroups();

        return view('StoreSettings::index', compact('settings', 'groups'));
    }

    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'settings' => 'required|array',
            'settings.*' => 'nullable'
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $settings = $request->input('settings', []);
        $updatedCount = 0;

        foreach ($settings as $key => $value) {
            $setting = StoreSetting::where('key', $key)->first();

            if (!$setting) {
                continue;
            }

            // Handle file uploads
            if ($setting->type === 'file' && $request->hasFile("settings.{$key}")) {
                $file = $request->file("settings.{$key}");

                // Delete old file if exists
                if ($setting->value && Storage::exists($setting->value)) {
                    Storage::delete($setting->value);
                }

                // Store new file
                $path = $file->store('settings', 'public');
                $value = $path;
            } elseif ($setting->type === 'file' && empty($value)) {
                // Don't update file fields if no new file uploaded
                continue;
            }

            // Validate based on type
            if (!$this->validateSettingValue($value, $setting)) {
                return back()->withErrors([
                    "settings.{$key}" => "Invalid value for {$setting->label}"
                ])->withInput();
            }

            if (StoreSetting::set($key, $value)) {
                $updatedCount++;
            }
        }

        return back()->with('success', "Updated {$updatedCount} settings successfully.");
    }

    public function reset(Request $request)
    {
        $group = $request->input('group');

        if (!$group || !array_key_exists($group, StoreSetting::getGroups())) {
            return back()->withErrors(['error' => 'Invalid settings group.']);
        }

        $settings = StoreSetting::where('group', $group)->get();
        $resetCount = 0;

        foreach ($settings as $setting) {
            // Get default value from seeder data
            $defaultValue = $this->getDefaultValue($setting->key);

            if ($defaultValue !== null && StoreSetting::set($setting->key, $defaultValue)) {
                $resetCount++;
            }
        }

        return back()->with('success', "Reset {$resetCount} {$group} settings to defaults.");
    }

    public function clearCache()
    {
        StoreSetting::clearCache();

        return back()->with('success', 'Settings cache cleared successfully.');
    }

    public function export()
    {
        $settings = StoreSetting::getAllGrouped();

        $export = [];
        foreach ($settings as $group => $groupSettings) {
            foreach ($groupSettings as $setting) {
                $export[$setting['key']] = $setting['value'];
            }
        }

        $filename = 'store-settings-' . date('Y-m-d-H-i-s') . '.json';

        return response()->json($export)
            ->header('Content-Disposition', "attachment; filename={$filename}")
            ->header('Content-Type', 'application/json');
    }

    public function import(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'settings_file' => 'required|file|mimes:json|max:2048'
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator);
        }

        try {
            $content = file_get_contents($request->file('settings_file')->getRealPath());
            $importedSettings = json_decode($content, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                return back()->withErrors(['settings_file' => 'Invalid JSON file.']);
            }

            $importedCount = 0;
            foreach ($importedSettings as $key => $value) {
                if (StoreSetting::set($key, $value)) {
                    $importedCount++;
                }
            }

            return back()->with('success', "Imported {$importedCount} settings successfully.");

        } catch (\Exception $e) {
            return back()->withErrors(['settings_file' => 'Error processing file: ' . $e->getMessage()]);
        }
    }

    private function validateSettingValue($value, $setting): bool
    {
        if ($value === null || $value === '') {
            return true; // Allow empty values
        }

        switch ($setting->type) {
            case 'integer':
                return is_numeric($value) && is_int($value + 0);

            case 'decimal':
            case 'float':
                return is_numeric($value);

            case 'boolean':
                return in_array($value, [true, false, 1, 0, '1', '0', 'true', 'false'], true);

            case 'email':
                return filter_var($value, FILTER_VALIDATE_EMAIL) !== false;

            case 'url':
                return filter_var($value, FILTER_VALIDATE_URL) !== false;

            case 'json':
                json_decode($value);
                return json_last_error() === JSON_ERROR_NONE;

            case 'select':
                if ($setting->options && isset($setting->options['options'])) {
                    return array_key_exists($value, $setting->options['options']);
                }
                return true;

            default:
                return true;
        }
    }

    private function getDefaultValue(string $key)
    {
        $defaults = [
            'company_name' => 'Your Company Name',
            'company_address' => '123 Business Street',
            'company_city' => 'Business City',
            'company_state' => 'State',
            'company_postal_code' => '12345',
            'company_country' => 'United States',
            'company_phone' => '(555) 123-4567',
            'company_email' => 'contact@company.com',
            'company_website' => 'https://company.com',
            'currency_code' => 'USD',
            'currency_symbol' => '$',
            'currency_position' => 'before',
            'decimal_places' => '2',
            'thousands_separator' => ',',
            'decimal_separator' => '.',
            'business_hours' => 'Monday - Friday: 9:00 AM - 6:00 PM',
            'tax_rate' => '8.5',
            'invoice_terms' => 'Payment due within 30 days',
            'timezone' => 'America/New_York',
            'date_format' => 'M j, Y',
            'time_format' => 'g:i A'
        ];

        return $defaults[$key] ?? null;
    }

    public static function getCurrencySymbolDefaults()
    {
        return [
            'USD' => '$',
            'EUR' => '€',
            'GBP' => '£',
            'CAD' => 'C$',
            'AUD' => 'A$',
            'JPY' => '¥',
            'CNY' => '¥',
            'INR' => '₹',
            'BDT' => '৳'
        ];
    }

    public static function getGroupIcon($group): string
    {
        $icons = [
            'company' => 'building',
            'currency' => 'dollar-sign',
            'business' => 'briefcase',
            'system' => 'cogs'
        ];
        return $icons[$group] ?? 'cog';
    }
}