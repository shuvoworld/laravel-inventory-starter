<?php

namespace App\Modules\ExpenseCategory\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\ExpenseCategory\Models\ExpenseCategory;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;

class ExpenseCategoryController extends Controller
{
    public function index(): View
    {
        return view('expense-category::index');
    }

    public function data(Request $request)
    {
        $query = ExpenseCategory::query();

        return DataTables::eloquent($query)
            ->addColumn('actions', function (ExpenseCategory $item) {
                return view('expense-category::partials.actions', ['id' => $item->id])->render();
            })
            ->addColumn('status', function (ExpenseCategory $item) {
                return $item->is_active
                    ? '<span class="badge bg-success">Active</span>'
                    : '<span class="badge bg-danger">Inactive</span>';
            })
            ->editColumn('created_at', function (ExpenseCategory $item) {
                return $item->created_at->format('Y-m-d');
            })
            ->rawColumns(['actions', 'status'])
            ->toJson();
    }

    public function create(): View
    {
        return view('expense-category::create');
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
            'color' => 'nullable|string|max:7',
            'is_active' => 'boolean'
        ]);

        ExpenseCategory::create([
            'name' => $request->name,
            'description' => $request->description,
            'color' => $request->color ?? '#6B7280',
            'is_active' => $request->boolean('is_active', true)
        ]);

        return redirect()->route('modules.expense-category.index')->with('success', 'Expense category created successfully.');
    }

    public function edit(int $id): View
    {
        $category = ExpenseCategory::findOrFail($id);
        return view('expense-category::edit', compact('category'));
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
            'color' => 'nullable|string|max:7',
            'is_active' => 'boolean'
        ]);

        $category = ExpenseCategory::findOrFail($id);
        $category->update([
            'name' => $request->name,
            'description' => $request->description,
            'color' => $request->color ?? '#6B7280',
            'is_active' => $request->boolean('is_active', true)
        ]);

        return redirect()->route('modules.expense-category.index')->with('success', 'Expense category updated successfully.');
    }

    public function destroy(int $id): RedirectResponse
    {
        $category = ExpenseCategory::findOrFail($id);
        $category->delete();
        return redirect()->route('modules.expense-category.index')->with('success', 'Expense category deleted successfully.');
    }
}