<?php

namespace App\Modules\Types\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Types\Http\Requests\StoreTypeRequest;
use App\Modules\Types\Http\Requests\UpdateTypeRequest;
use App\Modules\Types\Models\Type;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;

/**
 * Controller for managing Types CRUD and DataTables endpoint.
 */
class TypeController extends Controller
{
    public function index(Request $request): View
    {
        return view('types::index');
    }

    /** DataTables server-side endpoint (Yajra) */
    public function data(Request $request)
    {
        $query = Type::query();

        return DataTables::eloquent($query)
            ->addColumn('actions', function (Type $type) {
                return view('types::partials.actions', ['id' => $type->id])->render();
            })
            ->editColumn('created_at', function (Type $type) {
                return $type->created_at?->toDateTimeString();
            })
            ->editColumn('updated_at', function (Type $type) {
                return $type->updated_at?->toDateTimeString();
            })
            ->rawColumns(['actions'])
            ->toJson();
    }

    public function create(): View
    {
        return view('types::create');
    }

    public function store(StoreTypeRequest $request): RedirectResponse
    {
        Type::create($request->validated());
        return redirect()->route('modules.types.index')->with('success', 'Type created.');
    }

    public function show(int $id): View
    {
        $item = Type::findOrFail($id);
        return view('types::show', compact('item'));
    }

    public function edit(int $id): View
    {
        $item = Type::findOrFail($id);
        return view('types::edit', compact('item'));
    }

    public function update(UpdateTypeRequest $request, int $id): RedirectResponse
    {
        $item = Type::findOrFail($id);
        $item->update($request->validated());
        return redirect()->route('modules.types.index')->with('success', 'Type updated.');
    }

    public function destroy(int $id): RedirectResponse
    {
        $item = Type::findOrFail($id);
        $item->delete();
        return redirect()->route('modules.types.index')->with('success', 'Type deleted.');
    }
}
