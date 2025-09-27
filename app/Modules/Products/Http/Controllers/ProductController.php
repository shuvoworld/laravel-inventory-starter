<?php

namespace App\Modules\Products\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Products\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;

class ProductController extends Controller
{
    public function index(): View
    {
        return view('products::index');
    }

    public function data(Request $request)
    {
        $query = Product::query();

        return DataTables::eloquent($query)
            ->addColumn('actions', function (Product $product) {
                return view('products::partials.actions', ['id' => $product->id])->render();
            })
            ->editColumn('cost_price', function (Product $product) {
                return '$' . number_format($product->cost_price, 2);
            })
            ->editColumn('price', function (Product $product) {
                return '$' . number_format($product->price, 2);
            })
            ->editColumn('profit_margin', function (Product $product) {
                $margin = $product->getProfitMargin();
                $color = $margin > 0 ? 'text-success' : ($margin < 0 ? 'text-danger' : 'text-muted');
                return '<span class="' . $color . '">' . number_format($margin, 2) . '%</span>';
            })
            ->rawColumns(['actions', 'profit_margin'])
            ->toJson();
    }

    public function create(): View
    {
        return view('products::create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'sku' => ['nullable', 'string', 'max:100', 'unique:products,sku'],
            'name' => ['required', 'string', 'max:255'],
            'unit' => ['nullable', 'string', 'max:50'],
            'price' => ['nullable', 'numeric', 'min:0'],
            'cost_price' => ['nullable', 'numeric', 'min:0'],
            'reorder_level' => ['nullable', 'integer', 'min:0'],
        ]);

        $product = Product::create([
            'sku' => $validated['sku'] ?? null,
            'name' => $validated['name'],
            'unit' => $validated['unit'] ?? null,
            'price' => $validated['price'] ?? 0,
            'cost_price' => $validated['cost_price'] ?? 0,
            'quantity_on_hand' => 0,
            'reorder_level' => $validated['reorder_level'] ?? 0,
        ]);

        // Calculate profit margin
        $product->calculateProfitMargin();

        return redirect()->route('modules.products.index')->with('success', 'Product created');
    }

    public function show(int $id): View
    {
        $item = Product::findOrFail($id);

        return view('products::show', compact('item'));
    }

    public function edit(int $id): View
    {
        $item = Product::findOrFail($id);

        return view('products::edit', compact('item'));
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $item = Product::findOrFail($id);

        $validated = $request->validate([
            'sku' => ['nullable', 'string', 'max:100', 'unique:products,sku,'.$item->id],
            'name' => ['required', 'string', 'max:255'],
            'unit' => ['nullable', 'string', 'max:50'],
            'price' => ['nullable', 'numeric', 'min:0'],
            'cost_price' => ['nullable', 'numeric', 'min:0'],
            'reorder_level' => ['nullable', 'integer', 'min:0'],
        ]);

        $item->update([
            'sku' => $validated['sku'] ?? null,
            'name' => $validated['name'],
            'unit' => $validated['unit'] ?? null,
            'price' => $validated['price'] ?? 0,
            'cost_price' => $validated['cost_price'] ?? 0,
            'reorder_level' => $validated['reorder_level'] ?? 0,
        ]);

        // Calculate profit margin
        $item->calculateProfitMargin();

        return redirect()->route('modules.products.index')->with('success', 'Product updated');
    }

    public function destroy(int $id): RedirectResponse
    {
        $item = Product::findOrFail($id);
        $item->delete();

        return redirect()->route('modules.products.index')->with('success', 'Product deleted');
    }
}
