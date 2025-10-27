<?php

namespace App\Modules\Products\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Products\Models\ProductVariantOption;
use App\Modules\Products\Models\ProductVariantOptionValue;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductVariantOptionController extends Controller
{
    /**
     * Display a listing of variant options
     */
    public function index()
    {
        $options = ProductVariantOption::where('store_id', auth()->user()->currentStoreId())
            ->withCount('values')
            ->orderBy('display_order')
            ->orderBy('name')
            ->get();

        return view('products::variant-options.index', compact('options'));
    }

    /**
     * Show the form for creating a new variant option
     */
    public function create()
    {
        return view('products::variant-options.create');
    }

    /**
     * Store a newly created variant option
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'display_order' => 'nullable|integer|min:0',
            'values' => 'required|array|min:1',
            'values.*.value' => 'required|string|max:100',
            'values.*.display_order' => 'nullable|integer|min:0',
        ]);

        try {
            DB::beginTransaction();

            $option = ProductVariantOption::create([
                'store_id' => auth()->user()->currentStoreId(),
                'name' => $request->name,
                'display_order' => $request->display_order ?? 0
            ]);

            foreach ($request->values as $index => $valueData) {
                ProductVariantOptionValue::create([
                    'store_id' => auth()->user()->currentStoreId(),
                    'option_id' => $option->id,
                    'value' => $valueData['value'],
                    'display_order' => $valueData['display_order'] ?? $index
                ]);
            }

            DB::commit();

            return redirect()->route('modules.products.variant-options.index')
                ->with('success', 'Variant option created successfully');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Error creating variant option: ' . $e->getMessage());
        }
    }

    /**
     * Show the form for editing the specified variant option
     */
    public function edit(ProductVariantOption $variantOption)
    {
        $this->authorize('update', $variantOption);

        $variantOption->load('values');

        return view('products::variant-options.edit', compact('variantOption'));
    }

    /**
     * Update the specified variant option
     */
    public function update(Request $request, ProductVariantOption $variantOption)
    {
        $this->authorize('update', $variantOption);

        $request->validate([
            'name' => 'required|string|max:100',
            'display_order' => 'nullable|integer|min:0',
            'values' => 'required|array|min:1',
            'values.*.id' => 'nullable|exists:product_variant_option_values,id',
            'values.*.value' => 'required|string|max:100',
            'values.*.display_order' => 'nullable|integer|min:0',
        ]);

        try {
            DB::beginTransaction();

            $variantOption->update([
                'name' => $request->name,
                'display_order' => $request->display_order ?? 0
            ]);

            // Get existing value IDs
            $existingValueIds = $variantOption->values->pluck('id')->toArray();
            $updatedValueIds = [];

            foreach ($request->values as $index => $valueData) {
                if (isset($valueData['id']) && in_array($valueData['id'], $existingValueIds)) {
                    // Update existing value
                    $value = ProductVariantOptionValue::find($valueData['id']);
                    $value->update([
                        'value' => $valueData['value'],
                        'display_order' => $valueData['display_order'] ?? $index
                    ]);
                    $updatedValueIds[] = $valueData['id'];
                } else {
                    // Create new value
                    $value = ProductVariantOptionValue::create([
                        'store_id' => auth()->user()->currentStoreId(),
                        'option_id' => $variantOption->id,
                        'value' => $valueData['value'],
                        'display_order' => $valueData['display_order'] ?? $index
                    ]);
                    $updatedValueIds[] = $value->id;
                }
            }

            // Delete removed values
            $valuesToDelete = array_diff($existingValueIds, $updatedValueIds);
            if (!empty($valuesToDelete)) {
                ProductVariantOptionValue::whereIn('id', $valuesToDelete)->delete();
            }

            DB::commit();

            return redirect()->route('modules.products.variant-options.index')
                ->with('success', 'Variant option updated successfully');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Error updating variant option: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified variant option
     */
    public function destroy(ProductVariantOption $variantOption)
    {
        $this->authorize('delete', $variantOption);

        try {
            // Check if any variants are using this option
            $usageCount = DB::table('product_variant_attribute_values')
                ->join('product_variant_option_values', 'product_variant_attribute_values.option_value_id', '=', 'product_variant_option_values.id')
                ->where('product_variant_option_values.option_id', $variantOption->id)
                ->count();

            if ($usageCount > 0) {
                return back()->with('error', 'Cannot delete this option as it is being used by ' . $usageCount . ' variant(s)');
            }

            $variantOption->delete();

            return redirect()->route('modules.products.variant-options.index')
                ->with('success', 'Variant option deleted successfully');

        } catch (\Exception $e) {
            return back()->with('error', 'Error deleting variant option: ' . $e->getMessage());
        }
    }

    /**
     * Get option values via AJAX
     */
    public function getValues(ProductVariantOption $variantOption)
    {
        $values = $variantOption->values()
            ->orderBy('display_order')
            ->get(['id', 'value', 'display_order']);

        return response()->json([
            'success' => true,
            'values' => $values
        ]);
    }

    // API Methods

    /**
     * Display a listing of variant options (API)
     */
    public function apiIndex()
    {
        $options = ProductVariantOption::with('values')
            ->where('store_id', auth()->user()->currentStoreId())
            ->orderBy('display_order')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $options
        ]);
    }

    /**
     * Store a newly created variant option (API)
     */
    public function apiStore(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'display_order' => 'nullable|integer|min:0',
            'values' => 'required|array|min:1',
            'values.*.value' => 'required|string|max:100',
            'values.*.display_order' => 'nullable|integer|min:0'
        ]);

        try {
            $option = ProductVariantOption::create([
                'store_id' => auth()->user()->currentStoreId(),
                'name' => $request->name,
                'display_order' => $request->display_order ?? 0
            ]);

            foreach ($request->values as $index => $valueData) {
                ProductVariantOptionValue::create([
                    'store_id' => auth()->user()->currentStoreId(),
                    'option_id' => $option->id,
                    'value' => $valueData['value'],
                    'display_order' => $valueData['display_order'] ?? $index
                ]);
            }

            $option->load('values');

            return response()->json([
                'success' => true,
                'message' => 'Variant option created successfully',
                'data' => $option
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error creating variant option: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified variant option (API)
     */
    public function apiShow(ProductVariantOption $variantOption)
    {
        $variantOption->load('values');

        return response()->json([
            'success' => true,
            'data' => $variantOption
        ]);
    }

    /**
     * Update the specified variant option (API)
     */
    public function apiUpdate(Request $request, ProductVariantOption $variantOption)
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'display_order' => 'nullable|integer|min:0'
        ]);

        try {
            $variantOption->update([
                'name' => $request->name,
                'display_order' => $request->display_order ?? 0
            ]);

            $variantOption->load('values');

            return response()->json([
                'success' => true,
                'message' => 'Variant option updated successfully',
                'data' => $variantOption
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating variant option: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified variant option (API)
     */
    public function apiDestroy(ProductVariantOption $variantOption)
    {
        try {
            // Check if any variants are using this option
            $usageCount = DB::table('product_variant_attribute_values')
                ->join('product_variant_option_values', 'product_variant_attribute_values.option_value_id', '=', 'product_variant_option_values.id')
                ->where('product_variant_option_values.option_id', $variantOption->id)
                ->count();

            if ($usageCount > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete this option as it is being used by ' . $usageCount . ' variant(s)'
                ], 400);
            }

            $variantOption->delete();

            return response()->json([
                'success' => true,
                'message' => 'Variant option deleted successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error deleting variant option: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get option values via API
     */
    public function apiGetValues(ProductVariantOption $variantOption)
    {
        $values = $variantOption->values()
            ->orderBy('display_order')
            ->get(['id', 'value', 'display_order']);

        return response()->json([
            'success' => true,
            'data' => $values
        ]);
    }
}
