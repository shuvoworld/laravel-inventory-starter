<?php

namespace App\Modules\Products\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Products\Models\Product;
use App\Modules\Brand\Models\Brand;
use App\Services\ImageService;
use App\Services\StockCalculationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;

class ProductController extends Controller
{
    protected ImageService $imageService;

    public function __construct(ImageService $imageService)
    {
        $this->imageService = $imageService;
    }

    public function index(): View
    {
        return view('products::index');
    }

    public function data(Request $request)
    {
        $query = Product::query();

        // Optional low-stock filter (?filter=low-stock)
        if ($request->string('filter')->lower() === 'low-stock') {
            $query->lowStock();
        }

        return DataTables::eloquent($query)
            ->addColumn('actions', function (Product $product) {
                return view('products::partials.actions', ['id' => $product->id])->render();
            })
            ->editColumn('cost_price', function (Product $product) {
                return '$'.number_format($product->cost_price, 2);
            })
            ->editColumn('price', function (Product $product) {
                return '$'.number_format($product->price, 2);
            })
            ->addColumn('current_stock', function (Product $product) {
                // Use movements-based stock calculation
                return StockCalculationService::getStockForProduct($product->id);
            })
            ->addColumn('stock_status', function (Product $product) {
                // Use movements-based stock calculation for status
                $currentStock = StockCalculationService::getStockForProduct($product->id);
                $reorderLevel = $product->reorder_level ?? 10;

                if ($currentStock <= 0) {
                    return '<span class="badge badge-danger">Out of Stock</span>';
                } elseif ($currentStock <= $reorderLevel) {
                    return '<span class="badge badge-warning">Low Stock</span>';
                } else {
                    return '<span class="badge badge-success">In Stock</span>';
                }
            })
            ->editColumn('profit_margin', function (Product $product) {
                $margin = $product->getProfitMargin();
                $color = $margin > 0 ? 'text-success' : ($margin < 0 ? 'text-danger' : 'text-muted');

                return '<span class="'.$color.'">'.number_format($margin, 2).'%</span>';
            })
            ->rawColumns(['actions', 'profit_margin', 'stock_status'])
            ->toJson();
    }

    public function create(): View
    {
        $brands = Brand::active()->orderBy('name')->get();
        return view('products::create', compact('brands'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'sku' => ['nullable', 'string', 'max:100', 'unique:products,sku'],
            'name' => ['required', 'string', 'max:255'],
            'image' => ['nullable', 'image', 'mimes:jpeg,jpg,png,gif,webp', 'max:2048'],
            'brand_id' => ['nullable', 'exists:brands,id'],
            'unit' => ['nullable', 'string', 'max:50'],
            'price' => ['nullable', 'numeric', 'min:0'],
            'cost_price' => ['nullable', 'numeric', 'min:0'],
            'reorder_level' => ['nullable', 'integer', 'min:0'],
        ]);

        // Handle image upload
        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $this->imageService->uploadProductImage($request->file('image'));
        }

        $product = Product::create([
            'sku' => $validated['sku'] ?? null,
            'name' => $validated['name'],
            'image' => $imagePath,
            'brand_id' => $validated['brand_id'] ?? null,
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

    public function show($id): View
    {
        $item = Product::findOrFail($id);

        return view('products::show', compact('item'));
    }

    public function edit($id): View
    {
        $item = Product::with('variants.optionValues.option')->findOrFail($id);
        $brands = Brand::active()->orderBy('name')->get();

        return view('products::edit', compact('item', 'brands'));
    }

    public function update(Request $request, $id): RedirectResponse
    {
        $item = Product::findOrFail($id);

        $validated = $request->validate([
            'sku' => ['nullable', 'string', 'max:100', 'unique:products,sku,'.$item->id],
            'name' => ['required', 'string', 'max:255'],
            'image' => ['nullable', 'image', 'mimes:jpeg,jpg,png,gif,webp', 'max:2048'],
            'remove_image' => ['nullable', 'boolean'],
            'brand_id' => ['nullable', 'exists:brands,id'],
            'unit' => ['nullable', 'string', 'max:50'],
            'price' => ['nullable', 'numeric', 'min:0'],
            'cost_price' => ['nullable', 'numeric', 'min:0'],
            'minimum_profit_margin' => ['required', 'numeric', 'min:0'],
            'standard_profit_margin' => ['required', 'numeric', 'min:0'],
            'reorder_level' => ['nullable', 'integer', 'min:0'],
            'has_variants' => ['nullable', 'boolean'],
        ]);

        // Handle image removal
        if ($request->boolean('remove_image')) {
            $this->imageService->deleteProductImage($item->image);
            $item->image = null;
        }

        // Handle new image upload
        if ($request->hasFile('image')) {
            $imagePath = $this->imageService->uploadProductImage($request->file('image'), $item->image);
            $item->image = $imagePath;
        }

        $item->update([
            'sku' => $validated['sku'] ?? null,
            'name' => $validated['name'],
            'image' => $item->image,
            'brand_id' => $validated['brand_id'] ?? null,
            'unit' => $validated['unit'] ?? null,
            'price' => $validated['price'] ?? 0,
            'cost_price' => $validated['cost_price'] ?? 0,
            'minimum_profit_margin' => $validated['minimum_profit_margin'],
            'standard_profit_margin' => $validated['standard_profit_margin'],
            'reorder_level' => $validated['reorder_level'] ?? 0,
            'has_variants' => $request->boolean('has_variants'),
        ]);

        // Calculate profit margin
        $item->calculateProfitMargin();

        return redirect()->route('modules.products.index')->with('success', 'Product updated');
    }

    public function destroy($id): RedirectResponse
    {
        $item = Product::findOrFail($id);

        // Delete product image
        $this->imageService->deleteProductImage($item->image);

        $item->delete();

        return redirect()->route('modules.products.index')->with('success', 'Product deleted');
    }
}
