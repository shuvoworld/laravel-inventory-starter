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
        // Update the currency_code setting to include BDT
        $setting = DB::table('store_settings')->where('key', 'currency_code')->first();

        if ($setting) {
            $options = json_decode($setting->options, true);
            $options['options']['BDT'] = 'Bangladeshi Taka (BDT)';

            DB::table('store_settings')
                ->where('key', 'currency_code')
                ->update([
                    'options' => json_encode($options),
                    'updated_at' => now()
                ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove BDT from currency options
        $setting = DB::table('store_settings')->where('key', 'currency_code')->first();

        if ($setting) {
            $options = json_decode($setting->options, true);
            unset($options['options']['BDT']);

            DB::table('store_settings')
                ->where('key', 'currency_code')
                ->update([
                    'options' => json_encode($options),
                    'updated_at' => now()
                ]);
        }
    }
};