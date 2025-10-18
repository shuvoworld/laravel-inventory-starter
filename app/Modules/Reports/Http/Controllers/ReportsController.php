<?php

namespace App\Modules\Reports\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\SalesOrder\Models\SalesOrder;
use App\Modules\PurchaseOrder\Models\PurchaseOrder;
use App\Modules\Products\Models\Product;
use App\Modules\OperatingExpenses\Models\OperatingExpense;
use App\Services\COGSService;
use App\Services\DailySalesReportService;
use App\Services\WeeklyProductPerformanceService;
use App\Services\LowStockAlertService;
use App\Services\StockReportService;
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
            'period_type' => 'nullable|in:day,week,month,custom',
        ]);

        $periodType = $request->period_type ?? 'month';

        // Set default date ranges based on period type
        switch ($periodType) {
            case 'day':
                $startDate = $request->start_date ? Carbon::parse($request->start_date) : Carbon::now()->subDays(30);
                $endDate = $request->end_date ? Carbon::parse($request->end_date) : Carbon::now();
                break;
            case 'week':
                $startDate = $request->start_date ? Carbon::parse($request->start_date) : Carbon::now()->subWeeks(12);
                $endDate = $request->end_date ? Carbon::parse($request->end_date) : Carbon::now();
                break;
            case 'month':
                $startDate = $request->start_date ? Carbon::parse($request->start_date) : Carbon::now()->subMonths(12);
                $endDate = $request->end_date ? Carbon::parse($request->end_date) : Carbon::now();
                break;
            default: // custom
                $startDate = $request->start_date ? Carbon::parse($request->start_date)->startOfDay() : Carbon::now()->startOfMonth();
                $endDate = $request->end_date ? Carbon::parse($request->end_date)->endOfDay() : Carbon::now()->endOfMonth();
        }

        $startDate = $startDate->startOfDay();
        $endDate = $endDate->endOfDay();

        // Initialize COGS Service
        $cogsService = new COGSService();

        // Sales Revenue
        $salesOrders = SalesOrder::with('items.product')
            ->whereBetween('order_date', [$startDate, $endDate])
            ->whereIn('status', ['confirmed', 'processing', 'shipped', 'delivered'])
            ->get();

        $totalRevenue = $salesOrders->sum('total_amount');
        $totalSalesQuantity = $salesOrders->sum(function($order) {
            return $order->items->sum('quantity');
        });

        // Cost of Goods Sold (COGS) using service
        $cogs = $cogsService->calculatePeriodCOGS($startDate, $endDate);

        // Purchase Costs
        $purchaseOrders = PurchaseOrder::with('items')
            ->whereBetween('order_date', [$startDate, $endDate])
            ->whereIn('status', ['confirmed', 'processing', 'received'])
            ->get();

        $totalPurchases = $purchaseOrders->sum('total_amount');

        // Operating Expenses
        $operatingExpenses = OperatingExpense::getExpensesForPeriod($startDate, $endDate);
        $expensesByCategory = OperatingExpense::getExpensesByCategoryForPeriod($startDate, $endDate);

        // Calculate metrics
        $grossProfit = $totalRevenue - $cogs;
        $grossProfitMargin = $totalRevenue > 0 ? ($grossProfit / $totalRevenue) * 100 : 0;
        $netProfit = $grossProfit - $operatingExpenses;
        $netProfitMargin = $totalRevenue > 0 ? ($netProfit / $totalRevenue) * 100 : 0;

        // Get enhanced product breakdown with COGS data
        $topProducts = collect($cogsService->getProductCOGSBreakdown($startDate, $endDate))
            ->sortByDesc('total_revenue')
            ->take(10);

        // Generate period breakdown for charts using service
        $periodBreakdown = $cogsService->getCOGSTrends($startDate, $endDate, $periodType);

        // Get COGS summary
        $cogsSummary = $cogsService->getCOGSSummary($startDate, $endDate);

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
                'operating_expenses' => $operatingExpenses,
                'total_expenses' => $cogs + $operatingExpenses,
            ],
            'profit' => [
                'gross_profit' => $grossProfit,
                'gross_profit_margin' => $grossProfitMargin,
                'net_profit' => $netProfit,
                'net_profit_margin' => $netProfitMargin,
            ],
            'expenses_by_category' => $expensesByCategory,
            'orders' => [
                'sales_orders_count' => $salesOrders->count(),
                'purchase_orders_count' => $purchaseOrders->count(),
            ],
            'top_products' => $topProducts,
            'period_breakdown' => $periodBreakdown,
            'period_type' => $periodType,
            'cogs_summary' => $cogsSummary,
        ];

        return view('reports::profit-loss', compact('data'));
    }

    private function generatePeriodBreakdown($salesOrders, Carbon $startDate, Carbon $endDate, string $periodType): array
    {
        $breakdown = [];
        $current = $startDate->copy();

        while ($current <= $endDate) {
            $periodStart = $current->copy();
            $periodEnd = match($periodType) {
                'day' => $current->copy()->endOfDay(),
                'week' => $current->copy()->endOfWeek(),
                'month' => $current->copy()->endOfMonth(),
                default => $current->copy()->endOfDay(),
            };

            // Don't go beyond the end date
            if ($periodEnd > $endDate) {
                $periodEnd = $endDate->copy();
            }

            // Filter orders for this period
            $periodOrders = $salesOrders->filter(function($order) use ($periodStart, $periodEnd) {
                $orderDate = Carbon::parse($order->order_date);
                return $orderDate >= $periodStart && $orderDate <= $periodEnd;
            });

            $revenue = $periodOrders->sum('total_amount');
            $cogs = 0;

            foreach ($periodOrders as $order) {
                foreach ($order->items as $item) {
                    $cogs += $item->quantity * ($item->product->cost_price ?? 0);
                }
            }

            // Get operating expenses for this period
            $periodOperatingExpenses = OperatingExpense::getExpensesForPeriod($periodStart, $periodEnd);

            $grossProfit = $revenue - $cogs;
            $netProfit = $grossProfit - $periodOperatingExpenses;

            $breakdown[] = [
                'period' => match($periodType) {
                    'day' => $periodStart->format('M j'),
                    'week' => $periodStart->format('M j') . ' - ' . $periodEnd->format('M j'),
                    'month' => $periodStart->format('M Y'),
                    default => $periodStart->format('M j'),
                },
                'period_start' => $periodStart->format('Y-m-d'),
                'period_end' => $periodEnd->format('Y-m-d'),
                'revenue' => $revenue,
                'cogs' => $cogs,
                'operating_expenses' => $periodOperatingExpenses,
                'gross_profit' => $grossProfit,
                'net_profit' => $netProfit,
                'orders_count' => $periodOrders->count(),
            ];

            // Move to next period
            $current = match($periodType) {
                'day' => $current->addDay(),
                'week' => $current->addWeek(),
                'month' => $current->addMonth(),
                default => $current->addDay(),
            };
        }

        return $breakdown;
    }

    /**
     * Generate daily sales report
     */
    public function dailySales(Request $request): View
    {
        $request->validate([
            'date' => 'nullable|date',
        ]);

        $date = $request->date ? Carbon::parse($request->date) : Carbon::today();
        $dailySalesService = new DailySalesReportService();

        $report = $dailySalesService->generateDailyReport($date);
        $weeklyTrends = $dailySalesService->getWeeklyTrends();

        return view('reports::daily-sales', compact('report', 'weeklyTrends', 'date'));
    }

    /**
     * Generate weekly product performance report
     */
    public function weeklyPerformance(Request $request): View
    {
        $request->validate([
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ]);

        // Default to current week
        $startDate = $request->start_date ? Carbon::parse($request->start_date)->startOfDay() : Carbon::now()->startOfWeek();
        $endDate = $request->end_date ? Carbon::parse($request->end_date)->endOfDay() : Carbon::now()->endOfWeek();

        $weeklyPerformanceService = new WeeklyProductPerformanceService();
        $report = $weeklyPerformanceService->generateWeeklyReport($startDate, $endDate);
        $comparison = $weeklyPerformanceService->getWeeklyComparison($startDate);

        return view('reports::weekly-performance', compact('report', 'comparison', 'startDate', 'endDate'));
    }

    /**
     * Generate low stock alert report
     */
    public function lowStockAlert(Request $request): View
    {
        $request->validate([
            'threshold' => 'nullable|integer|min:1|max:1000',
        ]);

        $threshold = $request->threshold ?? 10;
        $lowStockService = new LowStockAlertService();
        $report = $lowStockService->generateLowStockAlert($threshold);
        $dashboardData = $lowStockService->getLowStockByCategory();

        return view('reports::low-stock-alert', compact('report', 'threshold', 'dashboardData'));
    }

    /**
     * Generate comprehensive stock report
     */
    public function stockReport(Request $request): View
    {
        $stockService = new StockReportService();
        $overview = $stockService->getStockOverview();
        $reorderRecommendations = $stockService->getReorderRecommendations()->take(10);
        $valuation = $stockService->getStockValuation();

        return view('reports::stock', compact('overview', 'reorderRecommendations', 'valuation'));
    }

    /**
     * Generate detailed stock report with filters
     */
    public function stockReportDetailed(Request $request): View
    {
        $request->validate([
            'category_id' => 'nullable|integer|exists:categories,id',
            'brand_id' => 'nullable|integer|exists:brands,id',
            'supplier_id' => 'nullable|integer|exists:suppliers,id',
            'stock_status' => 'nullable|in:in_stock,low_stock,out_of_stock',
            'search' => 'nullable|string|max:255',
        ]);

        $filters = $request->only(['category_id', 'brand_id', 'supplier_id', 'stock_status', 'search']);

        $stockService = new StockReportService();
        $detailedStock = $stockService->getDetailedStock($filters);

        return view('reports::stock-detailed', compact('detailedStock', 'filters'));
    }

    /**
     * Generate stock movement trends report
     */
    public function stockMovementTrends(Request $request): View
    {
        $request->validate([
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'period_type' => 'nullable|in:day,week,month',
        ]);

        $periodType = $request->period_type ?? 'day';

        // Set default date ranges based on period type
        switch ($periodType) {
            case 'day':
                $startDate = $request->start_date ? Carbon::parse($request->start_date) : Carbon::now()->subDays(30);
                $endDate = $request->end_date ? Carbon::parse($request->end_date) : Carbon::now();
                break;
            case 'week':
                $startDate = $request->start_date ? Carbon::parse($request->start_date) : Carbon::now()->subWeeks(12);
                $endDate = $request->end_date ? Carbon::parse($request->end_date) : Carbon::now();
                break;
            case 'month':
                $startDate = $request->start_date ? Carbon::parse($request->start_date) : Carbon::now()->subMonths(12);
                $endDate = $request->end_date ? Carbon::parse($request->end_date) : Carbon::now();
                break;
        }

        $stockService = new StockReportService();
        $trends = $stockService->getStockMovementTrends($startDate, $endDate);

        return view('reports::stock-movement-trends', compact('trends', 'startDate', 'endDate', 'periodType'));
    }

    /**
     * Generate stock reorder recommendations report
     */
    public function stockReorderRecommendations(Request $request): View
    {
        $stockService = new StockReportService();
        $recommendations = $stockService->getReorderRecommendations();

        return view('reports::stock-reorder-recommendations', compact('recommendations'));
    }

    /**
     * Generate stock valuation report
     */
    public function stockValuation(Request $request): View
    {
        $stockService = new StockReportService();
        $valuation = $stockService->getStockValuation();
        $overview = $stockService->getStockOverview();

        return view('reports::stock-valuation', compact('valuation', 'overview'));
    }
}
