<?php

namespace App\Modules\SalesOrderItem\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\SalesOrderItem\Http\Requests\StoreSalesOrderItemRequest;
use App\Modules\SalesOrderItem\Http\Requests\UpdateSalesOrderItemRequest;
use App\Modules\SalesOrderItem\Models\SalesOrderItem;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;

/**
 * Controller for managing SalesOrderItem CRUD pages and DataTables endpoint.
 */
class SalesOrderItemController extends Controller
{
    public function index(Request $request): View
    {
        return view('sales-order-item::index');
    }

    /** DataTables server-side endpoint (Yajra) */
    public function data(Request $request)
    {
        $query = SalesOrderItem::query();

        return DataTables::eloquent($query)
            ->addColumn('actions', function (SalesOrderItem $item) {
                return view('sales-order-item::partials.actions', ['id' => $item->id])->render();
            })
            ->editColumn('created_at', function (SalesOrderItem $item) {
                return $item->created_at?->toDateTimeString();
            })
            ->editColumn('updated_at', function (SalesOrderItem $item) {
                return $item->updated_at?->toDateTimeString();
            })
            ->rawColumns(['actions'])
            ->toJson();
    }

    public function create(): View
    {
        return view('sales-order-item::create');
    }

    public function store(StoreSalesOrderItemRequest $request): RedirectResponse
    {
        $item = SalesOrderItem::create($request->validated());
        return redirect()->route('modules.sales-order-item.index')->with('success', 'SalesOrderItem created.');
    }

    public function show(int $id): View
    {
        $item = SalesOrderItem::findOrFail($id);
        return view('sales-order-item::show', compact('item'));
    }

    public function edit(int $id): View
    {
        $item = SalesOrderItem::findOrFail($id);
        return view('sales-order-item::edit', compact('item'));
    }

    public function update(UpdateSalesOrderItemRequest $request, int $id): RedirectResponse
    {
        $item = SalesOrderItem::findOrFail($id);
        $item->update($request->validated());
        return redirect()->route('modules.sales-order-item.index')->with('success', 'SalesOrderItem updated.');
    }

    public function destroy(int $id): RedirectResponse
    {
        $item = SalesOrderItem::findOrFail($id);
        $item->delete();
        return redirect()->route('modules.sales-order-item.index')->with('success', 'SalesOrderItem deleted.');
    }
}
