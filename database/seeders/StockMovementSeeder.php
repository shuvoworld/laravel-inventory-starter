<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Modules\StockMovement\Models\StockMovement;
use Illuminate\Support\Facades\DB;

class StockMovementSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Updating existing stock movements to maintain data consistency...');

        // Get all existing stock movements that don't have movement_type set
        $stockMovements = StockMovement::whereNull('movement_type')->get();

        foreach ($stockMovements as $movement) {
            // Map old 'type' field to new 'movement_type'
            $oldType = $movement->attributes['type'] ?? 'adjustment';
            $movement->movement_type = $oldType;

            // Set transaction_type based on reference_type
            switch ($movement->reference_type) {
                case 'sales_order':
                    $movement->transaction_type = 'sale';
                    break;
                case 'purchase_order':
                    $movement->transaction_type = 'purchase';
                    break;
                case 'purchase_order_adjustment':
                    $movement->transaction_type = 'purchase_return';
                    break;
                case 'sales_order_adjustment':
                    $movement->transaction_type = 'sale_return';
                    break;
                case 'stock_adjustment':
                    $movement->transaction_type = 'manual_adjustment';
                    break;
                default:
                    $movement->transaction_type = 'manual_adjustment';
                    break;
            }

            // Set user_id if null (assign to admin user with ID 1 if exists)
            if (!$movement->user_id) {
                $movement->user_id = 1;
            }

            $movement->save();
        }

        $this->command->info('Updated ' . $stockMovements->count() . ' stock movements successfully.');
    }
}
