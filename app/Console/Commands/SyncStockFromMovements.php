<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Jobs\SyncProductStockFromMovements;
use App\Modules\Products\Models\Product;
use App\Modules\StockMovement\Models\StockMovement;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class SyncStockFromMovements extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'stock:sync-from-movements
                            {--product-id= : Sync specific product ID}
                            {--from-date= : Sync movements from specific date (Y-m-d H:i:s)}
                            {--force : Force update all products even if no discrepancies}
                            {--queue : Dispatch job to queue instead of running synchronously}
                            {--dry-run : Show what would be updated without making changes}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Synchronize product stock quantities from stock movements table';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔄 Starting stock synchronization from movements...');

        // Parse options
        $productId = $this->option('product-id') ? (int) $this->option('product-id') : null;
        $fromDate = $this->option('from-date') ? Carbon::parse($this->option('from-date')) : null;
        $forceUpdate = $this->option('force');
        $useQueue = $this->option('queue');
        $dryRun = $this->option('dry-run');

        $this->displayOptions($productId, $fromDate, $forceUpdate, $useQueue, $dryRun);

        if ($dryRun) {
            return $this->performDryRun($productId, $fromDate);
        }

        if ($useQueue) {
            return $this->dispatchToQueue($productId, $fromDate, $forceUpdate);
        }

        return $this->executeSynchronously($productId, $fromDate, $forceUpdate);
    }

    /**
     * Display command options
     */
    private function displayOptions(?int $productId, ?Carbon $fromDate, bool $forceUpdate, bool $useQueue, bool $dryRun): void
    {
        $this->line('');
        $this->info('📋 Configuration:');
        $this->line('  Product ID: ' . ($productId ? "#{$productId}" : 'All products'));
        $this->line('  From Date: ' . ($fromDate ? $fromDate->format('Y-m-d H:i:s') : 'Beginning of time'));
        $this->line('  Force Update: ' . ($forceUpdate ? 'Yes' : 'No'));
        $this->line('  Use Queue: ' . ($useQueue ? 'Yes' : 'No'));
        $this->line('  Dry Run: ' . ($dryRun ? 'Yes' : 'No'));
        $this->line('');
    }

    /**
     * Perform dry run to show what would be updated
     */
    private function performDryRun(?int $productId, ?Carbon $fromDate): int
    {
        $this->info('🔍 Performing dry run analysis...');

        $query = Product::whereHas('stockMovements');
        if ($productId) {
            $query->where('id', $productId);
        }

        $products = $query->withCount('stockMovements')->get();
        $discrepanciesFound = 0;

        $this->info("Found {$products->count()} products with stock movements");

        $this->table(
            ['Product ID', 'Product Name', 'System Stock', 'Calculated Stock', 'Difference', 'Movements'],
            $products->map(function ($product) use ($fromDate, &$discrepanciesFound) {
                $calculatedStock = StockMovement::where('product_id', $product->id)
                    ->when($fromDate, fn($q) => $q->where('created_at', '>=', $fromDate))
                    ->selectRaw("
                        SUM(CASE WHEN movement_type = 'in' THEN quantity ELSE 0 END) as total_in,
                        SUM(CASE WHEN movement_type = 'out' THEN quantity ELSE 0 END) as total_out
                    ")
                    ->first();

                $totalIn = $calculatedStock->total_in ?? 0;
                $totalOut = $calculatedStock->total_out ?? 0;
                $calculated = $totalIn - $totalOut;
                $difference = $product->quantity_on_hand - $calculated;

                if ($difference !== 0) {
                    $discrepanciesFound++;
                }

                return [
                    $product->id,
                    substr($product->name, 0, 30),
                    $product->quantity_on_hand,
                    $calculated,
                    $difference === 0 ? '✅' : ($difference > 0 ? "+{$difference}" : $difference),
                    $product->stock_movements_count
                ];
            })
        );

        $this->info('');
        $this->warn("Products with discrepancies: {$discrepanciesFound}");
        $this->info('💡 Use --force flag to update all products, or let the system update only discrepancies automatically.');

        return $discrepanciesFound > 0 ? 1 : 0;
    }

    /**
     * Dispatch job to queue
     */
    private function dispatchToQueue(?int $productId, ?Carbon $fromDate, bool $forceUpdate): int
    {
        $this->info('📤 Dispatching stock synchronization job to queue...');

        $job = new SyncProductStockFromMovements($productId, $fromDate, $forceUpdate);
        dispatch($job);

        $this->info('✅ Job dispatched successfully!');
        $this->info('📊 Monitor job progress with: php artisan queue:monitor stock-sync');
        $this->info('📋 Check logs for: php artisan log:show --tag=stock-sync');

        return 0;
    }

    /**
     * Execute synchronization synchronously
     */
    private function executeSynchronously(?int $productId, ?Carbon $fromDate, bool $forceUpdate): int
    {
        $this->info('⚡ Executing stock synchronization synchronously...');
        $startTime = now();

        try {
            $job = new SyncProductStockFromMovements($productId, $fromDate, $forceUpdate);
            $job->handle();

            $duration = $startTime->diffInSeconds(now());
            $this->info("✅ Stock synchronization completed in {$duration} seconds");

            return 0;

        } catch (\Exception $e) {
            $this->error('❌ Stock synchronization failed: ' . $e->getMessage());
            $this->error('📋 Check logs for more details: php artisan log:show');
            return 1;
        }
    }

    /**
     * Display stock synchronization statistics
     */
    public function showStatistics()
    {
        $this->info('📊 Stock Movement Statistics');

        $totalProducts = Product::count();
        $ProductsWithMovements = Product::whereHas('stockMovements')->count();
        $totalMovements = StockMovement::count();

        // Get recent movements
        $recentMovements = StockMovement::where('created_at', '>=', now()->subHours(24))->count();
        $lastSyncLog = $this->getLastSyncLog();

        $this->table(
            ['Metric', 'Value'],
            [
                ['Total Products', $totalProducts],
                ['Products with Movements', $ProductsWithMovements],
                ['Total Stock Movements', $totalMovements],
                ['Movements (24h)', $recentMovements],
                ['Last Sync', $lastSyncLog]
            ]
        );

        // Show distribution by movement type
        $this->info('');
        $this->info('📈 Movement Type Distribution (Last 7 Days):');

        $movementStats = StockMovement::where('created_at', '>=', now()->subDays(7))
            ->selectRaw('movement_type, COUNT(*) as count, SUM(quantity) as total_quantity')
            ->groupBy('movement_type')
            ->get();

        $this->table(
            ['Movement Type', 'Count', 'Total Quantity'],
            $movementStats->map(function ($stat) {
                return [
                    $stat->movement_type,
                    $stat->count,
                    $stat->total_quantity
                ];
            })
        );
    }

    /**
     * Get last sync log entry
     */
    private function getLastSyncLog(): string
    {
        try {
            $logFile = storage_path('logs/laravel.log');
            if (!file_exists($logFile)) {
                return 'No logs found';
            }

            $content = file_get_contents($logFile);
            $lines = explode("\n", $content);
            $lines = array_reverse($lines);

            foreach ($lines as $line) {
                if (strpos($line, 'Stock synchronization completed successfully') !== false) {
                    return '✅ Recent success';
                }
            }

            return '❓ No recent sync found';
        } catch (\Exception $e) {
            return '❌ Error reading logs';
        }
    }
}
