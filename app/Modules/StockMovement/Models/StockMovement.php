<?php

namespace App\Modules\StockMovement\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;
use OwenIt\Auditing\Auditable;
use App\Traits\BelongsToStore;

class StockMovement extends Model implements AuditableContract
{
    use HasFactory, Auditable, BelongsToStore;

    protected $table = 'stock_movements';

    protected $fillable = [
        'store_id', 'product_id', 'variant_id', 'movement_type', 'transaction_type', 'quantity', 'reference_type', 'reference_id', 'notes', 'user_id'
    ];

    protected $auditInclude = [
        'product_id', 'variant_id', 'movement_type', 'transaction_type', 'quantity', 'reference_type', 'reference_id', 'notes', 'user_id'
    ];

    public function product()
    {
        return $this->belongsTo(\App\Modules\Products\Models\Product::class);
    }

    public function variant()
    {
        return $this->belongsTo(\App\Modules\Products\Models\ProductVariant::class, 'variant_id');
    }

    public function reference()
    {
        return $this->morphTo('reference', 'reference_type', 'reference_id');
    }

    /**
     * Get the actual reference model with error handling
     */
    public function getReferenceWithFallback()
    {
        try {
            return $this->reference;
        } catch (\Exception $e) {
            Log::error('StockMovement reference resolution failed', [
                'stock_movement_id' => $this->id,
                'reference_type' => $this->reference_type,
                'reference_id' => $this->reference_id,
                'error' => $e->getMessage()
            ]);

            // Return null if the relationship can't be resolved
            return null;
        }
    }

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class);
    }

    /**
     * Get all movement types
     */
    public static function getMovementTypes(): array
    {
        return [
            'in' => 'Stock In',
            'out' => 'Stock Out',
            'adjustment' => 'Stock Adjustment',
        ];
    }

    /**
     * Get all transaction types
     */
    public static function getTransactionTypes(): array
    {
        return [
            // IN Transactions (Stock IN)
            'purchase' => '📦 Purchase Order',
            'sale_return' => '↩️ Sales Return',
            'opening_stock' => '🏪 Opening Stock',
            'transfer_in' => '📥 Transfer IN',
            'stock_count_correction' => '✏️ Stock Count (+)',
            'recovery_found' => '🔍 Found/Recovered',
            'manufacturing_in' => '🏭 Manufacturing IN',

            // OUT Transactions (Stock OUT)
            'sale' => '💰 Sales Order',
            'purchase_return' => '↩️ Purchase Return',
            'damage' => '⚠️ Damage',
            'lost_missing' => '❌ Lost/Missing',
            'theft' => '🔒 Theft',
            'expired' => '⏰ Expired',
            'transfer_out' => '📤 Transfer OUT',
            'stock_count_correction_minus' => '✏️ Stock Count (-)',
            'quality_control' => '🚫 Quality Control',
            'manufacturing_out' => '🏭 Manufacturing OUT',
            'promotional' => '🎁 Promotional/Sample',

            // Adjustments
            'manual_adjustment' => '✋ Manual Adjustment',
            'stock_correction' => '🔧 Manual Stock Correction',
        ];
    }

    /**
     * Get movement direction for a transaction type
     */
    public static function getMovementDirection(string $transactionType): string
    {
        $inTransactions = [
            'purchase', 'sale_return', 'opening_stock', 'transfer_in',
            'stock_count_correction', 'recovery_found', 'manufacturing_in'
        ];

        $outTransactions = [
            'sale', 'purchase_return', 'damage', 'lost_missing', 'theft',
            'expired', 'transfer_out', 'stock_count_correction_minus',
            'quality_control', 'manufacturing_out', 'promotional'
        ];

        if (in_array($transactionType, $inTransactions)) {
            return 'in';
        } elseif (in_array($transactionType, $outTransactions)) {
            return 'out';
        }

        return 'adjustment'; // Default for manual_adjustment, stock_correction
    }

    /**
     * Get transaction types grouped by movement direction
     */
    public static function getTransactionTypesByDirection(): array
    {
        return [
            'in' => [
                'purchase' => '📦 Purchase Order',
                'sale_return' => '↩️ Sales Return',
                'opening_stock' => '🏪 Opening Stock',
                'transfer_in' => '📥 Transfer IN',
                'stock_count_correction' => '✏️ Stock Count (+)',
                'recovery_found' => '🔍 Found/Recovered',
                'manufacturing_in' => '🏭 Manufacturing IN',
            ],
            'out' => [
                'sale' => '💰 Sales Order',
                'purchase_return' => '↩️ Purchase Return',
                'damage' => '⚠️ Damage',
                'lost_missing' => '❌ Lost/Missing',
                'theft' => '🔒 Theft',
                'expired' => '⏰ Expired',
                'transfer_out' => '📤 Transfer OUT',
                'stock_count_correction_minus' => '✏️ Stock Count (-)',
                'quality_control' => '🚫 Quality Control',
                'manufacturing_out' => '🏭 Manufacturing OUT',
                'promotional' => '🎁 Promotional/Sample',
            ],
            'adjustment' => [
                'manual_adjustment' => '✋ Manual Adjustment',
                'stock_correction' => '🔧 Manual Stock Correction',
            ]
        ];
    }

    /**
     * Get current stock calculated from movements (source of truth)
     */
    public static function getCurrentStockFromMovements(int $productId): int
    {
        $movements = self::where('product_id', $productId)->get();

        $totalIn = $movements->where('movement_type', 'in')->sum('quantity');
        $totalOut = $movements->where('movement_type', 'out')->sum('quantity');

        return $totalIn - $totalOut;
    }

    /**
     * Calculate stock at a specific point in time
     */
    public static function getStockAtDate(int $productId, Carbon $date): int
    {
        $movements = self::where('product_id', $productId)
            ->where('created_at', '<=', $date)
            ->get();

        $totalIn = $movements->where('movement_type', 'in')->sum('quantity');
        $totalOut = $movements->where('movement_type', 'out')->sum('quantity');

        return $totalIn - $totalOut;
    }

    /**
     * Get recent movements for a product
     */
    public static function getProductMovements(int $productId, int $limit = 10)
    {
        return self::where('product_id', $productId)
            ->with(['user'])
            ->latest()
            ->limit($limit)
            ->get();
    }

    /**
     * Sync stock for all variants that have movements
     */
    public static function syncAllVariantStocks(): array
    {
        $variantsUpdated = 0;
        $variantsWithDiscrepancies = 0;
        $totalStockIn = 0;
        $totalStockOut = 0;

        // Get all variants that have stock movements
        $variantIds = self::whereNotNull('variant_id')
            ->distinct()
            ->pluck('variant_id');

        Log::info('Starting variant stock synchronization', [
            'total_variants' => $variantIds->count()
        ]);

        foreach ($variantIds as $variantId) {
            try {
                $variant = \App\Modules\Products\Models\ProductVariant::find($variantId);
                if (!$variant) {
                    Log::warning('Variant not found during sync', ['variant_id' => $variantId]);
                    continue;
                }

                // Calculate current stock from movements
                $currentVariantStock = self::where('variant_id', $variantId)
                    ->selectRaw("
                        SUM(CASE WHEN movement_type = 'in' THEN quantity ELSE 0 END) as total_in,
                        SUM(CASE WHEN movement_type = 'out' THEN quantity ELSE 0 END) as total_out
                    ")
                    ->first();

                $calculatedVariantStock = ($currentVariantStock->total_in ?? 0) - ($currentVariantStock->total_out ?? 0);
                $oldVariantStock = $variant->quantity_on_hand;
                $difference = $oldVariantStock - $calculatedVariantStock;

                if ($difference !== 0) {
                    $variantsWithDiscrepancies++;
                    Log::info('Variant stock discrepancy found and corrected', [
                        'variant_id' => $variantId,
                        'variant_name' => $variant->variant_name,
                        'product_id' => $variant->product_id,
                        'old_stock' => $oldVariantStock,
                        'calculated_stock' => $calculatedVariantStock,
                        'difference' => $difference
                    ]);

                    // Update variant stock
                    $variant->quantity_on_hand = $calculatedVariantStock;
                    $variant->save();
                    $variantsUpdated++;
                }

                $totalStockIn += $currentVariantStock->total_in ?? 0;
                $totalStockOut += $currentVariantStock->total_out ?? 0;

            } catch (\Exception $e) {
                Log::error('Failed to sync variant stock', [
                    'variant_id' => $variantId,
                    'error' => $e->getMessage()
                ]);
            }
        }

        Log::info('Variant stock synchronization completed', [
            'variants_updated' => $variantsUpdated,
            'variants_with_discrepancies' => $variantsWithDiscrepancies,
            'total_stock_in' => $totalStockIn,
            'total_stock_out' => $totalStockOut,
            'net_movement' => $totalStockIn - $totalStockOut
        ]);

        return [
            'variants_updated' => $variantsUpdated,
            'variants_with_discrepancies' => $variantsWithDiscrepancies,
            'total_stock_in' => $totalStockIn,
            'total_stock_out' => $totalStockOut,
            'net_movement' => $totalStockIn - $totalStockOut
        ];
    }

    /**
     * Sync stock for a specific variant
     */
    public static function syncVariantStock(int $variantId): array
    {
        $variant = \App\Modules\Products\Models\ProductVariant::findOrFail($variantId);

        // Calculate current stock from movements
        $currentVariantStock = self::where('variant_id', $variantId)
            ->selectRaw("
                SUM(CASE WHEN movement_type = 'in' THEN quantity ELSE 0 END) as total_in,
                SUM(CASE WHEN movement_type = 'out' THEN quantity ELSE 0 END) as total_out
            ")
            ->first();

        $calculatedVariantStock = ($currentVariantStock->total_in ?? 0) - ($currentVariantStock->total_out ?? 0);
        $oldVariantStock = $variant->quantity_on_hand;
        $difference = $oldVariantStock - $calculatedVariantStock;
        $updated = false;

        if ($difference !== 0) {
            $variant->quantity_on_hand = $calculatedVariantStock;
            $variant->save();
            $updated = true;

            Log::info('Single variant stock synchronized', [
                'variant_id' => $variantId,
                'variant_name' => $variant->variant_name,
                'product_id' => $variant->product_id,
                'old_stock' => $oldVariantStock,
                'new_stock' => $calculatedVariantStock,
                'difference' => $difference
            ]);
        }

        return [
            'updated' => $updated,
            'old_stock' => $oldVariantStock,
            'new_stock' => $calculatedVariantStock,
            'difference' => $difference,
            'total_in' => $currentVariantStock->total_in ?? 0,
            'total_out' => $currentVariantStock->total_out ?? 0
        ];
    }

    protected static function boot()
    {
        parent::boot();

        static::created(function ($movement) {
            // Log the movement creation
            Log::info('Stock movement created', [
                'movement_id' => $movement->id,
                'product_id' => $movement->product_id,
                'variant_id' => $movement->variant_id,
                'movement_type' => $movement->movement_type,
                'transaction_type' => $movement->transaction_type,
                'quantity' => $movement->quantity,
                'user_id' => $movement->user_id,
                'created_at' => $movement->created_at->format('Y-m-d H:i:s')
            ]);

            // Update stock immediately and also schedule backup sync
        try {
            // Update variant stock if this movement has a variant
            if ($movement->variant_id) {
                $variant = $movement->variant;
                if ($variant) {
                    // Calculate current stock from movements for this specific variant
                    $currentVariantStock = StockMovement::where('variant_id', $variant->id)
                        ->selectRaw("
                            SUM(CASE WHEN movement_type = 'in' THEN quantity ELSE 0 END) as total_in,
                            SUM(CASE WHEN movement_type = 'out' THEN quantity ELSE 0 END) as total_out
                        ")
                        ->first();

                    $calculatedVariantStock = ($currentVariantStock->total_in ?? 0) - ($currentVariantStock->total_out ?? 0);
                    $oldVariantStock = $variant->quantity_on_hand;

                    // Update variant stock if there's a difference
                    if ($oldVariantStock !== $calculatedVariantStock) {
                        $variant->quantity_on_hand = $calculatedVariantStock;
                        $variant->save();

                        Log::info('Variant stock updated immediately after movement', [
                            'variant_id' => $variant->id,
                            'variant_name' => $variant->variant_name,
                            'product_id' => $variant->product_id,
                            'movement_id' => $movement->id,
                            'old_stock' => $oldVariantStock,
                            'new_stock' => $calculatedVariantStock,
                            'difference' => $calculatedVariantStock - $oldVariantStock,
                            'movement_type' => $movement->movement_type,
                            'transaction_type' => $movement->transaction_type
                        ]);
                    }
                }
            }

            // Update product stock (for non-variant products or as aggregate total)
            $product = $movement->product;
            if ($product) {
                // Calculate current stock from movements for this specific product
                $currentStock = StockMovement::where('product_id', $product->id)
                    ->selectRaw("
                        SUM(CASE WHEN movement_type = 'in' THEN quantity ELSE 0 END) as total_in,
                        SUM(CASE WHEN movement_type = 'out' THEN quantity ELSE 0 END) as total_out
                    ")
                    ->first();

                $calculatedStock = ($currentStock->total_in ?? 0) - ($currentStock->total_out ?? 0);
                $oldStock = $product->quantity_on_hand;

                // Update product stock if there's a difference
                if ($oldStock !== $calculatedStock) {
                    $product->quantity_on_hand = $calculatedStock;
                    $product->save();

                    Log::info('Product stock updated immediately after movement', [
                        'product_id' => $product->id,
                        'product_name' => $product->name,
                        'movement_id' => $movement->id,
                        'old_stock' => $oldStock,
                        'new_stock' => $calculatedStock,
                        'difference' => $calculatedStock - $oldStock,
                        'movement_type' => $movement->movement_type,
                        'transaction_type' => $movement->transaction_type,
                        'variant_id' => $movement->variant_id
                    ]);
                }
            }
        } catch (\Exception $e) {
            Log::error('Failed to update stock immediately after movement', [
                'movement_id' => $movement->id,
                'product_id' => $movement->product_id,
                'variant_id' => $movement->variant_id,
                'error' => $e->getMessage()
            ]);
        }

        // Schedule backup sync as well
        dispatch(function () use ($movement) {
            try {
                dispatch(new \App\Jobs\SyncProductStockFromMovements($movement->product_id))
                    ->onQueue('stock-sync');
            } catch (\Exception $e) {
                Log::error('Failed to schedule backup stock sync', [
                    'movement_id' => $movement->id,
                    'error' => $e->getMessage()
                ]);
            }
        })->onQueue('stock-sync');
        });

        static::updated(function ($movement) {
            Log::info('Stock movement updated', [
                'movement_id' => $movement->id,
                'product_id' => $movement->product_id,
                'variant_id' => $movement->variant_id,
                'changes' => $movement->getDirty()
            ]);

            // Update stock immediately
        try {
            // Update variant stock if this movement has a variant
            if ($movement->variant_id) {
                $variant = $movement->variant;
                if ($variant) {
                    $currentVariantStock = StockMovement::where('variant_id', $variant->id)
                        ->selectRaw("
                            SUM(CASE WHEN movement_type = 'in' THEN quantity ELSE 0 END) as total_in,
                            SUM(CASE WHEN movement_type = 'out' THEN quantity ELSE 0 END) as total_out
                        ")
                        ->first();

                    $calculatedVariantStock = ($currentVariantStock->total_in ?? 0) - ($currentVariantStock->total_out ?? 0);
                    $oldVariantStock = $variant->quantity_on_hand;

                    if ($oldVariantStock !== $calculatedVariantStock) {
                        $variant->quantity_on_hand = $calculatedVariantStock;
                        $variant->save();

                        Log::info('Variant stock updated after movement update', [
                            'variant_id' => $variant->id,
                            'old_stock' => $oldVariantStock,
                            'new_stock' => $calculatedVariantStock
                        ]);
                    }
                }
            }

            // Update product stock
            $product = $movement->product;
            if ($product) {
                $currentStock = StockMovement::where('product_id', $product->id)
                    ->selectRaw("
                        SUM(CASE WHEN movement_type = 'in' THEN quantity ELSE 0 END) as total_in,
                        SUM(CASE WHEN movement_type = 'out' THEN quantity ELSE 0 END) as total_out
                    ")
                    ->first();

                $calculatedStock = ($currentStock->total_in ?? 0) - ($currentStock->total_out ?? 0);
                $oldStock = $product->quantity_on_hand;

                if ($oldStock !== $calculatedStock) {
                    $product->quantity_on_hand = $calculatedStock;
                    $product->save();

                    Log::info('Product stock updated after movement update', [
                        'product_id' => $product->id,
                        'old_stock' => $oldStock,
                        'new_stock' => $calculatedStock
                    ]);
                }
            }
        } catch (\Exception $e) {
            Log::error('Failed to update stock after movement update', [
                'movement_id' => $movement->id,
                'error' => $e->getMessage()
            ]);
        }
        });

        static::deleted(function ($movement) {
            Log::warning('Stock movement deleted', [
                'movement_id' => $movement->id,
                'product_id' => $movement->product_id,
                'variant_id' => $movement->variant_id,
                'movement_type' => $movement->movement_type,
                'quantity' => $movement->quantity
            ]);

            // Update stock immediately
        try {
            // Update variant stock if this movement had a variant
            if ($movement->variant_id) {
                $variant = \App\Modules\Products\Models\ProductVariant::find($movement->variant_id);
                if ($variant) {
                    $currentVariantStock = StockMovement::where('variant_id', $variant->id)
                        ->selectRaw("
                            SUM(CASE WHEN movement_type = 'in' THEN quantity ELSE 0 END) as total_in,
                            SUM(CASE WHEN movement_type = 'out' THEN quantity ELSE 0 END) as total_out
                        ")
                        ->first();

                    $calculatedVariantStock = ($currentVariantStock->total_in ?? 0) - ($currentVariantStock->total_out ?? 0);
                    $oldVariantStock = $variant->quantity_on_hand;

                    if ($oldVariantStock !== $calculatedVariantStock) {
                        $variant->quantity_on_hand = $calculatedVariantStock;
                        $variant->save();

                        Log::info('Variant stock updated after movement deletion', [
                            'variant_id' => $variant->id,
                            'old_stock' => $oldVariantStock,
                            'new_stock' => $calculatedVariantStock
                        ]);
                    }
                }
            }

            // Update product stock
            $product = \App\Modules\Products\Models\Product::find($movement->product_id);
            if ($product) {
                $currentStock = StockMovement::where('product_id', $product->id)
                    ->selectRaw("
                        SUM(CASE WHEN movement_type = 'in' THEN quantity ELSE 0 END) as total_in,
                        SUM(CASE WHEN movement_type = 'out' THEN quantity ELSE 0 END) as total_out
                    ")
                    ->first();

                $calculatedStock = ($currentStock->total_in ?? 0) - ($currentStock->total_out ?? 0);
                $oldStock = $product->quantity_on_hand;

                if ($oldStock !== $calculatedStock) {
                    $product->quantity_on_hand = $calculatedStock;
                    $product->save();

                    Log::info('Product stock updated after movement deletion', [
                        'product_id' => $product->id,
                        'old_stock' => $oldStock,
                        'new_stock' => $calculatedStock
                    ]);
                }
            }
        } catch (\Exception $e) {
            Log::error('Failed to update stock after movement deletion', [
                'movement_id' => $movement->id,
                'error' => $e->getMessage()
            ]);
        }
        });
    }
}
