<?php

namespace App\Modules\PurchaseOrderItem\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\PurchaseOrderItem\Http\Requests\StorePurchaseOrderItemRequest;
use App\Modules\PurchaseOrderItem\Http\Requests\UpdatePurchaseOrderItemRequest;
use App\Modules\PurchaseOrderItem\Models\PurchaseOrderItem;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;

/**
 * Controller for managing PurchaseOrderItem CRUD pages and DataTables endpoint.
 */
class PurchaseOrderItemController extends Controller
{
    public function index(Request $request): View
    {
        return view('purchase-order-item::index');
    }

    /** DataTables server-side endpoint (Yajra) */
    public function data(Request $request)
    {
        $query = PurchaseOrderItem::query();

        return DataTables::eloquent($query)
            ->addColumn('actions', function (PurchaseOrderItem $item) {
                return view('purchase-order-item::partials.actions', ['id' => $item->id])->render();
            })
            ->editColumn('created_at', function (PurchaseOrderItem $item) {
                return $item->created_at?->toDateTimeString();
            })
            ->editColumn('updated_at', function (PurchaseOrderItem $item) {
                return $item->updated_at?->toDateTimeString();
            })
            ->rawColumns(['actions'])
            ->toJson();
    }

    public function create(): View
    {
        return view('purchase-order-item::create');
    }

    public function store(StorePurchaseOrderItemRequest $request): RedirectResponse
    {
        $item = PurchaseOrderItem::create($request->validated());
        return redirect()->route('modules.purchase-order-item.index')->with('success', 'PurchaseOrderItem created.');
    }

    public function show(int $id): View
    {
        $item = PurchaseOrderItem::findOrFail($id);
        return view('purchase-order-item::show', compact('item'));
    }

    public function edit(int $id): View
    {
        $item = PurchaseOrderItem::findOrFail($id);
        return view('purchase-order-item::edit', compact('item'));
    }

    public function update(UpdatePurchaseOrderItemRequest $request, int $id): RedirectResponse
    {
        $item = PurchaseOrderItem::findOrFail($id);
        $item->update($request->validated());
        return redirect()->route('modules.purchase-order-item.index')->with('success', 'PurchaseOrderItem updated.');
    }

    public function destroy(int $id): RedirectResponse
    {
        $item = PurchaseOrderItem::findOrFail($id);
        $item->delete();
        return redirect()->route('modules.purchase-order-item.index')->with('success', 'PurchaseOrderItem deleted.');
    }
}
