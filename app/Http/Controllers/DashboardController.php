<?php

namespace App\Http\Controllers;

use App\Modules\SalesOrder\Models\SalesOrder;
use App\Modules\PurchaseOrder\Models\PurchaseOrder;
use App\Modules\OperatingExpenses\Models\OperatingExpense;
use App\Modules\Products\Models\Product;
use App\Modules\Customers\Models\Customer;
use App\Modules\StoreSettings\Models\StoreSetting;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
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
        $lowStockProducts = Product::where('quantity_on_hand', '<=', \DB::raw('reorder_level'))->count();
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

        return view('dashboard', compact(
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