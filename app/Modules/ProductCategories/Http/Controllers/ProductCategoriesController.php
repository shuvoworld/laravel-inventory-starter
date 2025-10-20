<?php

namespace App\Modules\ProductCategories\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\ProductCategories\Models\ProductCategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;

class ProductCategoriesController extends Controller
{
    public function index(): View
    {
        return view('product-categories::index');
    }

    public function data(Request $request)
    {
        $query = ProductCategory::query()->orderBy('sort_order')->orderBy('name');

        return DataTables::eloquent($query)
            ->addColumn('actions', function (ProductCategory $category) {
                return view('product-categories::partials.actions', ['id' => $category->id])->render();
            })
            ->editColumn('is_active', function (ProductCategory $category) {
                return $category->is_active
                    ? '<span class="badge bg-success">Active</span>'
                    : '<span class="badge bg-secondary">Inactive</span>';
            })
            ->addColumn('products_count', function (ProductCategory $category) {
                return $category->products()->count();
            })
            ->rawColumns(['actions', 'is_active'])
            ->toJson();
    }

    public function create(): View
    {
        return view('product-categories::create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'unique:product_categories,slug'],
            'description' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ]);

        ProductCategory::create([
            'name' => $validated['name'],
            'slug' => $validated['slug'] ?? null,
            'description' => $validated['description'] ?? null,
            'is_active' => $validated['is_active'] ?? true,
            'sort_order' => $validated['sort_order'] ?? 0,
        ]);

        return redirect()->route('modules.product-categories.index')->with('success', 'Product category created');
    }

    public function edit(int $id): View
    {
        $item = ProductCategory::findOrFail($id);

        return view('product-categories::edit', compact('item'));
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $item = ProductCategory::findOrFail($id);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'unique:product_categories,slug,' . $item->id],
            'description' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ]);

        $item->update([
            'name' => $validated['name'],
            'slug' => $validated['slug'] ?? $item->slug,
            'description' => $validated['description'] ?? null,
            'is_active' => $validated['is_active'] ?? true,
            'sort_order' => $validated['sort_order'] ?? 0,
        ]);

        return redirect()->route('modules.product-categories.index')->with('success', 'Product category updated');
    }

    public function destroy(int $id): RedirectResponse
    {
        $item = ProductCategory::findOrFail($id);

        // Check if category has products
        if ($item->products()->count() > 0) {
            return redirect()->route('modules.product-categories.index')
                ->with('error', 'Cannot delete category with associated products');
        }

        $item->delete();

        return redirect()->route('modules.product-categories.index')->with('success', 'Product category deleted');
    }
}
