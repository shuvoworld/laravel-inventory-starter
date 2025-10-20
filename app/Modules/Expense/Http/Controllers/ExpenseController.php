<?php

namespace App\Modules\Expense\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Expense\Models\Expense;
use App\Modules\ExpenseCategory\Models\ExpenseCategory;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;

class ExpenseController extends Controller
{
    public function index(): View
    {
        return view('expense::index');
    }

    public function data(Request $request)
    {
        $query = Expense::with(['category', 'store'])
                    ->forCurrentStore();

        return DataTables::eloquent($query)
            ->addColumn('category_name', function (Expense $item) {
                return $item->category ? $item->category->name : 'N/A';
            })
            ->addColumn('amount_formatted', function (Expense $item) {
                return '$' . number_format($item->amount, 2);
            })
            ->addColumn('actions', function (Expense $item) {
                return view('expense::partials.actions', ['id' => $item->id])->render();
            })
            ->addColumn('status', function (Expense $item) {
                $badges = [
                    'pending' => 'badge-warning',
                    'active' => 'badge-success',
                    'completed' => 'badge-info',
                ];
                $class = $badges[$item->status] ?? 'badge-secondary';
                return "<span class='badge {$class}'>" . ucfirst($item->status) . "</span>";
            })
            ->editColumn('expense_date', function (Expense $item) {
                return $item->expense_date->format('Y-m-d');
            })
            ->addColumn('payment_method', function (Expense $item) {
                $methods = [
                    'cash' => 'Cash',
                    'card' => 'Card',
                    'bank_transfer' => 'Bank Transfer',
                    'cheque' => 'Cheque',
                    'mobile_banking' => 'Mobile Banking'
                ];
                return $methods[$item->payment_method] ?? ucfirst($item->payment_method);
            })
            ->rawColumns(['actions', 'status'])
            ->toJson();
    }

    public function create(): View
    {
        $categories = ExpenseCategory::where('is_active', true)->orderBy('name')->get();
        $paymentMethods = $this->getPaymentMethods();
        return view('expense::create', compact('categories', 'paymentMethods'));
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'expense_category_id' => 'nullable|exists:expense_categories,id',
            'reference_number' => 'nullable|string|max:255',
            'expense_date' => 'required|date',
            'amount' => 'required|numeric|min:0|max:999999.99',
            'description' => 'required|string|max:500',
            'payment_method' => 'nullable|in:cash,card,bank_transfer,cheque,mobile_banking',
            'receipt' => 'nullable|string|max:255',
            'notes' => 'nullable|string|max:1000'
        ]);

        Expense::create([
            'store_id' => auth()->user()->currentStoreId(),
            'expense_category_id' => $request->expense_category_id,
            'reference_number' => $request->reference_number,
            'expense_date' => $request->expense_date,
            'amount' => $request->amount,
            'description' => $request->description,
            'payment_method' => $request->payment_method,
            'receipt' => $request->receipt,
            'notes' => $request->notes,
            'status' => 'active'
        ]);

        return redirect()->route('modules.expenses.index')->with('success', 'Expense recorded successfully.');
    }

    public function edit(int $id): View
    {
        $expense = Expense::with(['category'])->findOrFail($id);
        $categories = ExpenseCategory::where('is_active', true)->orderBy('name')->get();
        $paymentMethods = $this->getPaymentMethods();
        return view('expense::edit', compact('expense', 'categories', 'paymentMethods'));
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $request->validate([
            'expense_category_id' => 'nullable|exists:expense_categories,id',
            'reference_number' => 'nullable|string|max:255',
            'expense_date' => 'required|date',
            'amount' => 'required|numeric|min:0|max:999999.99',
            'description' => 'required|string|max:500',
            'payment_method' => 'nullable|in:cash,card,bank_transfer,cheque,mobile_banking',
            'receipt' => 'nullable|string|max:255',
            'notes' => 'nullable|string|max:1000'
        ]);

        $expense = Expense::findOrFail($id);
        $expense->update([
            'expense_category_id' => $request->expense_category_id,
            'reference_number' => $request->reference_number,
            'expense_date' => $request->expense_date,
            'amount' => $request->amount,
            'description' => $request->description,
            'payment_method' => $request->payment_method,
            'receipt' => $request->receipt,
            'notes' => $request->notes,
        ]);

        return redirect()->route('modules.expenses.index')->with('success', 'Expense updated successfully.');
    }

    public function show(int $id): View
    {
        $expense = Expense::with(['category', 'store'])->findOrFail($id);
        return view('expense::show', compact('expense'));
    }

    public function destroy(int $id): RedirectResponse
    {
        $expense = Expense::findOrFail($id);
        $expense->delete();
        return redirect()->route('modules.expenses.index')->with('success', 'Expense deleted successfully.');
    }

    private function getPaymentMethods(): array
    {
        return [
            'cash' => 'Cash',
            'card' => 'Card',
            'bank_transfer' => 'Bank Transfer',
            'cheque' => 'Cheque',
            'mobile_banking' => 'Mobile Banking'
        ];
    }
}