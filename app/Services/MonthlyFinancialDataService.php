<?php

namespace App\Services;

use App\Modules\SalesOrder\Models\SalesOrder;
use App\Modules\PurchaseOrder\Models\PurchaseOrder;
use App\Modules\OperatingExpenses\Models\OperatingExpense;
use App\Modules\Expense\Models\Expense;
use Carbon\Carbon;

class MonthlyFinancialDataService
{
    /**
     * Get monthly financial data for the last 12 months
     */
    public function get12MonthsFinancialData(): array
    {
        $data = [];
        $startDate = Carbon::now()->subMonths(11)->startOfMonth();

        for ($i = 0; $i < 12; $i++) {
            $currentMonth = $startDate->copy()->addMonths($i);
            $monthData = $this->getMonthlyData($currentMonth);

            $data[] = [
                'month' => $currentMonth->format('M Y'),
                'month_short' => $currentMonth->format('M'),
                'month_full' => $currentMonth->format('F'),
                'year' => $currentMonth->format('Y'),
                'sales' => $monthData['sales'],
                'purchases' => $monthData['purchases'],
                'profit' => $monthData['profit'],
                'formatted' => [
                    'sales' => number_format($monthData['sales'], 2),
                    'purchases' => number_format($monthData['purchases'], 2),
                    'profit' => number_format($monthData['profit'], 2),
                ]
            ];
        }

        return $data;
    }

    /**
     * Get financial data for a specific month
     */
    private function getMonthlyData(Carbon $month): array
    {
        $startDate = $month->copy()->startOfMonth();
        $endDate = $month->copy()->endOfMonth();

        // Calculate sales revenue
        $sales = SalesOrder::whereBetween('order_date', [$startDate, $endDate])
            ->whereIn('status', ['confirmed', 'processing', 'shipped', 'delivered'])
            ->sum('total_amount');

        // Calculate purchases amount
        $purchases = PurchaseOrder::whereBetween('order_date', [$startDate, $endDate])
            ->whereIn('status', ['confirmed', 'processing', 'received'])
            ->sum('total_amount');

        // Calculate COGS (Cost of Goods Sold)
        $cogs = $this->calculateCOGS($startDate, $endDate);

        // Calculate operating expenses
        $operatingExpenses = OperatingExpense::getExpensesForPeriod($startDate, $endDate);

        // Add general expenses
        $generalExpenses = Expense::whereBetween('expense_date', [$startDate, $endDate])
            ->whereIn('status', ['active', 'completed'])
            ->sum('amount');

        $totalExpenses = $cogs + $operatingExpenses + $generalExpenses;

        // Calculate profit
        $profit = $sales - $totalExpenses;

        return [
            'sales' => $sales,
            'purchases' => $purchases,
            'cogs' => $cogs,
            'operating_expenses' => $operatingExpenses,
            'general_expenses' => $generalExpenses,
            'total_expenses' => $totalExpenses,
            'profit' => $profit,
        ];
    }

    /**
     * Calculate Cost of Goods Sold for a period
     */
    private function calculateCOGS(Carbon $startDate, Carbon $endDate): float
    {
        // For simplicity, we'll calculate COGS based on sales orders
        // In a real implementation, this should be based on actual inventory movements

        $cogs = SalesOrder::whereBetween('order_date', [$startDate, $endDate])
            ->whereIn('status', ['confirmed', 'processing', 'shipped', 'delivered'])
            ->with(['items.product'])
            ->get()
            ->sum(function($order) {
                $total = 0;
                foreach ($order->items as $item) {
                    $total += $item->quantity * ($item->product->cost_price ?? 0);
                }
                return $total;
            });

        return $cogs;
    }

    /**
     * Get recent transactions for dashboard
     */
    public function getRecentTransactions(int $limit = 10): array
    {
        $transactions = [];

        // Get recent sales orders
        $recentSales = SalesOrder::with('customer')
            ->orderBy('created_at', 'desc')
            ->take($limit)
            ->get();

        foreach ($recentSales as $order) {
            $transactions[] = [
                'id' => $order->id,
                'type' => 'sale',
                'type_display' => 'Sales Order',
                'reference' => $order->order_number ?? 'SO-' . $order->id,
                'party' => $order->customer ? $order->customer->name : 'Walk-in Customer',
                'amount' => $order->total_amount,
                'status' => $order->status,
                'date' => $order->order_date->format('M j, Y'),
                'time' => $order->order_date->format('g:i A'),
                'created_at' => $order->created_at,
                'icon' => 'fas fa-shopping-cart',
                'color' => 'success'
            ];
        }

        // Get recent purchase orders
        $recentPurchases = PurchaseOrder::with('supplier')
            ->orderBy('created_at', 'desc')
            ->take($limit)
            ->get();

        foreach ($recentPurchases as $order) {
            $transactions[] = [
                'id' => $order->id,
                'type' => 'purchase',
                'type_display' => 'Purchase Order',
                'reference' => $order->po_number,
                'party' => $order->supplier->name,
                'amount' => $order->total_amount,
                'status' => $order->status,
                'date' => $order->order_date->format('M j, Y'),
                'time' => $order->order_date->format('g:i A'),
                'created_at' => $order->created_at,
                'icon' => 'fas fa-truck',
                'color' => 'info'
            ];
        }

        // Get recent expenses
        $recentExpenses = OperatingExpense::orderBy('created_at', 'desc')
            ->take($limit)
            ->get();

        foreach ($recentExpenses as $expense) {
            $transactions[] = [
                'id' => $expense->id,
                'type' => 'expense',
                'type_display' => 'Operating Expense',
                'reference' => $expense->reference_number ?? 'EXP-' . $expense->id,
                'party' => $expense->vendor ?? 'General',
                'amount' => $expense->amount,
                'status' => $expense->payment_status ?? 'pending',
                'date' => $expense->expense_date->format('M j, Y'),
                'time' => $expense->expense_date->format('g:i A'),
                'created_at' => $expense->created_at,
                'icon' => 'fas fa-receipt',
                'color' => 'warning'
            ];
        }

        // Sort by created_at descending
        usort($transactions, function($a, $b) {
            return $b['created_at']->timestamp - $a['created_at']->timestamp;
        });

        // Take only the top 10
        return array_slice($transactions, 0, 10);
    }
}