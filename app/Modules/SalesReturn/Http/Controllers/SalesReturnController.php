<?php

namespace App\Modules\SalesReturn\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\SalesReturn\Models\SalesReturn;
use App\Modules\SalesReturn\Models\SalesReturnItem;
use App\Modules\SalesOrder\Models\SalesOrder;
use App\Modules\StockMovement\Models\StockMovement;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class SalesReturnController extends Controller
{
    public function index(Request $request): View
    {
        return view('sales-return::index');
    }

    public function data(Request $request)
    {
        $query = SalesReturn::with(['customer', 'salesOrder', 'items']);

        return DataTables::eloquent($query)
            ->addColumn('customer_name', function (SalesReturn $item) {
                return $item->customer->name ?? 'N/A';
            })
            ->addColumn('sales_order_number', function (SalesReturn $item) {
                return $item->salesOrder->order_number ?? 'N/A';
            })
            ->addColumn('items_count', function (SalesReturn $item) {
                return $item->items->count();
            })
            ->addColumn('status_badge', function (SalesReturn $item) {
                $badges = [
                    'pending' => 'badge-warning',
                    'approved' => 'badge-info',
                    'processed' => 'badge-success',
                    'rejected' => 'badge-danger'
                ];
                $class = $badges[$item->status] ?? 'badge-secondary';
                return "<span class='badge {$class}'>" . ucfirst($item->status) . "</span>";
            })
            ->addColumn('actions', function (SalesReturn $item) {
                return view('sales-return::partials.actions', ['id' => $item->id])->render();
            })
            ->editColumn('return_date', function (SalesReturn $item) {
                return $item->return_date?->format('Y-m-d');
            })
            ->editColumn('total_amount', function (SalesReturn $item) {
                return '$' . number_format($item->total_amount, 2);
            })
            ->rawColumns(['actions', 'status_badge'])
            ->toJson();
    }

    public function create(Request $request): View
    {
        $salesOrderId = $request->get('sales_order_id');
        $salesOrder = null;

        if ($salesOrderId) {
            $salesOrder = SalesOrder::with(['customer', 'items.product'])->findOrFail($salesOrderId);
        }

        return view('sales-return::create', compact('salesOrder'));
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'sales_order_id' => 'required|exists:sales_orders,id',
            'return_date' => 'required|date',
            'reason' => 'required|in:defective,damaged,wrong_item,customer_request,other',
            'items' => 'required|array|min:1',
            'items.*.sales_order_item_id' => 'required|exists:sales_order_items,id',
            'items.*.quantity_returned' => 'required|integer|min:1',
            'notes' => 'nullable|string',
        ]);

        DB::transaction(function () use ($request) {
            $salesOrder = SalesOrder::with('items')->findOrFail($request->sales_order_id);

            // Calculate totals
            $subtotal = 0;
            foreach ($request->items as $itemData) {
                $orderItem = $salesOrder->items->find($itemData['sales_order_item_id']);
                $subtotal += $itemData['quantity_returned'] * $orderItem->unit_price;
            }

            // Create sales return
            $salesReturn = SalesReturn::create([
                'sales_order_id' => $request->sales_order_id,
                'customer_id' => $salesOrder->customer_id,
                'return_date' => $request->return_date,
                'reason' => $request->reason,
                'status' => 'pending',
                'subtotal' => $subtotal,
                'tax_amount' => 0,
                'discount_amount' => 0,
                'total_amount' => $subtotal,
                'notes' => $request->notes,
            ]);

            // Create return items and update stock
            foreach ($request->items as $itemData) {
                $orderItem = $salesOrder->items->find($itemData['sales_order_item_id']);

                // Create sales return item
                SalesReturnItem::create([
                    'sales_return_id' => $salesReturn->id,
                    'sales_order_item_id' => $itemData['sales_order_item_id'],
                    'product_id' => $orderItem->product_id,
                    'quantity_returned' => $itemData['quantity_returned'],
                    'unit_price' => $orderItem->unit_price,
                    'total_price' => $itemData['quantity_returned'] * $orderItem->unit_price,
                ]);

                // Create stock movement (inbound - returned to inventory)
                StockMovement::create([
                    'product_id' => $orderItem->product_id,
                    'type' => 'in',
                    'quantity' => $itemData['quantity_returned'],
                    'reference_type' => 'sales_return',
                    'reference_id' => $salesReturn->id,
                    'notes' => "Return - {$salesReturn->return_number}",
                ]);
            }
        });

        return redirect()->route('modules.sales-return.index')->with('success', 'Sales Return created successfully.');
    }

    public function show(int $id): View
    {
        $item = SalesReturn::with(['customer', 'salesOrder', 'items.product', 'items.salesOrderItem'])->findOrFail($id);
        return view('sales-return::show', compact('item'));
    }

    public function edit(int $id): View
    {
        $item = SalesReturn::with(['customer', 'salesOrder', 'items.product', 'items.salesOrderItem'])->findOrFail($id);
        return view('sales-return::edit', compact('item'));
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $request->validate([
            'return_date' => 'required|date',
            'status' => 'required|in:pending,approved,processed,rejected',
            'reason' => 'required|in:defective,damaged,wrong_item,customer_request,other',
            'notes' => 'nullable|string',
        ]);

        $salesReturn = SalesReturn::findOrFail($id);
        $salesReturn->update([
            'return_date' => $request->return_date,
            'status' => $request->status,
            'reason' => $request->reason,
            'notes' => $request->notes,
        ]);

        return redirect()->route('modules.sales-return.show', $id)->with('success', 'Sales Return updated successfully.');
    }

    public function destroy(int $id): RedirectResponse
    {
        $item = SalesReturn::findOrFail($id);
        $item->delete();
        return redirect()->route('modules.sales-return.index')->with('success', 'Sales Return deleted.');
    }
}