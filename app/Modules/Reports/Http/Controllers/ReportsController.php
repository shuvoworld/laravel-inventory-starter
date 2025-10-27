<?php

namespace App\Modules\Reports\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\SalesOrder\Models\SalesOrder;
use App\Modules\PurchaseOrder\Models\PurchaseOrder;
use App\Modules\Products\Models\Product;
use App\Modules\OperatingExpenses\Models\OperatingExpense;
use App\Modules\Expense\Models\Expense;
use App\Modules\ExpenseCategory\Models\ExpenseCategory;
use App\Modules\Customers\Models\Customer;
use App\Modules\Suppliers\Models\Supplier;
use App\Services\COGSService;
use App\Services\DailySalesReportService;
use App\Services\DailyPurchaseReportService;
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

        // General Expenses from Expense module
        $generalExpenses = Expense::whereBetween('expense_date', [$startDate, $endDate])
            ->whereIn('status', ['active', 'completed'])
            ->sum('amount');

        // Total expenses (operating + general)
        $totalExpenses = $operatingExpenses + $generalExpenses;

        // Combine expenses by category
        $operatingExpensesByCategory = OperatingExpense::getExpensesByCategoryForPeriod($startDate, $endDate);
        $generalExpensesByCategory = Expense::whereBetween('expense_date', [$startDate, $endDate])
            ->whereIn('status', ['active', 'completed'])
            ->with('category')
            ->get()
            ->groupBy('category.name')
            ->map(function ($group) {
                return [
                    'category_name' => $group->first()->category->name ?? 'Uncategorized',
                    'total' => $group->sum('amount')
                ];
            })->toArray();

        $expensesByCategory = [];

        // Combine both expense types
        foreach ($operatingExpensesByCategory as $category => $amount) {
            $expensesByCategory[] = [
                'category_name' => $category,
                'total' => $amount,
                'type' => 'operating'
            ];
        }

        foreach ($generalExpensesByCategory as $expense) {
            $expensesByCategory[] = [
                'category_name' => $expense['category_name'],
                'total' => $expense['total'],
                'type' => 'general'
            ];
        }

        // Calculate metrics
        $grossProfit = $totalRevenue - $cogs;
        $grossProfitMargin = $totalRevenue > 0 ? ($grossProfit / $totalRevenue) * 100 : 0;
        $netProfit = $grossProfit - $totalExpenses;
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
                'general_expenses' => $generalExpenses,
                'total_expenses' => $totalExpenses,
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
        $monthlyTrends = $dailySalesService->getMonthlyTrends();

        return view('reports::daily-sales', compact('report', 'monthlyTrends', 'date'));
    }

    /**
     * Generate daily purchase report
     */
    public function dailyPurchase(Request $request): View
    {
        $request->validate([
            'date' => 'nullable|date',
        ]);

        $date = $request->date ? Carbon::parse($request->date) : Carbon::today();
        $dailyPurchaseService = new DailyPurchaseReportService();

        $report = $dailyPurchaseService->generateDailyReport($date);
        $monthlyTrends = $dailyPurchaseService->getMonthlyTrends();

        return view('reports::daily-purchase', compact('report', 'monthlyTrends', 'date'));
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

    /**
     * Generate supplier due report
     */
    public function supplierDueReport(Request $request): View
    {
        $request->validate([
            'status' => 'nullable|in:pending,overdue,paid,all',
            'supplier_id' => 'nullable|integer|exists:suppliers,id',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'due_date_from' => 'nullable|date',
            'due_date_to' => 'nullable|date|after_or_equal:due_date_from',
        ]);

        $status = $request->status ?? 'all';
        $supplierId = $request->supplier_id;
        $startDate = $request->start_date ? Carbon::parse($request->start_date) : null;
        $endDate = $request->end_date ? Carbon::parse($request->end_date) : null;
        $dueDateFrom = $request->due_date_from ? Carbon::parse($request->due_date_from) : null;
        $dueDateTo = $request->due_date_to ? Carbon::parse($request->due_date_to) : null;

        // Start building the query
        $query = PurchaseOrder::with(['supplier', 'items'])
            ->where(function($q) {
                $q->where('payment_status', '!=', 'paid')
                  ->orWhere('total_amount', '>', 'paid_amount');
            });

        // Apply filters
        if ($status !== 'all') {
            switch ($status) {
                case 'pending':
                    $query->where('payment_status', 'pending');
                    break;
                case 'overdue':
                    $query->where('order_date', '<', Carbon::now()->subDays(30))
                          ->where('payment_status', '!=', 'paid');
                    break;
                case 'paid':
                    $query->where('payment_status', 'paid');
                    break;
            }
        }

        if ($supplierId) {
            $query->where('supplier_id', $supplierId);
        }

        if ($startDate) {
            $query->whereDate('order_date', '>=', $startDate);
        }

        if ($endDate) {
            $query->whereDate('order_date', '<=', $endDate);
        }

        if ($dueDateFrom) {
            $query->whereDate('order_date', '>=', $dueDateFrom);
        }

        if ($dueDateTo) {
            $query->whereDate('order_date', '<=', $dueDateTo);
        }

        // Get the purchase orders
        $purchaseOrders = $query->orderBy('order_date', 'asc')->get();

        // Calculate summary data
        $summary = [
            'total_due' => 0,
            'total_overdue' => 0,
            'total_pending' => 0,
            'overdue_count' => 0,
            'pending_count' => 0,
            'supplier_count' => $purchaseOrders->pluck('supplier_id')->unique()->count(),
        ];

        $suppliers = [];
        $overdueOrders = collect();
        $pendingOrders = collect();

        foreach ($purchaseOrders as $order) {
            $dueAmount = $order->total_amount - $order->paid_amount;
            $summary['total_due'] += $dueAmount;

            if (!isset($suppliers[$order->supplier_id])) {
                $suppliers[$order->supplier_id] = [
                    'supplier' => $order->supplier,
                    'total_due' => 0,
                    'overdue' => 0,
                    'pending' => 0,
                    'order_count' => 0,
                ];
            }

            $suppliers[$order->supplier_id]['total_due'] += $dueAmount;
            $suppliers[$order->supplier_id]['order_count']++;

            if ($order->order_date < Carbon::now()->subDays(30) && $order->payment_status !== 'paid') {
                $suppliers[$order->supplier_id]['overdue'] += $dueAmount;
                $summary['total_overdue'] += $dueAmount;
                $summary['overdue_count']++;
                $overdueOrders->push($order);
            } elseif ($order->payment_status === 'pending') {
                $suppliers[$order->supplier_id]['pending'] += $dueAmount;
                $summary['total_pending'] += $dueAmount;
                $summary['pending_count']++;
                $pendingOrders->push($order);
            }
        }

        // Get all suppliers for filter dropdown
        $allSuppliers = Supplier::orderBy('name')->get();

        return view('reports::supplier-due', compact(
            'purchaseOrders',
            'suppliers',
            'overdueOrders',
            'pendingOrders',
            'summary',
            'allSuppliers'
        ))->with('filters', [
            'status' => $status,
            'supplier_id' => $supplierId,
            'start_date' => $startDate?->format('Y-m-d'),
            'end_date' => $endDate?->format('Y-m-d'),
            'due_date_from' => $dueDateFrom?->format('Y-m-d'),
            'due_date_to' => $dueDateTo?->format('Y-m-d'),
        ]);
    }

    /**
     * Generate customer due report
     */
    public function customerDueReport(Request $request): View
    {
        $request->validate([
            'status' => 'nullable|in:pending,overdue,paid,all',
            'customer_id' => 'nullable|integer|exists:customers,id',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'due_date_from' => 'nullable|date',
            'due_date_to' => 'nullable|date|after_or_equal:due_date_from',
        ]);

        $status = $request->status ?? 'all';
        $customerId = $request->customer_id;
        $startDate = $request->start_date ? Carbon::parse($request->start_date) : null;
        $endDate = $request->end_date ? Carbon::parse($request->end_date) : null;
        $dueDateFrom = $request->due_date_from ? Carbon::parse($request->due_date_from) : null;
        $dueDateTo = $request->due_date_to ? Carbon::parse($request->due_date_to) : null;

        // Start building the query
        $query = SalesOrder::with(['customer', 'items'])
            ->where(function($q) {
                $q->where('payment_status', '!=', 'paid')
                  ->orWhere('total_amount', '>', 'paid_amount');
            });

        // Apply filters
        if ($status !== 'all') {
            switch ($status) {
                case 'pending':
                    $query->where('payment_status', 'pending');
                    break;
                case 'overdue':
                    $query->where(function($q) {
                        $q->where('payment_status', 'pending')
                          ->orWhere('payment_status', 'partial');
                    })->whereDate('created_at', '<', Carbon::now()->subDays(30));
                    break;
                case 'paid':
                    $query->where('payment_status', 'paid');
                    break;
            }
        }

        if ($customerId) {
            $query->where('customer_id', $customerId);
        }

        if ($startDate) {
            $query->whereDate('order_date', '>=', $startDate);
        }

        if ($endDate) {
            $query->whereDate('order_date', '<=', $endDate);
        }

        if ($dueDateFrom) {
            $query->whereDate('created_at', '>=', $dueDateFrom);
        }

        if ($dueDateTo) {
            $query->whereDate('created_at', '<=', $dueDateTo);
        }

        // Get the sales orders
        $salesOrders = $query->orderBy('order_date', 'desc')->get();

        // Calculate summary data
        $summary = [
            'total_due' => 0,
            'total_overdue' => 0,
            'total_pending' => 0,
            'overdue_count' => 0,
            'pending_count' => 0,
            'customer_count' => $salesOrders->pluck('customer_id')->unique()->count(),
        ];

        $customers = [];
        $overdueOrders = collect();
        $pendingOrders = collect();

        foreach ($salesOrders as $order) {
            $dueAmount = $order->total_amount - $order->paid_amount;
            $summary['total_due'] += $dueAmount;

            if (!isset($customers[$order->customer_id])) {
                $customers[$order->customer_id] = [
                    'customer' => $order->customer,
                    'total_due' => 0,
                    'overdue' => 0,
                    'pending' => 0,
                    'order_count' => 0,
                    'last_order_date' => $order->order_date,
                ];
            }

            $customers[$order->customer_id]['total_due'] += $dueAmount;
            $customers[$order->customer_id]['order_count']++;

            // Update last order date if more recent
            if ($order->order_date > $customers[$order->customer_id]['last_order_date']) {
                $customers[$order->customer_id]['last_order_date'] = $order->order_date;
            }

            // Check if order is overdue (older than 30 days and not fully paid)
            $isOverdue = $order->order_date < Carbon::now()->subDays(30) &&
                        in_array($order->payment_status, ['pending', 'partial']);

            if ($isOverdue) {
                $customers[$order->customer_id]['overdue'] += $dueAmount;
                $summary['total_overdue'] += $dueAmount;
                $summary['overdue_count']++;
                $overdueOrders->push($order);
            } elseif (in_array($order->payment_status, ['pending', 'partial'])) {
                $customers[$order->customer_id]['pending'] += $dueAmount;
                $summary['total_pending'] += $dueAmount;
                $summary['pending_count']++;
                $pendingOrders->push($order);
            }
        }

        // Sort customers by total due amount (highest first)
        uasort($customers, function($a, $b) {
            return $b['total_due'] <=> $a['total_due'];
        });

        // Get all customers for filter dropdown
        $allCustomers = Customer::orderBy('name')->get();

        return view('reports::customer-due', compact(
            'salesOrders',
            'customers',
            'overdueOrders',
            'pendingOrders',
            'summary',
            'allCustomers'
        ))->with('filters', [
            'status' => $status,
            'customer_id' => $customerId,
            'start_date' => $startDate?->format('Y-m-d'),
            'end_date' => $endDate?->format('Y-m-d'),
            'due_date_from' => $dueDateFrom?->format('Y-m-d'),
            'due_date_to' => $dueDateTo?->format('Y-m-d'),
        ]);
    }
}
