<?php

namespace App\Services;

use App\Modules\StockMovement\Models\StockMovement;
use App\Modules\Products\Models\Product;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class StockMovementService
{
    /**
     * Record stock movement for any transaction type
     */
    public static function recordMovement(array $data): StockMovement
    {
        $movementData = array_merge([
            'user_id' => Auth::id(),
            'store_id' => auth()->user()->store_id ?? null,
        ], $data);

        return DB::transaction(function () use ($movementData) {
            return StockMovement::create($movementData);
        });
    }

    /**
     * Record stock movement for purchase order (IN - increases inventory)
     */
    public static function recordPurchase(int $productId, int $quantity, $referenceId = null, string $notes = ''): StockMovement
    {
        return self::recordMovement([
            'product_id' => $productId,
            'movement_type' => 'in',
            'transaction_type' => 'purchase',
            'quantity' => $quantity,
            'reference_type' => 'purchase_order',
            'reference_id' => $referenceId,
            'notes' => $notes ?: 'Stock IN from purchase order',
        ]);
    }

    /**
     * Record stock movement for purchase return (OUT - decreases inventory)
     */
    public static function recordPurchaseReturn(int $productId, int $quantity, $referenceId = null, string $notes = ''): StockMovement
    {
        return self::recordMovement([
            'product_id' => $productId,
            'movement_type' => 'out',
            'transaction_type' => 'purchase_return',
            'quantity' => $quantity,
            'reference_type' => 'purchase_return',
            'reference_id' => $referenceId,
            'notes' => $notes ?: 'Stock OUT returned to supplier',
        ]);
    }

    /**
     * Record stock movement for sales order (OUT - decreases inventory)
     */
    public static function recordSale(int $productId, int $quantity, $referenceId = null, string $notes = ''): StockMovement
    {
        return self::recordMovement([
            'product_id' => $productId,
            'movement_type' => 'out',
            'transaction_type' => 'sale',
            'quantity' => $quantity,
            'reference_type' => 'sales_order',
            'reference_id' => $referenceId,
            'notes' => $notes ?: 'Stock OUT sold to customer',
        ]);
    }

    /**
     * Record stock movement for sales return (IN - increases inventory)
     */
    public static function recordSaleReturn(int $productId, int $quantity, $referenceId = null, string $notes = ''): StockMovement
    {
        return self::recordMovement([
            'product_id' => $productId,
            'movement_type' => 'in',
            'transaction_type' => 'sale_return',
            'quantity' => $quantity,
            'reference_type' => 'sales_return',
            'reference_id' => $referenceId,
            'notes' => $notes ?: 'Stock IN returned by customer',
        ]);
    }

    /**
     * Record manual stock adjustment
     */
    public static function recordAdjustment(int $productId, string $movementType, int $quantity, string $notes = ''): StockMovement
    {
        return self::recordMovement([
            'product_id' => $productId,
            'movement_type' => $movementType,
            'transaction_type' => 'manual_adjustment',
            'quantity' => $quantity,
            'reference_type' => 'stock_adjustment',
            'reference_id' => null,
            'notes' => $notes ?: 'Manual stock adjustment',
        ]);
    }

    /**
     * Record opening stock
     */
    public static function recordOpeningStock(int $productId, int $quantity, string $notes = ''): StockMovement
    {
        return self::recordMovement([
            'product_id' => $productId,
            'movement_type' => 'in',
            'transaction_type' => 'opening_stock',
            'quantity' => $quantity,
            'reference_type' => 'opening_stock',
            'reference_id' => null,
            'notes' => $notes ?: 'Opening stock initialization',
        ]);
    }

    
    /**
     * Record stock theft
     */
    public static function recordTheft(int $productId, int $quantity, string $notes = ''): StockMovement
    {
        return self::recordMovement([
            'product_id' => $productId,
            'movement_type' => 'out',
            'transaction_type' => 'theft',
            'quantity' => $quantity,
            'reference_type' => 'theft',
            'reference_id' => null,
            'notes' => $notes ?: 'Stock stolen',
        ]);
    }

    /**
     * Get stock movements for a product
     */
    public static function getProductMovements(int $productId, int $limit = 50): \Illuminate\Database\Eloquent\Collection
    {
        return StockMovement::with(['product', 'user'])
            ->where('product_id', $productId)
            ->latest()
            ->limit($limit)
            ->get();
    }

    /**
     * Get current stock balance for a product
     */
    public static function getCurrentStock(int $productId): int
    {
        $product = Product::findOrFail($productId);
        return $product->quantity_on_hand;
    }

    /**
     * Validate stock availability before recording outbound movement
     */
    public static function validateStockAvailability(int $productId, int $quantity): bool
    {
        $currentStock = self::getCurrentStock($productId);
        return $currentStock >= $quantity;
    }

    /**
     * Get movement summary for reporting
     */
    public static function getMovementSummary(?\DateTime $startDate = null, ?\DateTime $endDate = null): array
    {
        $query = StockMovement::query();

        if ($startDate) {
            $query->whereDate('created_at', '>=', $startDate);
        }

        if ($endDate) {
            $query->whereDate('created_at', '<=', $endDate);
        }

        $movements = $query->get();

        return [
            'total_in' => $movements->where('movement_type', 'in')->sum('quantity'),
            'total_out' => $movements->where('movement_type', 'out')->sum('quantity'),
            'total_adjustments' => $movements->where('movement_type', 'adjustment')->count(),
            'transactions_by_type' => $movements->groupBy('transaction_type')->map->count(),
            'most_active_products' => $movements->groupBy('product_id')->map->count()->sortDesc()->take(10),
        ];
    }

    // IN MOVEMENT METHODS (Stock Increases)

    /**
     * Record transfer IN from another location
     */
    public static function recordTransferIn(int $productId, int $quantity, $referenceId = null, string $notes = ''): StockMovement
    {
        return self::recordMovement([
            'product_id' => $productId,
            'movement_type' => 'in',
            'transaction_type' => 'transfer_in',
            'quantity' => $quantity,
            'reference_type' => 'stock_transfer',
            'reference_id' => $referenceId,
            'notes' => $notes ?: 'Stock received from transfer',
        ]);
    }

    /**
     * Record stock count correction (positive adjustment)
     */
    public static function recordStockCountCorrectionPlus(int $productId, int $quantity, string $notes = ''): StockMovement
    {
        return self::recordMovement([
            'product_id' => $productId,
            'movement_type' => 'in',
            'transaction_type' => 'stock_count_correction',
            'quantity' => $quantity,
            'reference_type' => 'stock_count',
            'reference_id' => null,
            'notes' => $notes ?: 'Stock count correction (+)',
        ]);
    }

    /**
     * Record recovered/found items
     */
    public static function recordRecovery(int $productId, int $quantity, string $notes = ''): StockMovement
    {
        return self::recordMovement([
            'product_id' => $productId,
            'movement_type' => 'in',
            'transaction_type' => 'recovery_found',
            'quantity' => $quantity,
            'reference_type' => 'recovery',
            'reference_id' => null,
            'notes' => $notes ?: 'Item recovered/found',
        ]);
    }

    /**
     * Record manufacturing input
     */
    public static function recordManufacturingIn(int $productId, int $quantity, $referenceId = null, string $notes = ''): StockMovement
    {
        return self::recordMovement([
            'product_id' => $productId,
            'movement_type' => 'in',
            'transaction_type' => 'manufacturing_in',
            'quantity' => $quantity,
            'reference_type' => 'manufacturing',
            'reference_id' => $referenceId,
            'notes' => $notes ?: 'Manufacturing completion',
        ]);
    }

    // OUT MOVEMENT METHODS (Stock Decreases)

    /**
     * Record damage to items
     */
    public static function recordDamage(int $productId, int $quantity, string $notes = ''): StockMovement
    {
        return self::recordMovement([
            'product_id' => $productId,
            'movement_type' => 'out',
            'transaction_type' => 'damage',
            'quantity' => $quantity,
            'reference_type' => 'damage',
            'reference_id' => null,
            'notes' => $notes ?: 'Stock damaged',
        ]);
    }

    /**
     * Record lost/missing items
     */
    public static function recordLost(int $productId, int $quantity, string $notes = ''): StockMovement
    {
        return self::recordMovement([
            'product_id' => $productId,
            'movement_type' => 'out',
            'transaction_type' => 'lost_missing',
            'quantity' => $quantity,
            'reference_type' => 'lost',
            'reference_id' => null,
            'notes' => $notes ?: 'Stock lost or missing',
        ]);
    }

    /**
     * Record expired items
     */
    public static function recordExpired(int $productId, int $quantity, string $notes = ''): StockMovement
    {
        return self::recordMovement([
            'product_id' => $productId,
            'movement_type' => 'out',
            'transaction_type' => 'expired',
            'quantity' => $quantity,
            'reference_type' => 'expiry',
            'reference_id' => null,
            'notes' => $notes ?: 'Stock expired',
        ]);
    }

    /**
     * Record transfer OUT to another location
     */
    public static function recordTransferOut(int $productId, int $quantity, $referenceId = null, string $notes = ''): StockMovement
    {
        return self::recordMovement([
            'product_id' => $productId,
            'movement_type' => 'out',
            'transaction_type' => 'transfer_out',
            'quantity' => $quantity,
            'reference_type' => 'stock_transfer',
            'reference_id' => $referenceId,
            'notes' => $notes ?: 'Stock transferred out',
        ]);
    }

    /**
     * Record stock count correction (negative adjustment)
     */
    public static function recordStockCountCorrectionMinus(int $productId, int $quantity, string $notes = ''): StockMovement
    {
        return self::recordMovement([
            'product_id' => $productId,
            'movement_type' => 'out',
            'transaction_type' => 'stock_count_correction_minus',
            'quantity' => $quantity,
            'reference_type' => 'stock_count',
            'reference_id' => null,
            'notes' => $notes ?: 'Stock count correction (-)',
        ]);
    }

    /**
     * Record quality control rejection
     */
    public static function recordQualityControl(int $productId, int $quantity, string $notes = ''): StockMovement
    {
        return self::recordMovement([
            'product_id' => $productId,
            'movement_type' => 'out',
            'transaction_type' => 'quality_control',
            'quantity' => $quantity,
            'reference_type' => 'quality_control',
            'reference_id' => null,
            'notes' => $notes ?: 'Failed quality control',
        ]);
    }

    /**
     * Record manufacturing consumption
     */
    public static function recordManufacturingOut(int $productId, int $quantity, $referenceId = null, string $notes = ''): StockMovement
    {
        return self::recordMovement([
            'product_id' => $productId,
            'movement_type' => 'out',
            'transaction_type' => 'manufacturing_out',
            'quantity' => $quantity,
            'reference_type' => 'manufacturing',
            'reference_id' => $referenceId,
            'notes' => $notes ?: 'Manufacturing consumption',
        ]);
    }

    /**
     * Record promotional/sample usage
     */
    public static function recordPromotional(int $productId, int $quantity, string $notes = ''): StockMovement
    {
        return self::recordMovement([
            'product_id' => $productId,
            'movement_type' => 'out',
            'transaction_type' => 'promotional',
            'quantity' => $quantity,
            'reference_type' => 'promotional',
            'reference_id' => null,
            'notes' => $notes ?: 'Promotional/sample usage',
        ]);
    }

    /**
     * Record bulk stock movements for multiple products
     */
    public static function recordBulkMovements(array $movements): \Illuminate\Database\Eloquent\Collection
    {
        return DB::transaction(function () use ($movements) {
            $recordedMovements = collect();

            foreach ($movements as $movementData) {
                $recordedMovements->push(self::recordMovement($movementData));
            }

            return $recordedMovements;
        });
    }
}