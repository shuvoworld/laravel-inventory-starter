<?php

namespace App\Modules\AttributeSet\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\AttributeSet\Models\AttributeSet;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;

class AttributeSetController extends Controller
{
    public function index(): View
    {
        return view('attribute-set::index');
    }

    public function data(Request $request)
    {
        $query = AttributeSet::query();

        return DataTables::eloquent($query)
            ->addColumn('status_badge', function (AttributeSet $item) {
                $class = $item->is_active ? 'badge-success' : 'badge-secondary';
                $text = $item->is_active ? 'Active' : 'Inactive';
                return "<span class='badge {$class}'>{$text}</span>";
            })
            ->addColumn('actions', function (AttributeSet $item) {
                return view('attribute-set::partials.actions', ['id' => $item->id])->render();
            })
            ->rawColumns(['actions', 'status_badge'])
            ->toJson();
    }

    public function create(): View
    {
        return view('attribute-set::create');
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        AttributeSet::create([
            'store_id' => auth()->user()->currentStoreId(),
            'name' => $request->name,
            'description' => $request->description,
            'is_active' => $request->has('is_active'),
        ]);

        return redirect()->route('modules.attribute-set.index')->with('success', 'Attribute Set created successfully.');
    }

    public function show(int $id): View
    {
        $item = AttributeSet::with('attributes')->findOrFail($id);
        return view('attribute-set::show', compact('item'));
    }

    public function edit(int $id): View
    {
        $item = AttributeSet::findOrFail($id);
        return view('attribute-set::edit', compact('item'));
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $item = AttributeSet::findOrFail($id);
        $item->update([
            'name' => $request->name,
            'description' => $request->description,
            'is_active' => $request->has('is_active'),
        ]);

        return redirect()->route('modules.attribute-set.index')->with('success', 'Attribute Set updated successfully.');
    }

    public function destroy(int $id): RedirectResponse
    {
        $item = AttributeSet::findOrFail($id);
        $item->delete();
        return redirect()->route('modules.attribute-set.index')->with('success', 'Attribute Set deleted.');
    }
}
