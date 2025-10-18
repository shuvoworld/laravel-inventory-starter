<?php

namespace App\Http\Controllers;

use App\Modules\SalesOrder\Models\SalesOrder;
use App\Modules\PurchaseOrder\Models\PurchaseOrder;
use App\Modules\OperatingExpenses\Models\OperatingExpense;
use App\Modules\Products\Models\Product;
use App\Modules\Customers\Models\Customer;
use App\Modules\StoreSettings\Models\StoreSetting;
use App\Services\StockCalculationService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;

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
     * Store-Admin Dashboard - Full financial overview
     */
    private function storeAdminDashboard(): View
    {
        // Date ranges for calculations
        $currentMonth = Carbon::now();
        $previousMonth = Carbon::now()->subMonth();
        $last30Days = Carbon::now()->subDays(30);
        $today = Carbon::now();

        // Current month financial data
        $currentMonthData = $this->getFinancialData(
            $currentMonth->copy()->startOfMonth(),
            $currentMonth->copy()->endOfMonth()
        );

        // Previous month for comparison
        $previousMonthData = $this->getFinancialData(
            $previousMonth->copy()->startOfMonth(),
            $previousMonth->copy()->endOfMonth()
        );

        // Last 30 days trend data
        $trendData = $this->getLast30DaysTrend();

        // Recent activities
        $recentSalesOrders = SalesOrder::with('customer')
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        $recentExpenses = OperatingExpense::orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        // Quick stats
        $totalCustomers = Customer::count();
        $totalProducts = Product::count();

        // Calculate low stock products using movements-based stock calculation
        $lowStockProducts = 0;
        $products = Product::where('store_id', auth()->user()->store_id)->get();
        foreach ($products as $product) {
            $currentStock = StockCalculationService::getStockForProduct($product->id);
            $reorderLevel = $product->reorder_level ?? 10;
            if ($currentStock <= $reorderLevel) {
                $lowStockProducts++;
            }
        }

        $pendingExpenses = OperatingExpense::where('payment_status', 'pending')->sum('amount');

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

        // Get store settings for currency formatting
        $companyInfo = StoreSetting::getCompanyInfo();
        $currencySettings = StoreSetting::getCurrencySettings();

        return view('dashboards.store-admin', compact(
            'currentMonthData',
            'previousMonthData',
            'trendData',
            'recentSalesOrders',
            'recentExpenses',
            'totalCustomers',
            'totalProducts',
            'lowStockProducts',
            'pendingExpenses',
            'revenueGrowth',
            'profitGrowth',
            'expenseGrowth',
            'companyInfo',
            'currencySettings'
        ));
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

        $data = $this->getFinancialData($startDate, $endDate);
        $data['period'] = ucfirst($period);
        $data['period_label'] = $startDate->format('M j') . ' - ' . $endDate->format('M j, Y');

        return response()->json($data);
    }
}