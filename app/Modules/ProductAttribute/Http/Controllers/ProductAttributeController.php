<?php

namespace App\Modules\ProductAttribute\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\ProductAttribute\Http\Requests\StoreProductAttributeRequest;
use App\Modules\ProductAttribute\Http\Requests\UpdateProductAttributeRequest;
use App\Modules\ProductAttribute\Models\ProductAttribute;
use App\Modules\AttributeSet\Models\AttributeSet;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;

/**
 * Controller for managing ProductAttribute CRUD pages and DataTables endpoint.
 */
class ProductAttributeController extends Controller
{
    public function index(Request $request): View
    {
        return view('product-attribute::index');
    }

    /** DataTables server-side endpoint (Yajra) */
    public function data(Request $request)
    {
        $query = ProductAttribute::with('attributeSet');

        return DataTables::eloquent($query)
            ->addColumn('attribute_set_name', function (ProductAttribute $item) {
                return $item->attributeSet ? $item->attributeSet->name : '-';
            })
            ->addColumn('values_count', function (ProductAttribute $item) {
                return $item->values()->count();
            })
            ->addColumn('actions', function (ProductAttribute $item) {
                return view('product-attribute::partials.actions', ['id' => $item->id])->render();
            })
            ->editColumn('created_at', function (ProductAttribute $item) {
                return $item->created_at?->format('Y-m-d H:i');
            })
            ->rawColumns(['actions'])
            ->toJson();
    }

    public function create(): View
    {
        $attributeSets = AttributeSet::active()->orderBy('name')->get();
        return view('product-attribute::create', compact('attributeSets'));
    }

    public function store(StoreProductAttributeRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $item = ProductAttribute::create([
            'name' => $data['name'],
            'attribute_set_id' => $data['attribute_set_id'] ?? null,
        ]);

        // Create attribute values
        $values = collect($data['values'] ?? [])->filter(fn ($v) => filled($v))->unique();
        foreach ($values as $val) {
            $item->values()->firstOrCreate(['value' => $val]);
        }

        return redirect()->route('modules.product-attribute.index')->with('success', 'Attribute created.');
    }

    public function show(int $id): View
    {
        $item = ProductAttribute::findOrFail($id);
        return view('product-attribute::show', compact('item'));
    }

    public function edit(int $id): View
    {
        $item = ProductAttribute::findOrFail($id);
        $attributeSets = AttributeSet::active()->orderBy('name')->get();
        return view('product-attribute::edit', compact('item', 'attributeSets'));
    }

    public function update(UpdateProductAttributeRequest $request, int $id): RedirectResponse
    {
        $item = ProductAttribute::findOrFail($id);
        $data = $request->validated();

        $item->update([
            'name' => $data['name'],
            'attribute_set_id' => $data['attribute_set_id'] ?? null,
        ]);

        // Sync attribute values
        $incoming = collect($data['values'] ?? [])->filter(fn ($v) => filled($v))->unique()->values();
        $existing = $item->values()->pluck('value', 'id');

        // Delete removed
        $toDeleteIds = $existing->filter(fn ($v) => !$incoming->contains($v))->keys();
        if ($toDeleteIds->isNotEmpty()) {
            $item->values()->whereIn('id', $toDeleteIds)->delete();
        }
        // Add new
        foreach ($incoming as $val) {
            if (!$existing->contains($val)) {
                $item->values()->create(['value' => $val]);
            }
        }

        return redirect()->route('modules.product-attribute.index')->with('success', 'Attribute updated.');
    }

    public function destroy(int $id): RedirectResponse
    {
        $item = ProductAttribute::findOrFail($id);
        $item->delete();
        return redirect()->route('modules.product-attribute.index')->with('success', 'ProductAttribute deleted.');
    }
}
