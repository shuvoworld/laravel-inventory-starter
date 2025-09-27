<?php

namespace App\Modules\StoreSettings\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class StoreSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'key',
        'value',
        'type',
        'group',
        'label',
        'description',
        'options',
        'is_public',
        'sort_order'
    ];

    protected $casts = [
        'options' => 'array',
        'is_public' => 'boolean'
    ];

    /**
     * Get a setting value by key
     */
    public static function get(string $key, $default = null)
    {
        $cacheKey = "store_setting_{$key}";

        return Cache::remember($cacheKey, 3600, function () use ($key, $default) {
            $setting = static::where('key', $key)->first();

            if (!$setting) {
                return $default;
            }

            return static::castValue($setting->value, $setting->type);
        });
    }

    /**
     * Set a setting value by key
     */
    public static function set(string $key, $value): bool
    {
        $setting = static::where('key', $key)->first();

        if (!$setting) {
            return false;
        }

        $setting->value = $value;
        $result = $setting->save();

        // Clear cache
        Cache::forget("store_setting_{$key}");
        Cache::forget('all_store_settings');

        return $result;
    }

    /**
     * Get all settings grouped by category
     */
    public static function getAllGrouped(): array
    {
        return Cache::remember('all_store_settings', 3600, function () {
            $settings = static::orderBy('group')->orderBy('sort_order')->get();

            $grouped = [];
            foreach ($settings as $setting) {
                $grouped[$setting->group][] = [
                    'key' => $setting->key,
                    'value' => static::castValue($setting->value, $setting->type),
                    'raw_value' => $setting->value,
                    'type' => $setting->type,
                    'label' => $setting->label,
                    'description' => $setting->description,
                    'options' => $setting->options,
                    'is_public' => $setting->is_public
                ];
            }

            return $grouped;
        });
    }

    /**
     * Get public settings for frontend use
     */
    public static function getPublicSettings(): array
    {
        $cacheKey = 'public_store_settings';

        return Cache::remember($cacheKey, 3600, function () {
            $settings = static::where('is_public', true)->get();

            $public = [];
            foreach ($settings as $setting) {
                $public[$setting->key] = static::castValue($setting->value, $setting->type);
            }

            return $public;
        });
    }

    /**
     * Cast value to appropriate type
     */
    public static function castValue($value, string $type)
    {
        if ($value === null) {
            return null;
        }

        switch ($type) {
            case 'boolean':
                return filter_var($value, FILTER_VALIDATE_BOOLEAN);
            case 'integer':
                return (int) $value;
            case 'decimal':
            case 'float':
                return (float) $value;
            case 'json':
                return json_decode($value, true);
            case 'file':
                return $value ? Storage::url($value) : null;
            default:
                return $value;
        }
    }

    /**
     * Format currency amount using store settings
     */
    public static function formatCurrency(float $amount): string
    {
        $symbol = static::get('currency_symbol', '$');
        $position = static::get('currency_position', 'before');
        $decimalPlaces = static::get('decimal_places', 2);
        $thousandsSeparator = static::get('thousands_separator', ',');
        $decimalSeparator = static::get('decimal_separator', '.');

        $formattedAmount = number_format($amount, $decimalPlaces, $decimalSeparator, $thousandsSeparator);

        switch ($position) {
            case 'after':
                return $formattedAmount . $symbol;
            case 'before_space':
                return $symbol . ' ' . $formattedAmount;
            case 'after_space':
                return $formattedAmount . ' ' . $symbol;
            default: // 'before'
                return $symbol . $formattedAmount;
        }
    }

    /**
     * Get company information
     */
    public static function getCompanyInfo(): array
    {
        return [
            'name' => static::get('company_name', 'Your Company Name'),
            'logo' => static::get('company_logo'),
            'address' => static::get('company_address'),
            'city' => static::get('company_city'),
            'state' => static::get('company_state'),
            'postal_code' => static::get('company_postal_code'),
            'country' => static::get('company_country'),
            'phone' => static::get('company_phone'),
            'email' => static::get('company_email'),
            'website' => static::get('company_website'),
        ];
    }

    /**
     * Get currency settings
     */
    public static function getCurrencySettings(): array
    {
        return [
            'code' => static::get('currency_code', 'USD'),
            'symbol' => static::get('currency_symbol', '$'),
            'position' => static::get('currency_position', 'before'),
            'decimal_places' => static::get('decimal_places', 2),
            'thousands_separator' => static::get('thousands_separator', ','),
            'decimal_separator' => static::get('decimal_separator', '.'),
        ];
    }

    /**
     * Clear all settings cache
     */
    public static function clearCache(): void
    {
        $settings = static::all();
        foreach ($settings as $setting) {
            Cache::forget("store_setting_{$setting->key}");
        }
        Cache::forget('all_store_settings');
        Cache::forget('public_store_settings');
    }

    /**
     * Boot method to clear cache when settings are updated
     */
    protected static function boot()
    {
        parent::boot();

        static::saved(function () {
            static::clearCache();
        });

        static::deleted(function () {
            static::clearCache();
        });
    }

    /**
     * Get setting groups
     */
    public static function getGroups(): array
    {
        return [
            'company' => 'Company Information',
            'currency' => 'Currency Settings',
            'business' => 'Business Settings',
            'system' => 'System Settings'
        ];
    }
}