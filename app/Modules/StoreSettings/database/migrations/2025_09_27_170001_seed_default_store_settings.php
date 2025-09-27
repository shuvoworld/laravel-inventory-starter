<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $defaultSettings = [
            // Company Information
            [
                'key' => 'company_name',
                'value' => 'Your Company Name',
                'type' => 'string',
                'group' => 'company',
                'label' => 'Company Name',
                'description' => 'The legal name of your business',
                'is_public' => true,
                'sort_order' => 1
            ],
            [
                'key' => 'company_logo',
                'value' => null,
                'type' => 'file',
                'group' => 'company',
                'label' => 'Company Logo',
                'description' => 'Upload your company logo',
                'options' => json_encode(['accept' => 'image/*']),
                'is_public' => true,
                'sort_order' => 2
            ],
            [
                'key' => 'company_address',
                'value' => '123 Business Street',
                'type' => 'textarea',
                'group' => 'company',
                'label' => 'Company Address',
                'description' => 'Full business address',
                'is_public' => true,
                'sort_order' => 3
            ],
            [
                'key' => 'company_city',
                'value' => 'Business City',
                'type' => 'string',
                'group' => 'company',
                'label' => 'City',
                'description' => 'City where business is located',
                'is_public' => true,
                'sort_order' => 4
            ],
            [
                'key' => 'company_state',
                'value' => 'State',
                'type' => 'string',
                'group' => 'company',
                'label' => 'State/Province',
                'description' => 'State or province',
                'is_public' => true,
                'sort_order' => 5
            ],
            [
                'key' => 'company_postal_code',
                'value' => '12345',
                'type' => 'string',
                'group' => 'company',
                'label' => 'Postal Code',
                'description' => 'ZIP or postal code',
                'is_public' => true,
                'sort_order' => 6
            ],
            [
                'key' => 'company_country',
                'value' => 'United States',
                'type' => 'string',
                'group' => 'company',
                'label' => 'Country',
                'description' => 'Country where business operates',
                'is_public' => true,
                'sort_order' => 7
            ],
            [
                'key' => 'company_phone',
                'value' => '(555) 123-4567',
                'type' => 'string',
                'group' => 'company',
                'label' => 'Phone Number',
                'description' => 'Primary business phone number',
                'is_public' => true,
                'sort_order' => 8
            ],
            [
                'key' => 'company_email',
                'value' => 'contact@company.com',
                'type' => 'email',
                'group' => 'company',
                'label' => 'Email Address',
                'description' => 'Primary business email address',
                'is_public' => true,
                'sort_order' => 9
            ],
            [
                'key' => 'company_website',
                'value' => 'https://company.com',
                'type' => 'url',
                'group' => 'company',
                'label' => 'Website',
                'description' => 'Company website URL',
                'is_public' => true,
                'sort_order' => 10
            ],

            // Currency Settings
            [
                'key' => 'currency_code',
                'value' => 'USD',
                'type' => 'select',
                'group' => 'currency',
                'label' => 'Currency Code',
                'description' => 'ISO currency code (e.g., USD, EUR, GBP)',
                'options' => json_encode([
                    'options' => [
                        'USD' => 'US Dollar (USD)',
                        'EUR' => 'Euro (EUR)',
                        'GBP' => 'British Pound (GBP)',
                        'CAD' => 'Canadian Dollar (CAD)',
                        'AUD' => 'Australian Dollar (AUD)',
                        'JPY' => 'Japanese Yen (JPY)',
                        'CNY' => 'Chinese Yuan (CNY)',
                        'INR' => 'Indian Rupee (INR)',
                        'BDT' => 'Bangladeshi Taka (BDT)'
                    ]
                ]),
                'is_public' => true,
                'sort_order' => 1
            ],
            [
                'key' => 'currency_symbol',
                'value' => '$',
                'type' => 'string',
                'group' => 'currency',
                'label' => 'Currency Symbol',
                'description' => 'Currency symbol to display (e.g., $, €, £)',
                'is_public' => true,
                'sort_order' => 2
            ],
            [
                'key' => 'currency_position',
                'value' => 'before',
                'type' => 'select',
                'group' => 'currency',
                'label' => 'Symbol Position',
                'description' => 'Where to place the currency symbol',
                'options' => json_encode([
                    'options' => [
                        'before' => 'Before amount ($100)',
                        'after' => 'After amount (100$)',
                        'before_space' => 'Before with space ($ 100)',
                        'after_space' => 'After with space (100 $)'
                    ]
                ]),
                'is_public' => true,
                'sort_order' => 3
            ],
            [
                'key' => 'decimal_places',
                'value' => '2',
                'type' => 'integer',
                'group' => 'currency',
                'label' => 'Decimal Places',
                'description' => 'Number of decimal places for currency display',
                'options' => json_encode(['min' => 0, 'max' => 4]),
                'is_public' => true,
                'sort_order' => 4
            ],
            [
                'key' => 'thousands_separator',
                'value' => ',',
                'type' => 'string',
                'group' => 'currency',
                'label' => 'Thousands Separator',
                'description' => 'Character to separate thousands (e.g., comma, space)',
                'is_public' => true,
                'sort_order' => 5
            ],
            [
                'key' => 'decimal_separator',
                'value' => '.',
                'type' => 'string',
                'group' => 'currency',
                'label' => 'Decimal Separator',
                'description' => 'Character to separate decimals (e.g., period, comma)',
                'is_public' => true,
                'sort_order' => 6
            ],

            // Business Settings
            [
                'key' => 'business_hours',
                'value' => 'Monday - Friday: 9:00 AM - 6:00 PM',
                'type' => 'textarea',
                'group' => 'business',
                'label' => 'Business Hours',
                'description' => 'Operating hours for customer reference',
                'is_public' => true,
                'sort_order' => 1
            ],
            [
                'key' => 'tax_rate',
                'value' => '8.5',
                'type' => 'decimal',
                'group' => 'business',
                'label' => 'Default Tax Rate (%)',
                'description' => 'Default tax rate as percentage',
                'options' => json_encode(['min' => 0, 'max' => 100, 'step' => 0.01]),
                'is_public' => false,
                'sort_order' => 2
            ],
            [
                'key' => 'invoice_terms',
                'value' => 'Payment due within 30 days',
                'type' => 'textarea',
                'group' => 'business',
                'label' => 'Invoice Terms',
                'description' => 'Default terms and conditions for invoices',
                'is_public' => false,
                'sort_order' => 3
            ],

            // System Settings
            [
                'key' => 'timezone',
                'value' => 'America/New_York',
                'type' => 'select',
                'group' => 'system',
                'label' => 'Timezone',
                'description' => 'Default timezone for the application',
                'options' => json_encode([
                    'options' => [
                        'America/New_York' => 'Eastern Time (UTC-5)',
                        'America/Chicago' => 'Central Time (UTC-6)',
                        'America/Denver' => 'Mountain Time (UTC-7)',
                        'America/Los_Angeles' => 'Pacific Time (UTC-8)',
                        'Europe/London' => 'GMT (UTC+0)',
                        'Europe/Paris' => 'CET (UTC+1)',
                        'Asia/Tokyo' => 'JST (UTC+9)',
                        'Asia/Shanghai' => 'CST (UTC+8)',
                        'Australia/Sydney' => 'AEST (UTC+10)'
                    ]
                ]),
                'is_public' => false,
                'sort_order' => 1
            ],
            [
                'key' => 'date_format',
                'value' => 'M j, Y',
                'type' => 'select',
                'group' => 'system',
                'label' => 'Date Format',
                'description' => 'How dates should be displayed',
                'options' => json_encode([
                    'options' => [
                        'M j, Y' => 'Jan 15, 2024',
                        'Y-m-d' => '2024-01-15',
                        'd/m/Y' => '15/01/2024',
                        'm/d/Y' => '01/15/2024',
                        'F j, Y' => 'January 15, 2024'
                    ]
                ]),
                'is_public' => true,
                'sort_order' => 2
            ],
            [
                'key' => 'time_format',
                'value' => 'g:i A',
                'type' => 'select',
                'group' => 'system',
                'label' => 'Time Format',
                'description' => 'How times should be displayed',
                'options' => json_encode([
                    'options' => [
                        'g:i A' => '3:30 PM',
                        'H:i' => '15:30',
                        'g:i:s A' => '3:30:45 PM',
                        'H:i:s' => '15:30:45'
                    ]
                ]),
                'is_public' => true,
                'sort_order' => 3
            ]
        ];

        foreach ($defaultSettings as $setting) {
            DB::table('store_settings')->insert(array_merge($setting, [
                'created_at' => now(),
                'updated_at' => now()
            ]));
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('store_settings')->truncate();
    }
};