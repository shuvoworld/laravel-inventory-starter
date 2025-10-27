<?php

namespace App\Modules\Products\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Products\Models\Product;
use App\Modules\Products\Models\ProductVariant;
use App\Modules\Products\Models\ProductVariantOption;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ProductVariantController extends Controller
{
    /**
     * Display variants for a product
     */
    public function index(Product $product)
    {
        $variants = $product->variants()
            ->with('optionValues.option')
            ->orderBy('variant_name')
            ->get();

        // If AJAX request, return JSON
        if (request()->expectsJson()) {
            return response()->json([
                'success' => true,
                'variants' => $variants
            ]);
        }

        $options = ProductVariantOption::where('store_id', auth()->user()->currentStoreId())
            ->with('values')
            ->orderBy('display_order')
            ->get();

        return view('products::variants.index', compact('product', 'variants', 'options'));
    }

    /**
     * Store a newly created variant
     */
    public function store(Request $request, Product $product)
    {
        $request->validate([
            'sku' => 'nullable|string|unique:product_variants,sku',
            'option_values' => 'required|array|min:1',
            'option_values.*' => 'exists:product_variant_option_values,id',
            'cost_price' => 'nullable|numeric|min:0',
            'minimum_profit_margin' => 'nullable|numeric|min:0',
            'standard_profit_margin' => 'nullable|numeric|min:0',
            'quantity_on_hand' => 'required|integer|min:0',
            'reorder_level' => 'nullable|integer|min:0',
            'weight' => 'nullable|numeric|min:0',
            'barcode' => 'nullable|string',
            'image' => 'nullable|image|max:2048',
            'is_default' => 'nullable|boolean',
        ]);

        try {
            DB::beginTransaction();

            $imagePath = null;
            if ($request->hasFile('image')) {
                $imagePath = $request->file('image')->store('products/variants', 'public');
            }

            $variant = ProductVariant::create([
                'store_id' => auth()->user()->currentStoreId(),
                'product_id' => $product->id,
                'sku' => $request->sku,
                'barcode' => $request->barcode,
                'cost_price' => $request->cost_price,
                'minimum_profit_margin' => $request->minimum_profit_margin,
                'standard_profit_margin' => $request->standard_profit_margin,
                'quantity_on_hand' => $request->quantity_on_hand,
                'reorder_level' => $request->reorder_level ?? $product->reorder_level,
                'weight' => $request->weight,
                'image' => $imagePath,
                'is_default' => $request->is_default ?? false,
            ]);

            // Attach option values
            $variant->optionValues()->attach($request->option_values);

            // Generate and save variant name
            $variant->variant_name = $variant->generateVariantName();
            $variant->save();

            // Mark product as having variants
            if (!$product->has_variants) {
                $product->update(['has_variants' => true]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Variant created successfully',
                'variant' => $variant->load('optionValues.option')
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error creating variant: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified variant
     */
    public function update(Request $request, Product $product, ProductVariant $variant)
    {
        $request->validate([
            'sku' => 'nullable|string|unique:product_variants,sku,' . $variant->id,
            'option_values' => 'required|array|min:1',
            'option_values.*' => 'exists:product_variant_option_values,id',
            'cost_price' => 'nullable|numeric|min:0',
            'minimum_profit_margin' => 'nullable|numeric|min:0',
            'standard_profit_margin' => 'nullable|numeric|min:0',
            'quantity_on_hand' => 'required|integer|min:0',
            'reorder_level' => 'nullable|integer|min:0',
            'weight' => 'nullable|numeric|min:0',
            'barcode' => 'nullable|string',
            'image' => 'nullable|image|max:2048',
            'is_default' => 'nullable|boolean',
            'is_active' => 'nullable|boolean',
        ]);

        try {
            DB::beginTransaction();

            $imagePath = $variant->image;
            if ($request->hasFile('image')) {
                // Delete old image
                if ($variant->image && Storage::disk('public')->exists($variant->image)) {
                    Storage::disk('public')->delete($variant->image);
                }
                $imagePath = $request->file('image')->store('products/variants', 'public');
            }

            $variant->update([
                'sku' => $request->sku,
                'barcode' => $request->barcode,
                'cost_price' => $request->cost_price,
                'minimum_profit_margin' => $request->minimum_profit_margin,
                'standard_profit_margin' => $request->standard_profit_margin,
                'quantity_on_hand' => $request->quantity_on_hand,
                'reorder_level' => $request->reorder_level,
                'weight' => $request->weight,
                'image' => $imagePath,
                'is_default' => $request->is_default ?? false,
                'is_active' => $request->is_active ?? true,
            ]);

            // Sync option values
            $variant->optionValues()->sync($request->option_values);

            // Regenerate variant name
            $variant->variant_name = $variant->generateVariantName();
            $variant->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Variant updated successfully',
                'variant' => $variant->load('optionValues.option')
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error updating variant: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified variant
     */
    public function destroy(Product $product, ProductVariant $variant)
    {
        try {
            // Check if variant has sales/purchase history
            $hasSales = $variant->salesOrderItems()->exists();
            $hasPurchases = $variant->purchaseOrderItems()->exists();

            if ($hasSales || $hasPurchases) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete variant with sales or purchase history. Consider deactivating it instead.'
                ], 400);
            }

            // Delete image if exists
            if ($variant->image && Storage::disk('public')->exists($variant->image)) {
                Storage::disk('public')->delete($variant->image);
            }

            $variant->delete();

            // If no more variants, update product
            if ($product->variants()->count() === 0) {
                $product->update(['has_variants' => false]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Variant deleted successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error deleting variant: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Bulk generate variants from option combinations
     */
    public function generateBulk(Request $request, Product $product)
    {
        $request->validate([
            'options' => 'required|array|min:1',
            'options.*.option_id' => 'required|exists:product_variant_options,id',
            'options.*.values' => 'required|array|min:1',
            'options.*.values.*' => 'required|exists:product_variant_option_values,id',
        ]);

        try {
            DB::beginTransaction();

            // Generate all combinations
            $combinations = $this->generateCombinations($request->options);

            $createdCount = 0;
            foreach ($combinations as $combination) {
                // Check if combination already exists
                $exists = ProductVariant::where('product_id', $product->id)
                    ->whereHas('optionValues', function ($query) use ($combination) {
                        $query->whereIn('option_value_id', $combination);
                    }, '=', count($combination))
                    ->exists();

                if ($exists) {
                    continue; // Skip existing combinations
                }

                $variant = ProductVariant::create([
                    'store_id' => auth()->user()->currentStoreId(),
                    'product_id' => $product->id,
                    'sku' => $this->generateVariantSku($product->sku, $createdCount),
                    // Inherit from parent
                    'cost_price' => $product->cost_price,
                    'minimum_profit_margin' => $product->minimum_profit_margin,
                    'standard_profit_margin' => $product->standard_profit_margin,
                    'quantity_on_hand' => 0,
                    'reorder_level' => $product->reorder_level,
                    'is_default' => $createdCount === 0, // First variant is default
                ]);

                $variant->optionValues()->attach($combination);
                $variant->variant_name = $variant->generateVariantName();
                $variant->save();

                $createdCount++;
            }

            if ($createdCount > 0) {
                $product->update(['has_variants' => true]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => $createdCount . ' variant(s) generated successfully',
                'count' => $createdCount
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error generating variants: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generate all combinations from options
     */
    private function generateCombinations(array $options): array
    {
        $combinations = [[]];

        foreach ($options as $option) {
            $temp = [];
            foreach ($combinations as $combination) {
                foreach ($option['values'] as $valueId) {
                    $temp[] = array_merge($combination, [$valueId]);
                }
            }
            $combinations = $temp;
        }

        return $combinations;
    }

    /**
     * Generate variant SKU
     */
    private function generateVariantSku(?string $parentSku, int $index): string
    {
        $base = $parentSku ?? 'VAR';
        return $base . '-V' . str_pad($index + 1, 3, '0', STR_PAD_LEFT);
    }

    /**
     * Get variant data for display/editing
     */
    public function show(Product $product, ProductVariant $variant)
    {
        $variant->load('optionValues.option');

        return response()->json([
            'success' => true,
            'variant' => $variant
        ]);
    }

    // API Methods

    /**
     * Display a listing of product variants (API)
     */
    public function apiIndex(Product $product)
    {
        $variants = $product->variants()
            ->with('optionValues.option')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $variants
        ]);
    }

    /**
     * Store a newly created product variant (API)
     */
    public function apiStore(Request $request, Product $product)
    {
        $request->validate([
            'sku' => 'nullable|string|unique:product_variants,sku',
            'variant_name' => 'nullable|string|max:255',
            'option_values' => 'required|array|min:1',
            'option_values.*' => 'exists:product_variant_option_values,id',
            'cost_price' => 'nullable|numeric|min:0',
            'minimum_profit_margin' => 'nullable|numeric|min:0|max:100',
            'standard_profit_margin' => 'nullable|numeric|min:0|max:100',
            'quantity_on_hand' => 'required|integer|min:0',
            'reorder_level' => 'nullable|integer|min:0',
            'weight' => 'nullable|numeric|min:0',
            'barcode' => 'nullable|string|max:255',
            'image' => 'nullable|string|max:255',
            'is_active' => 'boolean',
            'is_default' => 'boolean'
        ]);

        try {
            $variant = ProductVariant::create([
                'store_id' => auth()->user()->currentStoreId(),
                'product_id' => $product->id,
                'sku' => $request->sku,
                'variant_name' => $request->variant_name,
                'cost_price' => $request->cost_price,
                'minimum_profit_margin' => $request->minimum_profit_margin ?? $product->minimum_profit_margin,
                'standard_profit_margin' => $request->standard_profit_margin ?? $product->standard_profit_margin,
                'quantity_on_hand' => $request->quantity_on_hand,
                'reorder_level' => $request->reorder_level ?? $product->reorder_level,
                'weight' => $request->weight,
                'barcode' => $request->barcode,
                'image' => $request->image,
                'is_active' => $request->is_active ?? true,
                'is_default' => $request->is_default ?? false
            ]);

            // Attach option values
            $variant->optionValues()->attach($request->option_values);

            // Generate variant name if not provided
            if (!$request->variant_name) {
                $variant->variant_name = $variant->generateVariantName();
                $variant->save();
            }

            $variant->load('optionValues.option');

            // Mark product as having variants
            $product->update(['has_variants' => true]);

            return response()->json([
                'success' => true,
                'message' => 'Variant created successfully',
                'data' => $variant
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error creating variant: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified product variant (API)
     */
    public function apiShow(Product $product, ProductVariant $variant)
    {
        if ($variant->product_id !== $product->id) {
            return response()->json([
                'success' => false,
                'message' => 'Variant does not belong to this product'
            ], 404);
        }

        $variant->load('optionValues.option');

        return response()->json([
            'success' => true,
            'data' => $variant
        ]);
    }

    /**
     * Update the specified product variant (API)
     */
    public function apiUpdate(Request $request, Product $product, ProductVariant $variant)
    {
        if ($variant->product_id !== $product->id) {
            return response()->json([
                'success' => false,
                'message' => 'Variant does not belong to this product'
            ], 404);
        }

        $request->validate([
            'sku' => 'nullable|string|unique:product_variants,sku,' . $variant->id,
            'variant_name' => 'nullable|string|max:255',
            'option_values' => 'nullable|array|min:1',
            'option_values.*' => 'exists:product_variant_option_values,id',
            'cost_price' => 'nullable|numeric|min:0',
            'minimum_profit_margin' => 'nullable|numeric|min:0|max:100',
            'standard_profit_margin' => 'nullable|numeric|min:0|max:100',
            'quantity_on_hand' => 'nullable|integer|min:0',
            'reorder_level' => 'nullable|integer|min:0',
            'weight' => 'nullable|numeric|min:0',
            'barcode' => 'nullable|string|max:255',
            'image' => 'nullable|string|max:255',
            'is_active' => 'boolean',
            'is_default' => 'boolean'
        ]);

        try {
            $variant->update($request->only([
                'sku', 'variant_name', 'cost_price', 'minimum_profit_margin',
                'standard_profit_margin', 'quantity_on_hand', 'reorder_level',
                'weight', 'barcode', 'image', 'is_active', 'is_default'
            ]));

            // Update option values if provided
            if ($request->has('option_values')) {
                $variant->optionValues()->sync($request->option_values);
            }

            $variant->load('optionValues.option');

            return response()->json([
                'success' => true,
                'message' => 'Variant updated successfully',
                'data' => $variant
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating variant: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified product variant (API)
     */
    public function apiDestroy(Product $product, ProductVariant $variant)
    {
        if ($variant->product_id !== $product->id) {
            return response()->json([
                'success' => false,
                'message' => 'Variant does not belong to this product'
            ], 404);
        }

        try {
            // Check if variant has sales/purchase history
            $hasSalesHistory = $variant->salesOrderItems()->exists();
            $hasPurchaseHistory = $variant->purchaseOrderItems()->exists();

            if ($hasSalesHistory || $hasPurchaseHistory) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete variant with transaction history. Consider deactivating it instead.'
                ], 400);
            }

            $variant->delete();

            // Check if product has any remaining variants
            if ($product->variants()->count() === 0) {
                $product->update(['has_variants' => false]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Variant deleted successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error deleting variant: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generate variants in bulk (API)
     */
    public function apiGenerateBulk(Request $request, Product $product)
    {
        $request->validate([
            'options' => 'required|array|min:1',
            'options.*.option_id' => 'required|exists:product_variant_options,id',
            'options.*.values' => 'required|array|min:1',
            'options.*.values.*' => 'exists:product_variant_option_values,id'
        ]);

        try {
            $combinations = $this->generateCombinations($request->options);

            $variants = [];
            foreach ($combinations as $index => $combination) {
                $variant = ProductVariant::create([
                    'store_id' => auth()->user()->currentStoreId(),
                    'product_id' => $product->id,
                    'sku' => $this->generateVariantSku($product->sku, $index),
                    'variant_name' => null, // Will be generated
                    'cost_price' => $product->cost_price,
                    'minimum_profit_margin' => $product->minimum_profit_margin,
                    'standard_profit_margin' => $product->standard_profit_margin,
                    'quantity_on_hand' => 0,
                    'reorder_level' => $product->reorder_level,
                    'weight' => $product->weight,
                    'is_active' => true,
                    'is_default' => false
                ]);

                $variant->optionValues()->attach($combination);
                $variant->variant_name = $variant->generateVariantName();
                $variant->save();

                $variant->load('optionValues.option');
                $variants[] = $variant;
            }

            // Mark product as having variants
            $product->update(['has_variants' => true]);

            return response()->json([
                'success' => true,
                'message' => count($variants) . ' variants generated successfully',
                'data' => $variants
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error generating variants: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display all variants for the store (standalone variant management)
     */
    public function variantIndex()
    {
        $variants = ProductVariant::where('store_id', auth()->user()->currentStoreId())
            ->with(['product', 'optionValues.option'])
            ->orderBy('product_id')
            ->orderBy('variant_name')
            ->paginate(50);

        return view('products::variants.variant-index', compact('variants'));
    }

    /**
     * Get data for standalone variant management (AJAX)
     */
    public function variantData(Request $request)
    {
        $variants = ProductVariant::where('store_id', auth()->user()->currentStoreId())
            ->with(['product', 'optionValues.option']);

        // Apply filters
        if ($request->filled('search')) {
            $search = $request->search;
            $variants->where(function ($query) use ($search) {
                $query->where('variant_name', 'like', "%{$search}%")
                      ->orWhere('sku', 'like', "%{$search}%")
                      ->orWhereHas('product', function ($q) use ($search) {
                          $q->where('name', 'like', "%{$search}%");
                      });
            });
        }

        if ($request->filled('product_id')) {
            $variants->where('product_id', $request->product_id);
        }

        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $variants->where('is_active', true);
            } elseif ($request->status === 'inactive') {
                $variants->where('is_active', false);
            } elseif ($request->status === 'low_stock') {
                $variants->where('quantity_on_hand', '<=', DB::raw('COALESCE(reorder_level, 5)'))
                        ->where('quantity_on_hand', '>', 0);
            } elseif ($request->status === 'out_of_stock') {
                $variants->where('quantity_on_hand', 0);
            }
        }

        return datatables()->of($variants)
            ->addColumn('product_name', function ($variant) {
                return $variant->product->name;
            })
            ->addColumn('display_name', function ($variant) {
                return $variant->getDisplayName();
            })
            ->addColumn('sku', function ($variant) {
                return $variant->sku ?: '-';
            })
            ->addColumn('options', function ($variant) {
                $options = [];
                foreach ($variant->optionValues as $optionValue) {
                    $options[] = '<span class="badge bg-light text-dark">' . $optionValue->option->name . ': ' . $optionValue->value . '</span>';
                }
                return implode(' ', $options);
            })
            ->addColumn('stock_status', function ($variant) {
                if ($variant->quantity_on_hand == 0) {
                    return '<span class="badge bg-danger">Out of Stock</span>';
                } elseif ($variant->quantity_on_hand <= ($variant->reorder_level ?? 5)) {
                    return '<span class="badge bg-warning">Low Stock (' . $variant->quantity_on_hand . ')</span>';
                } else {
                    return '<span class="badge bg-success">In Stock (' . $variant->quantity_on_hand . ')</span>';
                }
            })
            ->addColumn('quantity_on_hand', function ($variant) {
                return $variant->quantity_on_hand;
            })
            ->addColumn('price', function ($variant) {
                return '$' . number_format($variant->getEffectiveTargetPrice(), 2);
            })
            ->addColumn('status', function ($variant) {
                return $variant->is_active
                    ? '<span class="badge bg-success">Active</span>'
                    : '<span class="badge bg-secondary">Inactive</span>';
            })
            ->addColumn('actions', function ($variant) {
                $actions = '';
                if (auth()->user()->can('product-variant.edit')) {
                    $actions .= '<a href="' . route('modules.product-variant.edit', $variant->id) . '" class="btn btn-sm btn-primary" title="Edit"><i class="fas fa-edit"></i></a>';
                }
                if (auth()->user()->can('product-variant.delete')) {
                    $actions .= ' <button class="btn btn-sm btn-danger delete-variant" data-id="' . $variant->id . '" title="Delete"><i class="fas fa-trash"></i></button>';
                }
                return $actions;
            })
            ->rawColumns(['options', 'stock_status', 'status', 'actions'])
            ->make(true);
    }

    /**
     * Edit variant (standalone)
     */
    public function edit(ProductVariant $variant)
    {
        // Check if user can edit this variant (same store)
        if ($variant->store_id !== auth()->user()->currentStoreId()) {
            abort(403);
        }

        $variant->load(['product', 'optionValues.option']);
        $options = ProductVariantOption::where('store_id', auth()->user()->currentStoreId())
            ->with('values')
            ->orderBy('display_order')
            ->get();

        return view('products::variants.edit', compact('variant', 'options'));
    }

    /**
     * Update variant (standalone)
     */
    public function updateStandalone(Request $request, ProductVariant $variant)
    {
        // Check if user can edit this variant (same store)
        if ($variant->store_id !== auth()->user()->currentStoreId()) {
            abort(403);
        }

        $request->validate([
            'sku' => 'nullable|string|unique:product_variants,sku,' . $variant->id,
            'variant_name' => 'required|string|max:255',
            'barcode' => 'nullable|string',
            'option_values' => 'required|array|min:1',
            'option_values.*' => 'exists:product_variant_option_values,id',
            'cost_price' => 'nullable|numeric|min:0',
            'minimum_profit_margin' => 'nullable|numeric|min:0',
            'standard_profit_margin' => 'nullable|numeric|min:0',
            'target_price' => 'nullable|numeric|min:0',
            'quantity_on_hand' => 'required|integer|min:0',
            'reorder_level' => 'nullable|integer|min:0',
            'weight' => 'nullable|numeric|min:0',
            'image' => 'nullable|image|max:2048',
            'is_default' => 'nullable|boolean',
            'is_active' => 'nullable|boolean',
        ]);

        try {
            DB::beginTransaction();

            $imagePath = $variant->image;
            if ($request->hasFile('image')) {
                // Delete old image
                if ($variant->image && Storage::disk('public')->exists($variant->image)) {
                    Storage::disk('public')->delete($variant->image);
                }
                $imagePath = $request->file('image')->store('products/variants', 'public');
            } elseif ($request->remove_image) {
                // Remove image if requested
                if ($variant->image && Storage::disk('public')->exists($variant->image)) {
                    Storage::disk('public')->delete($variant->image);
                }
                $imagePath = null;
            }

            // Update variant
            $variant->update([
                'sku' => $request->sku,
                'barcode' => $request->barcode,
                'variant_name' => $request->variant_name,
                'cost_price' => $request->cost_price,
                'minimum_profit_margin' => $request->minimum_profit_margin,
                'standard_profit_margin' => $request->standard_profit_margin,
                'target_price' => $request->target_price,
                'quantity_on_hand' => $request->quantity_on_hand,
                'reorder_level' => $request->reorder_level,
                'weight' => $request->weight,
                'image' => $imagePath,
                'is_default' => $request->boolean('is_default'),
                'is_active' => $request->boolean('is_active'),
            ]);

            // Update option values
            $variant->optionValues()->sync($request->option_values);

            // Regenerate variant name if options changed
            $variant->variant_name = $variant->generateVariantName();
            $variant->save();

            DB::commit();

            return redirect()
                ->route('modules.product-variant.index')
                ->with('success', 'Variant updated successfully');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()
                ->withInput()
                ->with('error', 'Error updating variant: ' . $e->getMessage());
        }
    }

    /**
     * Destroy variant (standalone)
     */
    public function destroyStandalone(ProductVariant $variant)
    {
        // Check if user can delete this variant (same store)
        if ($variant->store_id !== auth()->user()->currentStoreId()) {
            abort(403);
        }

        try {
            DB::beginTransaction();

            // Check if variant has sales history
            $hasSalesHistory = $variant->salesOrderItems()->exists() || $variant->purchaseOrderItems()->exists();

            if ($hasSalesHistory) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete variant with transaction history. Please deactivate it instead.'
                ], 400);
            }

            // Delete variant image
            if ($variant->image && Storage::disk('public')->exists($variant->image)) {
                Storage::disk('public')->delete($variant->image);
            }

            // Delete option value relationships
            $variant->optionValues()->detach();

            // Delete variant
            $variant->delete();

            // Check if product still has variants
            $product = $variant->product;
            if ($product->variants()->count() === 0) {
                $product->update(['has_variants' => false]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Variant deleted successfully'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error deleting variant: ' . $e->getMessage()
            ], 500);
        }
    }
}
