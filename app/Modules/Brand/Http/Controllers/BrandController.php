<?php

namespace App\Modules\Brand\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Brand\Models\Brand;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;

class BrandController extends Controller
{
    public function index(): View
    {
        return view('brand::index');
    }

    public function data(Request $request)
    {
        $query = Brand::query();

        return DataTables::eloquent($query)
            ->addColumn('status_badge', function (Brand $item) {
                $class = $item->is_active ? 'badge-success' : 'badge-secondary';
                $text = $item->is_active ? 'Active' : 'Inactive';
                return "<span class='badge {$class}'>{$text}</span>";
            })
            ->addColumn('actions', function (Brand $item) {
                return view('brand::partials.actions', ['id' => $item->id])->render();
            })
            ->rawColumns(['actions', 'status_badge'])
            ->toJson();
    }

    public function create(): View
    {
        return view('brand::create');
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:brands,slug',
            'description' => 'nullable|string',
            'website' => 'nullable|url|max:255',
            'is_active' => 'boolean',
        ]);

        Brand::create([
            'name' => $request->name,
            'slug' => $request->slug,
            'description' => $request->description,
            'website' => $request->website,
            'is_active' => $request->has('is_active'),
        ]);

        return redirect()->route('modules.brand.index')->with('success', 'Brand created successfully.');
    }

    public function show(int $id): View
    {
        $item = Brand::with('products')->findOrFail($id);
        return view('brand::show', compact('item'));
    }

    public function edit(int $id): View
    {
        $item = Brand::findOrFail($id);
        return view('brand::edit', compact('item'));
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $item = Brand::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:brands,slug,' . $id,
            'description' => 'nullable|string',
            'website' => 'nullable|url|max:255',
            'is_active' => 'boolean',
        ]);

        $item->update([
            'name' => $request->name,
            'slug' => $request->slug,
            'description' => $request->description,
            'website' => $request->website,
            'is_active' => $request->has('is_active'),
        ]);

        return redirect()->route('modules.brand.index')->with('success', 'Brand updated successfully.');
    }

    public function destroy(int $id): RedirectResponse
    {
        $item = Brand::findOrFail($id);
        $item->delete();
        return redirect()->route('modules.brand.index')->with('success', 'Brand deleted.');
    }
}
