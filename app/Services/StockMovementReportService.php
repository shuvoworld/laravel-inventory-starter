<?php

namespace App\Services;

use App\Modules\StockMovement\Models\StockMovement;
use App\Modules\Products\Models\Product;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class StockMovementReportService
{
    /**
     * Get comprehensive stock movement report with filters
     */
    public static function generateReport(array $filters = []): array
    {
        $query = StockMovement::with(['product', 'user', 'reference'])
            ->when($filters['start_date'] ?? null, function ($q, $startDate) {
                $q->whereDate('created_at', '>=', $startDate);
            })
            ->when($filters['end_date'] ?? null, function ($q, $endDate) {
                $q->whereDate('created_at', '<=', $endDate);
            })
            ->when($filters['product_id'] ?? null, function ($q, $productId) {
                $q->where('product_id', $productId);
            })
            ->when($filters['movement_type'] ?? null, function ($q, $movementType) {
                $q->where('movement_type', $movementType);
            })
            ->when($filters['transaction_type'] ?? null, function ($q, $transactionType) {
                $q->where('transaction_type', $transactionType);
            })
            ->when($filters['user_id'] ?? null, function ($q, $userId) {
                $q->where('user_id', $userId);
            });

        $movements = $query->latest()->get();

        return [
            'movements' => $movements,
            'summary' => self::generateSummary($movements),
            'filters' => $filters,
        ];
    }

    /**
     * Generate summary statistics from movements
     */
    public static function generateSummary($movements): array
    {
        return [
            'total_movements' => $movements->count(),
            'total_in' => $movements->where('movement_type', 'in')->sum('quantity'),
            'total_out' => $movements->where('movement_type', 'out')->sum('quantity'),
            'total_adjustments' => $movements->where('movement_type', 'adjustment')->count(),
            'net_movement' => $movements->where('movement_type', 'in')->sum('quantity') -
                            $movements->where('movement_type', 'out')->sum('quantity'),
            'transaction_types' => $movements->groupBy('transaction_type')->map(function ($group) {
                return [
                    'count' => $group->count(),
                    'total_quantity' => $group->sum('quantity'),
                    'movements' => $group
                ];
            }),
            'top_products' => $movements->groupBy('product_id')
                ->map(function ($group) {
                    $product = $group->first()->product;
                    return [
                        'product_id' => $group->first()->product_id,
                        'product_name' => $product ? $product->name : 'Unknown',
                        'total_movements' => $group->count(),
                        'total_quantity' => $group->sum('quantity'),
                        'net_movement' => $group->where('movement_type', 'in')->sum('quantity') -
                                       $group->where('movement_type', 'out')->sum('quantity'),
                    ];
                })
                ->sortByDesc('total_movements')
                ->take(10)
                ->values(),
            'user_activity' => $movements->where('user_id')
                ->groupBy('user_id')
                ->map(function ($group) {
                    $user = $group->first()->user;
                    return [
                        'user_id' => $group->first()->user_id,
                        'user_name' => $user ? $user->name : 'Unknown',
                        'total_movements' => $group->count(),
                        'movements_by_type' => $group->groupBy('movement_type')->map->count()
                    ];
                })
                ->sortByDesc('total_movements')
                ->values(),
        ];
    }

    /**
     * Get daily stock movement trends
     */
    public static function getDailyTrends(Carbon $startDate, Carbon $endDate): array
    {
        $movements = StockMovement::whereBetween('created_at', [$startDate, $endDate])
            ->selectRaw('DATE(created_at) as date, movement_type, SUM(quantity) as total_quantity, COUNT(*) as count')
            ->groupBy('date', 'movement_type')
            ->orderBy('date')
            ->get()
            ->groupBy('date');

        return $movements->map(function ($dayMovements, $date) {
            $in = $dayMovements->where('movement_type', 'in')->first();
            $out = $dayMovements->where('movement_type', 'out')->first();
            $adjustment = $dayMovements->where('movement_type', 'adjustment')->first();

            return [
                'date' => $date,
                'in_quantity' => $in ? $in->total_quantity : 0,
                'out_quantity' => $out ? $out->total_quantity : 0,
                'adjustment_count' => $adjustment ? $adjustment->count : 0,
                'net_movement' => ($in ? $in->total_quantity : 0) - ($out ? $out->total_quantity : 0),
                'total_movements' => $dayMovements->sum('count'),
            ];
        })->values();
    }

    /**
     * Get product stock history
     */
    public static function getProductHistory(int $productId, Carbon $startDate, Carbon $endDate): array
    {
        $product = Product::findOrFail($productId);
        $movements = StockMovement::where('product_id', $productId)
            ->with(['user', 'reference'])
            ->whereBetween('created_at', [$startDate, $endDate])
            ->latest()
            ->get();

        return [
            'product' => $product,
            'current_stock' => $product->quantity_on_hand,
            'movements' => $movements,
            'summary' => [
                'total_movements' => $movements->count(),
                'total_in' => $movements->where('movement_type', 'in')->sum('quantity'),
                'total_out' => $movements->where('movement_type', 'out')->sum('quantity'),
                'transaction_types' => $movements->groupBy('transaction_type')->map->count(),
            ]
        ];
    }

    /**
     * Get audit trail for specific movement
     */
    public static function getAuditTrail(int $movementId): array
    {
        $movement = StockMovement::with(['product', 'user', 'reference'])
            ->findOrFail($movementId);

        return [
            'movement' => $movement,
            'audit_logs' => $movement->audits()->with('user')->latest()->get(),
            'product_state_before' => self::getProductStateAt($movement->product_id, $movement->created_at->subMinute()),
            'product_state_after' => self::getProductStateAt($movement->product_id, $movement->created_at->addMinute()),
        ];
    }

    /**
     * Get product state at specific time
     */
    private static function getProductStateAt(int $productId, $datetime): ?array
    {
        $stockAtTime = StockMovement::where('product_id', $productId)
            ->where('created_at', '<=', $datetime)
            ->selectRaw('
                SUM(CASE WHEN movement_type = "in" THEN quantity ELSE 0 END) as total_in,
                SUM(CASE WHEN movement_type = "out" THEN quantity ELSE 0 END) as total_out
            ')
            ->first();

        if (!$stockAtTime) {
            return null;
        }

        return [
            'datetime' => $datetime,
            'calculated_stock' => ($stockAtTime->total_in ?? 0) - ($stockAtTime->total_out ?? 0),
        ];
    }

    /**
     * Generate inventory valuation report
     */
    public static function getInventoryValuation(): array
    {
        $products = Product::with(['stockMovements' => function ($query) {
                $query->where('created_at', '>=', now()->subDays(30));
            }])
            ->get();

        $valuation = $products->map(function ($product) {
            $recentMovements = $product->stockMovements;
            $avgCost = $recentMovements->where('transaction_type', 'purchase')
                ->avg('quantity') ?: $product->cost_price;

            return [
                'product_id' => $product->id,
                'product_name' => $product->name,
                'current_stock' => $product->quantity_on_hand,
                'cost_price' => $product->cost_price,
                'selling_price' => $product->price,
                'avg_cost_price' => $avgCost,
                'total_value_cost' => $product->quantity_on_hand * $product->cost_price,
                'total_value_selling' => $product->quantity_on_hand * $product->price,
                'total_value_avg_cost' => $product->quantity_on_hand * $avgCost,
                'recent_movements_count' => $recentMovements->count(),
            ];
        });

        return [
            'products' => $valuation,
            'summary' => [
                'total_products' => $products->count(),
                'total_value_cost' => $valuation->sum('total_value_cost'),
                'total_value_selling' => $valuation->sum('total_value_selling'),
                'total_value_avg_cost' => $valuation->sum('total_value_avg_cost'),
                'potential_profit' => $valuation->sum('total_value_selling') - $valuation->sum('total_value_cost'),
            ]
        ];
    }

    /**
     * Export stock movements to CSV
     */
    public static function exportToCsv(array $filters = []): string
    {
        $report = self::generateReport($filters);
        $filename = 'stock_movements_' . now()->format('Y-m-d_H-i-s') . '.csv';

        $headers = [
            'ID', 'Date', 'Product', 'Movement Type', 'Transaction Type',
            'Quantity', 'Reference', 'User', 'Notes'
        ];

        $csv = implode(',', $headers) . "\n";

        foreach ($report['movements'] as $movement) {
            $row = [
                $movement->id,
                $movement->created_at->format('Y-m-d H:i:s'),
                $movement->product ? $movement->product->name : 'Unknown',
                $movement->movement_type,
                $movement->transaction_type,
                $movement->quantity,
                ($movement->reference_type ? $movement->reference_type . ' #' . $movement->reference_id : 'Manual'),
                $movement->user ? $movement->user->name : 'System',
                str_replace(["\n", "\r", ","], [" ", " ", ";"], $movement->notes),
            ];

            $csv .= implode(',', array_map(function($field) {
                return '"' . str_replace('"', '""', $field) . '"';
            }, $row)) . "\n";
        }

        return $csv;
    }
}