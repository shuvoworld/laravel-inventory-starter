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
        // Update timezone setting to have all timezones
        $timezoneSetting = DB::table('store_settings')->where('key', 'timezone')->first();
        if ($timezoneSetting) {
            $timezones = [];
            foreach (DateTimeZone::listIdentifiers() as $timezone) {
                try {
                    $dt = new DateTime('now', new DateTimeZone($timezone));
                    $offset = $dt->format('P');
                    $timezones[$timezone] = str_replace('_', ' ', $timezone) . ' (UTC' . $offset . ')';
                } catch (Exception $e) {
                    $timezones[$timezone] = $timezone;
                }
            }

            DB::table('store_settings')
                ->where('key', 'timezone')
                ->update([
                    'type' => 'select',
                    'options' => json_encode(['options' => $timezones]),
                    'updated_at' => now()
                ]);
        }

        // Update company_country setting to have select type with country codes
        $countrySetting = DB::table('store_settings')->where('key', 'company_country')->first();
        if ($countrySetting) {
            // Map old country name to new country code if needed
            $currentValue = $countrySetting->value;
            $countryCode = 'US'; // Default to US

            // Map common country names to codes
            $countryMap = [
                'United States' => 'US',
                'United Kingdom' => 'GB',
                'Canada' => 'CA',
                'Australia' => 'AU',
                'Germany' => 'DE',
                'France' => 'FR',
                'Italy' => 'IT',
                'Spain' => 'ES',
                'Bangladesh' => 'BD',
            ];

            if (isset($countryMap[$currentValue])) {
                $countryCode = $countryMap[$currentValue];
            }

            $countries = [
                'US' => 'United States',
                'GB' => 'United Kingdom',
                'CA' => 'Canada',
                'AU' => 'Australia',
                'DE' => 'Germany',
                'FR' => 'France',
                'IT' => 'Italy',
                'ES' => 'Spain',
                'NL' => 'Netherlands',
                'BE' => 'Belgium',
                'CH' => 'Switzerland',
                'AT' => 'Austria',
                'SE' => 'Sweden',
                'NO' => 'Norway',
                'DK' => 'Denmark',
                'FI' => 'Finland',
                'IE' => 'Ireland',
                'PT' => 'Portugal',
                'GR' => 'Greece',
                'PL' => 'Poland',
                'CZ' => 'Czech Republic',
                'HU' => 'Hungary',
                'RO' => 'Romania',
                'BG' => 'Bulgaria',
                'HR' => 'Croatia',
                'SK' => 'Slovakia',
                'SI' => 'Slovenia',
                'LT' => 'Lithuania',
                'LV' => 'Latvia',
                'EE' => 'Estonia',
                'JP' => 'Japan',
                'CN' => 'China',
                'IN' => 'India',
                'KR' => 'South Korea',
                'SG' => 'Singapore',
                'MY' => 'Malaysia',
                'TH' => 'Thailand',
                'ID' => 'Indonesia',
                'PH' => 'Philippines',
                'VN' => 'Vietnam',
                'BD' => 'Bangladesh',
                'PK' => 'Pakistan',
                'NZ' => 'New Zealand',
                'MX' => 'Mexico',
                'BR' => 'Brazil',
                'AR' => 'Argentina',
                'CL' => 'Chile',
                'CO' => 'Colombia',
                'PE' => 'Peru',
                'VE' => 'Venezuela',
                'ZA' => 'South Africa',
                'NG' => 'Nigeria',
                'EG' => 'Egypt',
                'KE' => 'Kenya',
                'MA' => 'Morocco',
                'DZ' => 'Algeria',
                'TN' => 'Tunisia',
                'ET' => 'Ethiopia',
                'GH' => 'Ghana',
                'UG' => 'Uganda',
                'TZ' => 'Tanzania',
                'RU' => 'Russia',
                'UA' => 'Ukraine',
                'TR' => 'Turkey',
                'IL' => 'Israel',
                'SA' => 'Saudi Arabia',
                'AE' => 'United Arab Emirates',
                'QA' => 'Qatar',
                'KW' => 'Kuwait',
                'OM' => 'Oman',
                'BH' => 'Bahrain',
                'JO' => 'Jordan',
                'LB' => 'Lebanon',
                'IQ' => 'Iraq',
                'IR' => 'Iran',
                'AF' => 'Afghanistan',
                'PG' => 'Papua New Guinea',
                'FJ' => 'Fiji',
                'NC' => 'New Caledonia',
                'PF' => 'French Polynesia',
                'IS' => 'Iceland',
                'LU' => 'Luxembourg',
                'MT' => 'Malta',
                'CY' => 'Cyprus',
                'MC' => 'Monaco',
                'LI' => 'Liechtenstein',
                'AD' => 'Andorra',
                'SM' => 'San Marino',
                'VA' => 'Vatican City',
                'GI' => 'Gibraltar',
                'IM' => 'Isle of Man',
                'JE' => 'Jersey',
                'GG' => 'Guernsey',
                'FO' => 'Faroe Islands',
                'GL' => 'Greenland',
                'AX' => 'Aland Islands',
                'SJ' => 'Svalbard and Jan Mayen',
                'BV' => 'Bouvet Island',
                'HM' => 'Heard Island and McDonald Islands'
            ];

            DB::table('store_settings')
                ->where('key', 'company_country')
                ->update([
                    'value' => $countryCode,
                    'type' => 'select',
                    'options' => json_encode(['options' => $countries]),
                    'updated_at' => now()
                ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert timezone setting to limited options
        DB::table('store_settings')
            ->where('key', 'timezone')
            ->update([
                'type' => 'select',
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
                'updated_at' => now()
            ]);

        // Revert country setting to string type
        $countrySetting = DB::table('store_settings')->where('key', 'company_country')->first();
        if ($countrySetting) {
            $countryCode = $countrySetting->value;

            $countries = [
                'US' => 'United States',
                'GB' => 'United Kingdom',
                'CA' => 'Canada',
                'AU' => 'Australia',
                'DE' => 'Germany',
                'FR' => 'France',
                'IT' => 'Italy',
                'ES' => 'Spain',
                'BD' => 'Bangladesh',
            ];

            $countryName = $countries[$countryCode] ?? 'United States';

            DB::table('store_settings')
                ->where('key', 'company_country')
                ->update([
                    'value' => $countryName,
                    'type' => 'string',
                    'options' => null,
                    'updated_at' => now()
                ]);
        }
    }
};
