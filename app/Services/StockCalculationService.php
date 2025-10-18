<?php

namespace App\Services;

use App\Modules\StockMovement\Models\StockMovement;
use App\Modules\Products\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class StockCalculationService
{
    /**
     * Calculate current stock for a single product from movements
     */
    public static function getStockForProduct(int $productId): int
    {
        return Cache::remember(
            "product_stock_{$productId}",
            now()->addMinutes(30),
            function () use ($productId) {
                $result = StockMovement::where('product_id', $productId)
                    ->selectRaw("
                        SUM(CASE WHEN movement_type = 'in' THEN quantity ELSE 0 END) as total_in,
                        SUM(CASE WHEN movement_type = 'out' THEN quantity ELSE 0 END) as total_out
                    ")
                    ->first();

                return ($result->total_in ?? 0) - ($result->total_out ?? 0);
            }
        );
    }

    /**
     * Calculate stock for all products at once from movements
     */
    public static function getStockForAllProducts(): array
    {
        return Cache::remember(
            'all_products_stock',
            now()->addMinutes(15),
            function () {
                $results = StockMovement::selectRaw("
                    product_id,
                    SUM(CASE WHEN movement_type = 'in' THEN quantity ELSE 0 END) as total_in,
                    SUM(CASE WHEN movement_type = 'out' THEN quantity ELSE 0 END) as total_out,
                    COUNT(*) as movement_count
                ")
                ->groupBy('product_id')
                ->get()
                ->mapWithKeys(function ($item) {
                    return [
                        $item->product_id => [
                            'stock' => ($item->total_in ?? 0) - ($item->total_out ?? 0),
                            'total_in' => $item->total_in ?? 0,
                            'total_out' => $item->total_out ?? 0,
                            'movement_count' => $item->movement_count
                        ]
                    ];
                })
                ->toArray();

                // Add products with no movements (stock = 0)
                $productsWithoutMovements = Product::whereDoesntHave('stockMovements')
                    ->pluck('name', 'id')
                    ->mapWithKeys(function ($name, $id) {
                        return [
                            $id => [
                                'stock' => 0,
                                'total_in' => 0,
                                'total_out' => 0,
                                'movement_count' => 0
                            ]
                        ];
                    })
                    ->toArray();

                return array_merge($results, $productsWithoutMovements);
            }
        );
    }

    /**
     * Get stock at a specific date for a single product
     */
    public static function getStockAtDate(int $productId, Carbon $date): int
    {
        $result = StockMovement::where('product_id', $productId)
            ->where('created_at', '<=', $date)
            ->selectRaw("
                SUM(CASE WHEN movement_type = 'in' THEN quantity ELSE 0 END) as total_in,
                SUM(CASE WHEN movement_type = 'out' THEN quantity ELSE 0 END) as total_out
            ")
            ->first();

        return ($result->total_in ?? 0) - ($result->total_out ?? 0);
    }

    /**
     * Get stock trend over time for a product
     */
    public static function getStockTrend(int $productId, Carbon $startDate, Carbon $endDate, string $period = 'daily'): array
    {
        $dateFormat = $period === 'monthly' ? '%Y-%m' : '%Y-%m-%d';

        $results = StockMovement::where('product_id', $productId)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->selectRaw("
                DATE_FORMAT(created_at, '{$dateFormat}') as period,
                SUM(CASE WHEN movement_type = 'in' THEN quantity ELSE 0 END) as total_in,
                SUM(CASE WHEN movement_type = 'out' THEN quantity ELSE 0 END) as total_out,
                COUNT(*) as movement_count
            ")
            ->groupBy('period')
            ->orderBy('period')
            ->get();

        $trend = [];
        $runningStock = 0;

        // Get initial stock before start date
        $initialStock = StockMovement::where('product_id', $productId)
            ->where('created_at', '<', $startDate)
            ->selectRaw("
                SUM(CASE WHEN movement_type = 'in' THEN quantity ELSE 0 END) as total_in,
                SUM(CASE WHEN movement_type = 'out' THEN quantity ELSE 0 END) as total_out
            ")
            ->first();

        $runningStock = ($initialStock->total_in ?? 0) - ($initialStock->total_out ?? 0);

        foreach ($results as $result) {
            $runningStock = ($runningStock + $result->total_in) - $result->total_out;

            $trend[] = [
                'period' => $result->period,
                'stock' => $runningStock,
                'in' => $result->total_in,
                'out' => $result->total_out,
                'movement_count' => $result->movement_count
            ];
        }

        return $trend;
    }

    /**
     * Get products with low stock from movements
     */
    public static function getLowStockProducts(int $threshold = 10): array
    {
        $stockData = self::getStockForAllProducts();

        $lowStock = [];
        foreach ($stockData as $productId => $data) {
            if ($data['stock'] <= $threshold) {
                $product = Product::find($productId);
                if ($product) {
                    $lowStock[] = [
                        'product' => $product,
                        'current_stock' => $data['stock'],
                        'movement_count' => $data['movement_count'],
                        'status' => $data['stock'] === 0 ? 'out_of_stock' : 'low_stock'
                    ];
                }
            }
        }

        // Sort by stock level (lowest first)
        usort($lowStock, function ($a, $b) {
            return $a['current_stock'] - $b['current_stock'];
        });

        return $lowStock;
    }

    /**
     * Get products with no movements (potential data issues)
     */
    public static function getProductsWithoutMovements(): array
    {
        return Product::whereDoesntHave('stockMovements')
            ->select('id', 'name', 'quantity_on_hand', 'created_at')
            ->get()
            ->map(function ($product) {
                return [
                    'product' => $product,
                    'system_stock' => $product->quantity_on_hand,
                    'calculated_stock' => 0,
                    'discrepancy' => $product->quantity_on_hand
                ];
            })
            ->toArray();
    }

    /**
     * Get products with stock discrepancies (system vs calculated)
     */
    public static function getProductsWithDiscrepancies(): array
    {
        $stockData = self::getStockForAllProducts();
        $discrepancies = [];

        foreach ($stockData as $productId => $data) {
            $product = Product::find($productId);
            if ($product && $product->quantity_on_hand !== $data['stock']) {
                $discrepancies[] = [
                    'product' => $product,
                    'system_stock' => $product->quantity_on_hand,
                    'calculated_stock' => $data['stock'],
                    'discrepancy' => $product->quantity_on_hand - $data['stock'],
                    'movement_count' => $data['movement_count']
                ];
            }
        }

        // Sort by absolute discrepancy (largest first)
        usort($discrepancies, function ($a, $b) {
            return abs($b['discrepancy']) - abs($a['discrepancy']);
        });

        return $discrepancies;
    }

    /**
     * Get stock summary statistics
     */
    public static function getStockSummary(): array
    {
        $stockData = self::getStockForAllProducts();

        $totalProducts = count($stockData);
        $totalStock = array_sum(array_column($stockData, 'stock'));
        $totalIn = array_sum(array_column($stockData, 'total_in'));
        $totalOut = array_sum(array_column($stockData, 'total_out'));

        $outOfStock = count(array_filter($stockData, fn($data) => $data['stock'] === 0));
        $lowStock = count(array_filter($stockData, fn($data) => $data['stock'] > 0 && $data['stock'] <= 10));

        return [
            'total_products' => $totalProducts,
            'total_stock' => $totalStock,
            'total_in' => $totalIn,
            'total_out' => $totalOut,
            'out_of_stock_products' => $outOfStock,
            'low_stock_products' => $lowStock,
            'average_stock_per_product' => $totalProducts > 0 ? round($totalStock / $totalProducts, 2) : 0
        ];
    }

    /**
     * Get movement statistics by transaction type
     */
    public static function getMovementStatsByTransactionType(Carbon $startDate = null, Carbon $endDate = null): array
    {
        $query = StockMovement::query();

        if ($startDate) {
            $query->where('created_at', '>=', $startDate);
        }
        if ($endDate) {
            $query->where('created_at', '<=', $endDate);
        }

        return $query->selectRaw("
                transaction_type,
                movement_type,
                COUNT(*) as count,
                SUM(quantity) as total_quantity
            ")
            ->groupBy('transaction_type', 'movement_type')
            ->get()
            ->groupBy('transaction_type')
            ->mapWithKeys(function ($group) {
                return [$group->first()->transaction_type => [
                    'count' => $group->sum('count'),
                    'total_quantity' => $group->sum('total_quantity'),
                    'in_count' => $group->where('movement_type', 'in')->sum('count'),
                    'out_count' => $group->where('movement_type', 'out')->sum('count'),
                    'in_quantity' => $group->where('movement_type', 'in')->sum('total_quantity'),
                    'out_quantity' => $group->where('movement_type', 'out')->sum('total_quantity'),
                ]];
            })
            ->toArray();
    }

    /**
     * Clear stock calculation cache
     */
    public static function clearCache(?int $productId = null): void
    {
        if ($productId) {
            Cache::forget("product_stock_{$productId}");
        } else {
            Cache::forget('all_products_stock');
        }
    }

    /**
     * Validate stock integrity (system vs calculated)
     */
    public static function validateStockIntegrity(): array
    {
        $stockData = self::getStockForAllProducts();
        $integrity = [
            'total_products' => count($stockData),
            'discrepancies' => 0,
            'accurate_products' => 0,
            'total_discrepancy_amount' => 0,
            'discrepancy_details' => []
        ];

        foreach ($stockData as $productId => $data) {
            $product = Product::find($productId);
            if ($product) {
                $difference = $product->quantity_on_hand - $data['stock'];

                if ($difference !== 0) {
                    $integrity['discrepancies']++;
                    $integrity['total_discrepancy_amount'] += abs($difference);

                    $integrity['discrepancy_details'][] = [
                        'product_id' => $productId,
                        'product_name' => $product->name,
                        'system_stock' => $product->quantity_on_hand,
                        'calculated_stock' => $data['stock'],
                        'difference' => $difference
                    ];
                } else {
                    $integrity['accurate_products']++;
                }
            }
        }

        $integrity['accuracy_percentage'] = $integrity['total_products'] > 0
            ? round(($integrity['accurate_products'] / $integrity['total_products']) * 100, 2)
            : 0;

        return $integrity;
    }
}