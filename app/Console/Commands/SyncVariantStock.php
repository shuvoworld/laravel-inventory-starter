<?php

namespace App\Console\Commands;

use App\Modules\StockMovement\Models\StockMovement;
use Illuminate\Console\Command;

class SyncVariantStock extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'stock:sync-variants {--variant-id= : Sync specific variant ID} {--force : Force update even if no discrepancy}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync variant stock quantities based on stock movements';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting variant stock synchronization...');

        if ($variantId = $this->option('variant-id')) {
            // Sync specific variant
            $this->info("Syncing variant ID: {$variantId}");

            try {
                $result = StockMovement::syncVariantStock($variantId);

                $this->table(
                    ['Metric', 'Value'],
                    [
                        ['Variant ID', $variantId],
                        ['Updated', $result['updated'] ? 'Yes' : 'No'],
                        ['Old Stock', $result['old_stock']],
                        ['New Stock', $result['new_stock']],
                        ['Difference', $result['difference']],
                        ['Total In', $result['total_in']],
                        ['Total Out', $result['total_out']],
                    ]
                );

                if ($result['updated']) {
                    $this->info("✅ Variant stock updated successfully!");
                } else {
                    $this->info("✅ Variant stock is already correct.");
                }

            } catch (\Exception $e) {
                $this->error("❌ Failed to sync variant: {$e->getMessage()}");
                return 1;
            }

        } else {
            // Sync all variants
            $this->info('Syncing all variants with stock movements...');

            $result = null;
            $this->withProgressBar(1, function () use (&$result) {
                $result = StockMovement::syncAllVariantStocks();
            });

            $this->newLine();
            $this->table(
                ['Metric', 'Value'],
                [
                    ['Total Variants Processed', $result['variants_with_discrepancies'] + $result['variants_updated']],
                    ['Variants Updated', $result['variants_updated']],
                    ['Variants with Discrepancies', $result['variants_with_discrepancies']],
                    ['Total Stock In', $result['total_stock_in']],
                    ['Total Stock Out', $result['total_stock_out']],
                    ['Net Movement', $result['net_movement']],
                ]
            );

            if ($result['variants_updated'] > 0) {
                $this->info("✅ Successfully updated {$result['variants_updated']} variants!");
            } else {
                $this->info("✅ All variant stocks are already correct.");
            }
        }

        $this->info('Variant stock synchronization completed!');
        return 0;
    }
}
