<?php

namespace App\Modules\Types\Http\Controllers;

use App\Modules\Types\Http\Requests\StoreTypeRequest;
use App\Modules\Types\Http\Requests\UpdateTypeRequest;
use App\Modules\Types\Models\Type;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;

class TypeController
{
    public function index(Request $request): View
    {
        return view('types::index');
    }

    /** DataTables server-side endpoint (Yajra) */
    public function data(Request $request): Response
    {
        $query = Type::query();

        $dt = DataTables::eloquent($query)
            ->addColumn('actions', function ($row) {
                return view('types::partials.actions', ['id' => $row->id])->render();
            })
            ->editColumn('created_at', function ($row) {
                return optional($row->created_at)->toDateTimeString();
            })
            ->editColumn('updated_at', function ($row) {
                return optional($row->updated_at)->toDateTimeString();
            })
            ->rawColumns(['actions'])
            ->toJson();

        return response($dt->getData(true));
    }

    public function create(): View
    {
        return view('types::create');
    }

    public function store(StoreTypeRequest $request): RedirectResponse
    {
        Type::create($request->validated());
        return redirect()->route('modules.types.index')->with('status', 'Type created.');
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
        return redirect()->route('modules.types.index')->with('status', 'Type updated.');
    }

    public function destroy(int $id): RedirectResponse
    {
        $item = Type::findOrFail($id);
        $item->delete();
        return redirect()->route('modules.types.index')->with('status', 'Type deleted.');
    }
}
