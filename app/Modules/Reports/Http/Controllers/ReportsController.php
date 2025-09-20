<?php

namespace App\Modules\Reports\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\SalesOrder\Models\SalesOrder;
use App\Modules\PurchaseOrder\Models\PurchaseOrder;
use App\Modules\Products\Models\Product;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Carbon\Carbon;

/**
 * Controller for managing business reports.
 */
class ReportsController extends Controller
{
    public function index(): View
    {
        return view('reports::index');
    }

    public function profitLoss(Request $request): View
    {
        $request->validate([
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ]);

        $startDate = $request->start_date ? Carbon::parse($request->start_date)->startOfDay() : Carbon::now()->startOfMonth();
        $endDate = $request->end_date ? Carbon::parse($request->end_date)->endOfDay() : Carbon::now()->endOfMonth();

        // Sales Revenue
        $salesOrders = SalesOrder::with('items.product')
            ->whereBetween('order_date', [$startDate, $endDate])
            ->whereIn('status', ['confirmed', 'processing', 'shipped', 'delivered'])
            ->get();

        $totalRevenue = $salesOrders->sum('total_amount');
        $totalSalesQuantity = $salesOrders->sum(function($order) {
            return $order->items->sum('quantity');
        });

        // Cost of Goods Sold (COGS)
        $cogs = 0;
        foreach ($salesOrders as $order) {
            foreach ($order->items as $item) {
                $cogs += $item->quantity * ($item->product->cost_price ?? 0);
            }
        }

        // Purchase Costs
        $purchaseOrders = PurchaseOrder::with('items')
            ->whereBetween('order_date', [$startDate, $endDate])
            ->whereIn('status', ['confirmed', 'processing', 'received'])
            ->get();

        $totalPurchases = $purchaseOrders->sum('total_amount');

        // Calculate metrics
        $grossProfit = $totalRevenue - $cogs;
        $grossProfitMargin = $totalRevenue > 0 ? ($grossProfit / $totalRevenue) * 100 : 0;
        $netProfit = $grossProfit; // Simplified - in real world, subtract operating expenses

        // Top selling products
        $topProducts = collect();
        foreach ($salesOrders as $order) {
            foreach ($order->items as $item) {
                $existing = $topProducts->firstWhere('id', $item->product_id);
                if ($existing) {
                    $existing['quantity'] += $item->quantity;
                    $existing['revenue'] += $item->total_price;
                } else {
                    $topProducts->push([
                        'id' => $item->product_id,
                        'name' => $item->product->name,
                        'quantity' => $item->quantity,
                        'revenue' => $item->total_price,
                    ]);
                }
            }
        }
        $topProducts = $topProducts->sortByDesc('revenue')->take(10);

        $data = [
            'period' => [
                'start_date' => $startDate->format('Y-m-d'),
                'end_date' => $endDate->format('Y-m-d'),
                'formatted_period' => $startDate->format('M j, Y') . ' - ' . $endDate->format('M j, Y'),
            ],
            'revenue' => [
                'total_revenue' => $totalRevenue,
                'total_sales_quantity' => $totalSalesQuantity,
                'average_order_value' => $salesOrders->count() > 0 ? $totalRevenue / $salesOrders->count() : 0,
            ],
            'costs' => [
                'cogs' => $cogs,
                'total_purchases' => $totalPurchases,
            ],
            'profit' => [
                'gross_profit' => $grossProfit,
                'gross_profit_margin' => $grossProfitMargin,
                'net_profit' => $netProfit,
            ],
            'orders' => [
                'sales_orders_count' => $salesOrders->count(),
                'purchase_orders_count' => $purchaseOrders->count(),
            ],
            'top_products' => $topProducts,
        ];

        return view('reports::profit-loss', compact('data'));
    }
}
