<?php

namespace App\Modules\OperatingExpenses\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\OperatingExpenses\Models\OperatingExpense;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;

/**
 * Controller for managing Operating Expenses CRUD pages and DataTables endpoint.
 */
class OperatingExpensesController extends Controller
{
    public function index(Request $request): View
    {
        return view('operating-expenses::index');
    }

    /** DataTables server-side endpoint (Yajra) */
    public function data(Request $request)
    {
        $query = OperatingExpense::query();

        return DataTables::eloquent($query)
            ->addColumn('category_label', function (OperatingExpense $expense) {
                return $expense->category_label;
            })
            ->addColumn('payment_status_badge', function (OperatingExpense $expense) {
                $badges = [
                    'pending' => 'badge-warning',
                    'paid' => 'badge-success',
                    'overdue' => 'badge-danger',
                ];
                $class = $badges[$expense->payment_status] ?? 'badge-secondary';
                return "<span class='badge {$class}'>{$expense->payment_status_label}</span>";
            })
            ->addColumn('frequency_label', function (OperatingExpense $expense) {
                return $expense->frequency_label;
            })
            ->addColumn('actions', function (OperatingExpense $expense) {
                return view('operating-expenses::partials.actions', ['id' => $expense->id])->render();
            })
            ->editColumn('expense_date', function (OperatingExpense $expense) {
                return $expense->expense_date?->format('Y-m-d');
            })
            ->editColumn('amount', function (OperatingExpense $expense) {
                return '$' . number_format($expense->amount, 2);
            })
            ->rawColumns(['actions', 'payment_status_badge'])
            ->toJson();
    }

    public function create(): View
    {
        $categories = OperatingExpense::getCategories();
        $paymentStatuses = OperatingExpense::getPaymentStatuses();
        $frequencies = OperatingExpense::getFrequencies();

        return view('operating-expenses::create', compact('categories', 'paymentStatuses', 'frequencies'));
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'category' => 'required|string|max:255',
            'description' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
            'expense_date' => 'required|date',
            'payment_status' => 'required|in:pending,paid,overdue',
            'frequency' => 'required|in:one_time,daily,weekly,monthly,quarterly,yearly',
            'vendor' => 'nullable|string|max:255',
            'receipt_number' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        OperatingExpense::create($request->all());

        return redirect()->route('modules.operating-expenses.index')->with('success', 'Operating Expense created successfully.');
    }

    public function show(int $id): View
    {
        $expense = OperatingExpense::findOrFail($id);
        return view('operating-expenses::show', compact('expense'));
    }

    public function edit(int $id): View
    {
        $expense = OperatingExpense::findOrFail($id);
        $categories = OperatingExpense::getCategories();
        $paymentStatuses = OperatingExpense::getPaymentStatuses();
        $frequencies = OperatingExpense::getFrequencies();

        return view('operating-expenses::edit', compact('expense', 'categories', 'paymentStatuses', 'frequencies'));
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $request->validate([
            'category' => 'required|string|max:255',
            'description' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
            'expense_date' => 'required|date',
            'payment_status' => 'required|in:pending,paid,overdue',
            'frequency' => 'required|in:one_time,daily,weekly,monthly,quarterly,yearly',
            'vendor' => 'nullable|string|max:255',
            'receipt_number' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        $expense = OperatingExpense::findOrFail($id);
        $expense->update($request->all());

        return redirect()->route('modules.operating-expenses.show', $id)->with('success', 'Operating Expense updated successfully.');
    }

    public function destroy(int $id): RedirectResponse
    {
        $expense = OperatingExpense::findOrFail($id);
        $expense->delete();
        return redirect()->route('modules.operating-expenses.index')->with('success', 'Operating Expense deleted.');
    }

    public function dashboard(): View
    {
        // Summary statistics for dashboard
        $totalExpenses = OperatingExpense::where('payment_status', 'paid')->sum('amount');
        $pendingExpenses = OperatingExpense::where('payment_status', 'pending')->sum('amount');
        $overdueExpenses = OperatingExpense::where('payment_status', 'overdue')->sum('amount');
        $monthlyExpenses = OperatingExpense::where('payment_status', 'paid')
            ->whereMonth('expense_date', now()->month)
            ->whereYear('expense_date', now()->year)
            ->sum('amount');

        // Category breakdown
        $expensesByCategory = OperatingExpense::getExpensesByCategoryForPeriod(
            now()->startOfMonth(),
            now()->endOfMonth()
        );

        // Recent expenses
        $recentExpenses = OperatingExpense::orderBy('created_at', 'desc')->take(10)->get();

        return view('operating-expenses::dashboard', compact(
            'totalExpenses',
            'pendingExpenses',
            'overdueExpenses',
            'monthlyExpenses',
            'expensesByCategory',
            'recentExpenses'
        ));
    }
}