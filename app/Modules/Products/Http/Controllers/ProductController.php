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
            ->rawColumns(['actions'])
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
            'reorder_level' => ['nullable', 'integer', 'min:0'],
        ]);

        $product = Product::create([
            'sku' => $validated['sku'] ?? null,
            'name' => $validated['name'],
            'unit' => $validated['unit'] ?? null,
            'price' => $validated['price'] ?? 0,
            'quantity_on_hand' => 0,
            'reorder_level' => $validated['reorder_level'] ?? 0,
        ]);

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
            'reorder_level' => ['nullable', 'integer', 'min:0'],
        ]);

        $item->update([
            'sku' => $validated['sku'] ?? null,
            'name' => $validated['name'],
            'unit' => $validated['unit'] ?? null,
            'price' => $validated['price'] ?? 0,
            'reorder_level' => $validated['reorder_level'] ?? 0,
        ]);

        return redirect()->route('modules.products.index')->with('success', 'Product updated');
    }

    public function destroy(int $id): RedirectResponse
    {
        $item = Product::findOrFail($id);
        $item->delete();

        return redirect()->route('modules.products.index')->with('success', 'Product deleted');
    }
}
