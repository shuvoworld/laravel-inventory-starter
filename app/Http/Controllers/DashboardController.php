<?php

namespace App\Http\Controllers;

use App\Modules\SalesOrder\Models\SalesOrder;
use App\Modules\OperatingExpenses\Models\OperatingExpense;
use App\Modules\Expense\Models\Expense;
use App\Modules\Products\Models\Product;
use App\Modules\Customers\Models\Customer;
use App\Modules\StoreSettings\Models\StoreSetting;
use App\Modules\StockMovement\Models\StockMovement;
use App\Modules\PurchaseOrder\Models\PurchaseOrder;
use App\Services\StockCalculationService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(): View
    {
        $user = auth()->user();

        // Route to role-specific dashboard
        if ($user->hasRole('store-user')) {
            return $this->storeUserDashboard();
        }

        if ($user->hasRole('store-admin')) {
            return $this->storeAdminDashboard();
        }

        // Default/Superadmin dashboard
        return $this->superadminDashboard();
    }

    /**
     * Store-Admin Dashboard - Stock-focused financial overview
     */
    private function storeAdminDashboard(): View
    {
        // Date ranges for calculations
        $currentMonth = Carbon::now();
        $previousMonth = Carbon::now()->subMonth();
        $last30Days = Carbon::now()->subDays(30);
        $today = Carbon::now();

        // Stock Analytics - Core metrics from movements
        try {
            $stockSummary = StockCalculationService::getStockSummary();
        } catch (\Exception $e) {
            \Log::error('Dashboard StockCalculationService error: ' . $e->getMessage());
            $stockSummary = [
                'total_products' => Product::count(),
                'total_stock' => 0,
                'total_in' => 0,
                'total_out' => 0,
                'out_of_stock_products' => 0,
                'low_stock_products' => 0,
                'average_stock_per_product' => 0
            ];
        }

        try {
            $stockMovementsToday = StockMovement::with('product')
                ->where('created_at', '>=', $today->copy()->startOfDay())
                ->where('created_at', '<=', $today->copy()->endOfDay())
                ->orderBy('created_at', 'desc')
                ->take(10)
                ->get();

            $stockMovementsThisMonth = StockMovement::whereBetween('created_at', [
                $currentMonth->copy()->startOfMonth(),
                $currentMonth->copy()->endOfMonth()
            ])->get();
        } catch (\Exception $e) {
            \Log::error('Dashboard StockMovement query error: ' . $e->getMessage());
            $stockMovementsToday = collect();
            $stockMovementsThisMonth = collect();
        }

        try {
            $movementStats = StockCalculationService::getMovementStatsByTransactionType(
                $currentMonth->copy()->startOfMonth(),
                $currentMonth->copy()->endOfMonth()
            );
        } catch (\Exception $e) {
            \Log::error('Dashboard movement stats error: ' . $e->getMessage());
            $movementStats = [];
        }

        try {
            $lowStockProducts = StockCalculationService::getLowStockProducts(10);
            $outOfStockProducts = StockCalculationService::getLowStockProducts(0);
        } catch (\Exception $e) {
            \Log::error('Dashboard low stock error: ' . $e->getMessage());
            $lowStockProducts = [];
            $outOfStockProducts = [];
        }

        try {
            $stockIntegrity = StockCalculationService::validateStockIntegrity();
        } catch (\Exception $e) {
            \Log::error('Dashboard stock integrity error: ' . $e->getMessage());
            $stockIntegrity = [
                'total_products' => Product::count(),
                'discrepancies' => 0,
                'accurate_products' => Product::count(),
                'total_discrepancy_amount' => 0,
                'discrepancy_details' => [],
                'accuracy_percentage' => 100
            ];
        }

        // Current month financial data (stock-aware)
        $currentMonthData = $this->getStockAwareFinancialData(
            $currentMonth->copy()->startOfMonth(),
            $currentMonth->copy()->endOfMonth()
        );

        // Previous month for comparison
        $previousMonthData = $this->getStockAwareFinancialData(
            $previousMonth->copy()->startOfMonth(),
            $previousMonth->copy()->endOfMonth()
        );

        // Stock trend data for last 30 days
        $stockTrendData = $this->getStockTrendData($last30Days, $today);

        // Last 30 days financial trend
        $trendData = $this->getLast30DaysTrend();

        // Recent activities
        $recentSalesOrders = SalesOrder::with('customer')
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        $recentPurchaseOrders = PurchaseOrder::with('supplier')
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        $recentExpenses = OperatingExpense::orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        // Quick stats
        $totalCustomers = Customer::count();
        $totalProducts = Product::count();

        $pendingExpenses = OperatingExpense::where('payment_status', 'pending')->sum('amount');

        // Stock performance metrics
        $stockTurnoverRate = $this->calculateStockTurnoverRate($currentMonthData['cogs'], $stockSummary);
        $stockValue = $this->calculateCurrentStockValue();
        $stockVelocity = $this->calculateStockVelocity($stockMovementsThisMonth);

        // Growth calculations
        $revenueGrowth = $this->calculateGrowthPercentage(
            $currentMonthData['revenue'],
            $previousMonthData['revenue']
        );

        $profitGrowth = $this->calculateGrowthPercentage(
            $currentMonthData['net_profit'],
            $previousMonthData['net_profit']
        );

        $expenseGrowth = $this->calculateGrowthPercentage(
            $currentMonthData['operating_expenses'],
            $previousMonthData['operating_expenses']
        );

        // Stock growth
        $stockGrowth = $this->calculateStockGrowth($currentMonth, $previousMonth);

        // Get Profit and Loss data for Today
        $todayData = $this->getProfitLossData(
            $today->copy()->startOfDay(),
            $today->copy()->endOfDay()
        );

        // Get Profit and Loss data for This Month
        $monthData = $this->getProfitLossData(
            $currentMonth->copy()->startOfMonth(),
            $currentMonth->copy()->endOfMonth()
        );

        // Get store settings for currency formatting
        $companyInfo = StoreSetting::getCompanyInfo();
        $currencySettings = StoreSetting::getCurrencySettings();

        return view('dashboards.store-admin', compact(
            'currentMonthData',
            'previousMonthData',
            'trendData',
            'stockTrendData',
            'stockSummary',
            'stockMovementsToday',
            'stockMovementsThisMonth',
            'movementStats',
            'lowStockProducts',
            'outOfStockProducts',
            'stockIntegrity',
            'recentSalesOrders',
            'recentPurchaseOrders',
            'recentExpenses',
            'totalCustomers',
            'totalProducts',
            'pendingExpenses',
            'stockTurnoverRate',
            'stockValue',
            'stockVelocity',
            'revenueGrowth',
            'profitGrowth',
            'expenseGrowth',
            'stockGrowth',
            'companyInfo',
            'currencySettings',
            'todayData',
            'monthData'
        ));
    }

    /**
     * Helper method to safely calculate stock summary stats
     */
    private function calculateStockSummaryStats(): array
    {
        try {
            return StockCalculationService::getStockSummary();
        } catch (\Exception $e) {
            // Fallback in case of errors
            return [
                'total_products' => Product::count(),
                'total_stock' => 0,
                'total_in' => 0,
                'total_out' => 0,
                'out_of_stock_products' => 0,
                'low_stock_products' => 0,
                'average_stock_per_product' => 0
            ];
        }
    }

    /**
     * Store-User Dashboard - Sales-focused view
     */
    private function storeUserDashboard(): View
    {
        $today = Carbon::now();
        $startOfDay = $today->copy()->startOfDay();
        $endOfDay = $today->copy()->endOfDay();

        // Today's sales
        $todaysSales = SalesOrder::whereBetween('order_date', [$startOfDay, $endOfDay])
            ->whereIn('status', ['confirmed', 'processing', 'shipped', 'delivered'])
            ->get();

        $todaysRevenue = $todaysSales->sum('total_amount');
        $todaysOrdersCount = $todaysSales->count();
        $avgOrderValue = $todaysOrdersCount > 0 ? $todaysRevenue / $todaysOrdersCount : 0;

        // Recent sales orders (today)
        $recentOrders = SalesOrder::with('customer')
            ->whereBetween('created_at', [$startOfDay, $endOfDay])
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get();

        // This week's sales
        $startOfWeek = $today->copy()->startOfWeek();
        $weekSales = SalesOrder::whereBetween('order_date', [$startOfWeek, $endOfDay])
            ->whereIn('status', ['confirmed', 'processing', 'shipped', 'delivered'])
            ->sum('total_amount');

        // Recent stock movements (today)
        $stockMovementsToday = StockMovement::with('product')
            ->where('created_at', '>=', $startOfDay)
            ->where('created_at', '<=', $endOfDay)
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get();

        // Quick access data
        $totalCustomers = Customer::count();
        $totalProducts = Product::count();

        // Get store settings
        $companyInfo = StoreSetting::getCompanyInfo();
        $currencySettings = StoreSetting::getCurrencySettings();

        return view('dashboards.store-user', compact(
            'todaysRevenue',
            'todaysOrdersCount',
            'avgOrderValue',
            'weekSales',
            'recentOrders',
            'stockMovementsToday',
            'totalCustomers',
            'totalProducts',
            'companyInfo',
            'currencySettings'
        ));
    }

    /**
     * Superadmin Dashboard - System-wide overview
     */
    private function superadminDashboard(): View
    {
        // Use the full admin dashboard for superadmins
        return $this->storeAdminDashboard();
    }

    private function getFinancialData(Carbon $startDate, Carbon $endDate): array
    {
        // Sales Revenue
        $salesOrders = SalesOrder::with('items.product')
            ->whereBetween('order_date', [$startDate, $endDate])
            ->whereIn('status', ['confirmed', 'processing', 'shipped', 'delivered'])
            ->get();

        $revenue = $salesOrders->sum('total_amount');
        $ordersCount = $salesOrders->count();

        // Cost of Goods Sold (COGS)
        $cogs = 0;
        foreach ($salesOrders as $order) {
            foreach ($order->items as $item) {
                $cogs += $item->quantity * ($item->product->cost_price ?? 0);
            }
        }

        // Operating Expenses
        $operatingExpenses = OperatingExpense::getExpensesForPeriod($startDate, $endDate);

        // Calculations
        $grossProfit = $revenue - $cogs;
        $netProfit = $grossProfit - $operatingExpenses;
        $grossProfitMargin = $revenue > 0 ? ($grossProfit / $revenue) * 100 : 0;
        $netProfitMargin = $revenue > 0 ? ($netProfit / $revenue) * 100 : 0;

        return [
            'revenue' => $revenue,
            'cogs' => $cogs,
            'operating_expenses' => $operatingExpenses,
            'gross_profit' => $grossProfit,
            'net_profit' => $netProfit,
            'gross_profit_margin' => $grossProfitMargin,
            'net_profit_margin' => $netProfitMargin,
            'orders_count' => $ordersCount,
            'average_order_value' => $ordersCount > 0 ? $revenue / $ordersCount : 0,
        ];
    }

    private function getLast30DaysTrend(): array
    {
        $trend = [];
        $startDate = Carbon::now()->subDays(29)->startOfDay();

        for ($i = 0; $i < 30; $i++) {
            $date = $startDate->copy()->addDays($i);
            $dayData = $this->getFinancialData($date, $date->copy()->endOfDay());

            $trend[] = [
                'date' => $date->format('M j'),
                'revenue' => $dayData['revenue'],
                'expenses' => $dayData['cogs'] + $dayData['operating_expenses'],
                'net_profit' => $dayData['net_profit'],
            ];
        }

        return $trend;
    }

    private function calculateGrowthPercentage(float $current, float $previous): float
    {
        if ($previous == 0) {
            return $current > 0 ? 100 : 0;
        }

        return (($current - $previous) / $previous) * 100;
    }

    /**
     * Stock-aware financial data calculation
     */
    private function getStockAwareFinancialData(Carbon $startDate, Carbon $endDate): array
    {
        // Get sales and COGS from actual stock movements
        $salesMovements = StockMovement::whereBetween('created_at', [$startDate, $endDate])
            ->where('transaction_type', 'sale')
            ->with('product')
            ->get();

        // Calculate revenue from sales orders
        $salesOrders = SalesOrder::whereBetween('order_date', [$startDate, $endDate])
            ->whereIn('status', ['confirmed', 'processing', 'shipped', 'delivered'])
            ->get();

        $revenue = $salesOrders->sum('total_amount');
        $ordersCount = $salesOrders->count();

        // Calculate COGS based on actual stock movements for sales
        $cogs = 0;
        foreach ($salesMovements as $movement) {
            if ($movement->product) {
                $cogs += $movement->quantity * ($movement->product->cost_price ?? 0);
            }
        }

        // Also add COGS from any direct damage/expired movements
        $lossMovements = StockMovement::whereBetween('created_at', [$startDate, $endDate])
            ->whereIn('transaction_type', ['damage', 'expired', 'lost_missing', 'theft'])
            ->with('product')
            ->get();

        foreach ($lossMovements as $movement) {
            if ($movement->product) {
                $cogs += $movement->quantity * ($movement->product->cost_price ?? 0);
            }
        }

        // Operating Expenses
        $operatingExpenses = OperatingExpense::getExpensesForPeriod($startDate, $endDate);

        // Calculations
        $grossProfit = $revenue - $cogs;
        $netProfit = $grossProfit - $operatingExpenses;
        $grossProfitMargin = $revenue > 0 ? ($grossProfit / $revenue) * 100 : 0;
        $netProfitMargin = $revenue > 0 ? ($netProfit / $revenue) * 100 : 0;

        return [
            'revenue' => $revenue,
            'cogs' => $cogs,
            'operating_expenses' => $operatingExpenses,
            'gross_profit' => $grossProfit,
            'net_profit' => $netProfit,
            'gross_profit_margin' => $grossProfitMargin,
            'net_profit_margin' => $netProfitMargin,
            'orders_count' => $ordersCount,
            'average_order_value' => $ordersCount > 0 ? $revenue / $ordersCount : 0,
            'stock_movements_count' => $salesMovements->count() + $lossMovements->count(),
        ];
    }

    /**
     * Get stock trend data for visualization
     */
    private function getStockTrendData(Carbon $startDate, Carbon $endDate): array
    {
        $trend = [];
        $currentDate = $startDate->copy();

        while ($currentDate->lte($endDate)) {
            $dayStart = $currentDate->copy()->startOfDay();
            $dayEnd = $currentDate->copy()->endOfDay();

            // Get stock movements for this day
            $dayMovements = StockMovement::whereBetween('created_at', [$dayStart, $dayEnd])
                ->selectRaw("
                    movement_type,
                    SUM(quantity) as total_quantity,
                    COUNT(*) as movement_count
                ")
                ->groupBy('movement_type')
                ->get()
                ->keyBy('movement_type');

            $totalIn = $dayMovements->get('in')->total_quantity ?? 0;
            $totalOut = $dayMovements->get('out')->total_quantity ?? 0;
            $totalAdjustments = $dayMovements->get('adjustment')->total_quantity ?? 0;

            $trend[] = [
                'date' => $currentDate->format('M j'),
                'in' => $totalIn,
                'out' => $totalOut,
                'adjustments' => $totalAdjustments,
                'net_change' => $totalIn - $totalOut,
                'movements_count' => $dayMovements->sum('movement_count'),
            ];

            $currentDate->addDay();
        }

        return $trend;
    }

    /**
     * Calculate stock turnover rate
     */
    private function calculateStockTurnoverRate(float $cogs, array $stockSummary): float
    {
        $averageStockValue = $stockSummary['total_stock'] > 0 ? $stockSummary['total_stock'] : 1;

        // Get average cost per product
        $avgCostPerUnit = DB::table('products')
            ->where('store_id', auth()->user()->store_id)
            ->avg('cost_price') ?? 0;

        $totalStockValue = $stockSummary['total_stock'] * $avgCostPerUnit;

        if ($totalStockValue > 0) {
            return ($cogs / $totalStockValue) * 12; // Annualized turnover rate
        }

        return 0;
    }

    /**
     * Calculate current stock value
     */
    private function calculateCurrentStockValue(): float
    {
        $stockData = StockCalculationService::getStockForAllProducts();
        $totalValue = 0;

        foreach ($stockData as $productId => $data) {
            if ($data['stock'] > 0) {
                $product = Product::find($productId);
                if ($product && $product->cost_price) {
                    $totalValue += $data['stock'] * $product->cost_price;
                }
            }
        }

        return $totalValue;
    }

    /**
     * Calculate stock velocity (movements per day)
     */
    private function calculateStockVelocity($stockMovements): float
    {
        $daysInMonth = Carbon::now()->daysInMonth;
        $totalMovements = $stockMovements->count();

        return $totalMovements / $daysInMonth;
    }

    /**
     * Calculate stock growth percentage
     */
    private function calculateStockGrowth(Carbon $currentMonth, Carbon $previousMonth): float
    {
        // Get stock value at start of current month
        $currentStockValue = StockMovement::where('created_at', '<', $currentMonth->copy()->startOfMonth())
            ->selectRaw("
                SUM(CASE WHEN movement_type = 'in' THEN quantity ELSE 0 END) as total_in,
                SUM(CASE WHEN movement_type = 'out' THEN quantity ELSE 0 END) as total_out
            ")
            ->first();

        $currentStock = ($currentStockValue->total_in ?? 0) - ($currentStockValue->total_out ?? 0);

        // Get stock value at start of previous month
        $previousStockValue = StockMovement::where('created_at', '<', $previousMonth->copy()->startOfMonth())
            ->selectRaw("
                SUM(CASE WHEN movement_type = 'in' THEN quantity ELSE 0 END) as total_in,
                SUM(CASE WHEN movement_type = 'out' THEN quantity ELSE 0 END) as total_out
            ")
            ->first();

        $previousStock = ($previousStockValue->total_in ?? 0) - ($previousStockValue->total_out ?? 0);

        return $this->calculateGrowthPercentage($currentStock, $previousStock);
    }

    public function financialSummary(Request $request)
    {
        $period = $request->get('period', 'month'); // month, week, quarter

        switch ($period) {
            case 'week':
                $startDate = Carbon::now()->startOfWeek();
                $endDate = Carbon::now()->endOfWeek();
                break;
            case 'quarter':
                $startDate = Carbon::now()->startOfQuarter();
                $endDate = Carbon::now()->endOfQuarter();
                break;
            default:
                $startDate = Carbon::now()->startOfMonth();
                $endDate = Carbon::now()->endOfMonth();
        }

        $data = $this->getStockAwareFinancialData($startDate, $endDate);
        $data['period'] = ucfirst($period);
        $data['period_label'] = $startDate->format('M j') . ' - ' . $endDate->format('M j, Y');

        return response()->json($data);
    }

    /**
     * Get simplified Profit and Loss data (5 sections including purchases)
     */
    private function getProfitLossData(Carbon $startDate, Carbon $endDate): array
    {
        // 1. INCOME - Total revenue from sales
        $income = SalesOrder::whereBetween('order_date', [$startDate, $endDate])
            ->whereIn('status', ['confirmed', 'processing', 'shipped', 'delivered'])
            ->sum('total_amount');

        // 2. PURCHASES - Total purchase amount from purchase orders
        $purchases = PurchaseOrder::whereBetween('order_date', [$startDate, $endDate])
            ->whereIn('status', ['confirmed', 'processing', 'received'])
            ->sum('total_amount');

        // 3. COST OF GOODS SOLD - Calculate from stock movements using WAC
        $salesMovements = StockMovement::whereBetween('created_at', [$startDate, $endDate])
            ->where('transaction_type', 'sale')
            ->with('product')
            ->get();

        $cogs = 0;
        foreach ($salesMovements as $movement) {
            if ($movement->product) {
                // Use cost_price which is synced with WAC
                $cogs += $movement->quantity * ($movement->product->cost_price ?? 0);
            }
        }

        // Subtract cost of returned goods
        $returnMovements = StockMovement::whereBetween('created_at', [$startDate, $endDate])
            ->where('transaction_type', 'return')
            ->with('product')
            ->get();

        foreach ($returnMovements as $movement) {
            if ($movement->product) {
                $cogs -= $movement->quantity * ($movement->product->cost_price ?? 0);
            }
        }

        // 4. OPERATING EXPENSES - All business expenses (from both tables)
        $operatingExpenses = OperatingExpense::getExpensesForPeriod($startDate, $endDate);

        // Add expenses from the Expense module
        $generalExpenses = Expense::whereBetween('expense_date', [$startDate, $endDate])
            ->whereIn('status', ['active', 'completed'])
            ->sum('amount');

        $totalExpenses = $operatingExpenses + $generalExpenses;

        // 5. NET PROFIT/LOSS - Income - COGS - Operating Expenses
        $netProfit = $income - $cogs - $totalExpenses;

        return [
            'income' => $income,
            'purchases' => $purchases,
            'cogs' => $cogs,
            'operating_expenses' => $totalExpenses,
            'net_profit' => $netProfit,
        ];
    }
}
