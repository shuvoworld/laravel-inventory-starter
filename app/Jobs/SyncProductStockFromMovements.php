<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Modules\Products\Models\Product;
use App\Modules\StockMovement\Models\StockMovement;
use Carbon\Carbon;

class SyncProductStockFromMovements implements ShouldQueue
{
    use Queueable;

    public $timeout = 300; // 5 minutes timeout
    public $tries = 3;
    public $backoff = [60, 300, 900]; // 1min, 5min, 15min

    /**
     * Create a new job instance.
     */
    public function __construct(
        public ?int $productId = null,
        public ?Carbon $fromDate = null,
        public bool $forceUpdate = false
    ) {
        $this->onQueue('stock-sync');
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $startTime = now();
        Log::info('Starting stock synchronization from movements', [
            'product_id' => $this->productId,
            'from_date' => $this->fromDate?->format('Y-m-d H:i:s'),
            'force_update' => $this->forceUpdate,
            'start_time' => $startTime->format('Y-m-d H:i:s')
        ]);

        try {
            if ($this->productId) {
                $this->syncSingleProduct($this->productId);
            } else {
                $this->syncAllProducts();
            }

            $duration = $startTime->diffInSeconds(now());
            Log::info('Stock synchronization completed successfully', [
                'duration_seconds' => $duration,
                'completed_at' => now()->format('Y-m-d H:i:s')
            ]);

        } catch (\Exception $e) {
            Log::error('Stock synchronization failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'product_id' => $this->productId
            ]);

            if ($this->attempts() >= $this->tries) {
                Log::critical('Stock synchronization permanently failed after all retries', [
                    'product_id' => $this->productId,
                    'attempts' => $this->attempts()
                ]);
            }

            throw $e;
        }
    }

    /**
     * Sync stock for all products
     */
    private function syncAllProducts(): void
    {
        $productsUpdated = 0;
        $productsWithDiscrepancies = 0;
        $totalStockIn = 0;
        $totalStockOut = 0;

        // Get all products that have stock movements
        $products = Product::whereHas('stockMovements')->get();

        Log::info('Processing stock synchronization for all products', [
            'total_products' => $products->count()
        ]);

        foreach ($products as $product) {
            $result = $this->calculateAndUpdateProductStock($product);

            if ($result['updated']) {
                $productsUpdated++;
            }

            if ($result['discrepancy']) {
                $productsWithDiscrepancies++;
                Log::warning('Stock discrepancy detected', [
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'system_stock' => $result['system_stock'],
                    'calculated_stock' => $result['calculated_stock'],
                    'difference' => $result['difference']
                ]);
            }

            $totalStockIn += $result['total_in'];
            $totalStockOut += $result['total_out'];
        }

        Log::info('Stock synchronization summary', [
            'products_updated' => $productsUpdated,
            'products_with_discrepancies' => $productsWithDiscrepancies,
            'total_stock_in' => $totalStockIn,
            'total_stock_out' => $totalStockOut,
            'net_movement' => $totalStockIn - $totalStockOut
        ]);
    }

    /**
     * Sync stock for a single product
     */
    private function syncSingleProduct(int $productId): void
    {
        $product = Product::findOrFail($productId);
        $result = $this->calculateAndUpdateProductStock($product);

        Log::info('Single product stock sync completed', [
            'product_id' => $product->id,
            'product_name' => $product->name,
            'updated' => $result['updated'],
            'system_stock' => $result['system_stock'],
            'calculated_stock' => $result['calculated_stock'],
            'difference' => $result['difference'],
            'total_in' => $result['total_in'],
            'total_out' => $result['total_out']
        ]);
    }

    /**
     * Calculate stock from movements and update product
     */
    private function calculateAndUpdateProductStock(Product $product): array
    {
        // Query stock movements for this product
        $query = StockMovement::where('product_id', $product->id);

        // Filter by date if specified
        if ($this->fromDate) {
            $query->where('created_at', '>=', $this->fromDate);
        }

        // Get aggregated movements
        $movements = $query->selectRaw("
            movement_type,
            SUM(CASE WHEN movement_type = 'in' THEN quantity ELSE 0 END) as total_in,
            SUM(CASE WHEN movement_type = 'out' THEN quantity ELSE 0 END) as total_out,
            COUNT(*) as movement_count
        ")->groupBy('movement_type')->get();

        // Calculate totals
        $totalIn = $movements->sum('total_in');
        $totalOut = $movements->sum('total_out');
        $calculatedStock = $totalIn - $totalOut;

        $systemStock = $product->quantity_on_hand;
        $difference = $systemStock - $calculatedStock;
        $hasDiscrepancy = $difference !== 0;

        // Update product stock if needed
        $updated = false;
        if ($hasDiscrepancy || $this->forceUpdate) {
            DB::transaction(function () use ($product, $calculatedStock, $difference) {
                $oldStock = $product->quantity_on_hand;

                $product->quantity_on_hand = $calculatedStock;
                $product->save();

                // Log the stock change
                Log::info('Product stock updated', [
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'old_stock' => $oldStock,
                    'new_stock' => $calculatedStock,
                    'difference' => $difference,
                    'sync_reason' => $this->forceUpdate ? 'forced_update' : 'discrepancy_correction'
                ]);
            });

            $updated = true;
        }

        return [
            'updated' => $updated,
            'discrepancy' => $hasDiscrepancy,
            'system_stock' => $systemStock,
            'calculated_stock' => $calculatedStock,
            'difference' => $difference,
            'total_in' => $totalIn,
            'total_out' => $totalOut,
            'movement_count' => $movements->sum('movement_count')
        ];
    }

    /**
     * Get job execution details
     */
    public function getJobDetails(): array
    {
        return [
            'product_id' => $this->productId,
            'from_date' => $this->fromDate,
            'force_update' => $this->forceUpdate,
            'queue' => 'stock-sync',
            'timeout' => $this->timeout,
            'tries' => $this->tries
        ];
    }
}
