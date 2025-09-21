<?php

namespace App\Modules\PurchaseReturn\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\PurchaseReturn\Models\PurchaseReturn;
use App\Modules\PurchaseReturn\Models\PurchaseReturnItem;
use App\Modules\PurchaseOrder\Models\PurchaseOrder;
use App\Modules\StockMovement\Models\StockMovement;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class PurchaseReturnController extends Controller
{
    public function index(Request $request): View
    {
        return view('purchase-return::index');
    }

    public function data(Request $request)
    {
        $query = PurchaseReturn::with(['purchaseOrder', 'items']);

        return DataTables::eloquent($query)
            ->addColumn('purchase_order_number', function (PurchaseReturn $item) {
                return $item->purchaseOrder->po_number ?? 'N/A';
            })
            ->addColumn('items_count', function (PurchaseReturn $item) {
                return $item->items->count();
            })
            ->addColumn('status_badge', function (PurchaseReturn $item) {
                $badges = [
                    'pending' => 'badge-warning',
                    'approved' => 'badge-info',
                    'processed' => 'badge-success',
                    'rejected' => 'badge-danger'
                ];
                $class = $badges[$item->status] ?? 'badge-secondary';
                return "<span class='badge {$class}'>" . ucfirst($item->status) . "</span>";
            })
            ->addColumn('actions', function (PurchaseReturn $item) {
                return view('purchase-return::partials.actions', ['id' => $item->id])->render();
            })
            ->editColumn('return_date', function (PurchaseReturn $item) {
                return $item->return_date?->format('Y-m-d');
            })
            ->editColumn('total_amount', function (PurchaseReturn $item) {
                return '$' . number_format($item->total_amount, 2);
            })
            ->rawColumns(['actions', 'status_badge'])
            ->toJson();
    }

    public function create(Request $request): View
    {
        $purchaseOrderId = $request->get('purchase_order_id');
        $purchaseOrder = null;

        if ($purchaseOrderId) {
            $purchaseOrder = PurchaseOrder::with(['items.product'])->findOrFail($purchaseOrderId);
        }

        return view('purchase-return::create', compact('purchaseOrder'));
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'purchase_order_id' => 'required|exists:purchase_orders,id',
            'return_date' => 'required|date',
            'reason' => 'required|in:defective,damaged,wrong_item,quality_issue,other',
            'items' => 'required|array|min:1',
            'items.*.purchase_order_item_id' => 'required|exists:purchase_order_items,id',
            'items.*.quantity_returned' => 'required|integer|min:1',
            'notes' => 'nullable|string',
        ]);

        DB::transaction(function () use ($request) {
            $purchaseOrder = PurchaseOrder::with('items')->findOrFail($request->purchase_order_id);

            // Calculate totals
            $subtotal = 0;
            foreach ($request->items as $itemData) {
                $orderItem = $purchaseOrder->items->find($itemData['purchase_order_item_id']);
                $subtotal += $itemData['quantity_returned'] * $orderItem->unit_price;
            }

            // Create purchase return
            $purchaseReturn = PurchaseReturn::create([
                'purchase_order_id' => $request->purchase_order_id,
                'supplier_name' => $purchaseOrder->supplier_name,
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
                $orderItem = $purchaseOrder->items->find($itemData['purchase_order_item_id']);

                // Create purchase return item
                PurchaseReturnItem::create([
                    'purchase_return_id' => $purchaseReturn->id,
                    'purchase_order_item_id' => $itemData['purchase_order_item_id'],
                    'product_id' => $orderItem->product_id,
                    'quantity_returned' => $itemData['quantity_returned'],
                    'unit_price' => $orderItem->unit_price,
                    'total_price' => $itemData['quantity_returned'] * $orderItem->unit_price,
                ]);

                // Create stock movement (outbound - removed from inventory)
                StockMovement::create([
                    'product_id' => $orderItem->product_id,
                    'type' => 'out',
                    'quantity' => $itemData['quantity_returned'],
                    'reference_type' => 'purchase_return',
                    'reference_id' => $purchaseReturn->id,
                    'notes' => "Return to Supplier - {$purchaseReturn->return_number}",
                ]);
            }
        });

        return redirect()->route('modules.purchase-return.index')->with('success', 'Purchase Return created successfully.');
    }

    public function show(int $id): View
    {
        $item = PurchaseReturn::with(['purchaseOrder', 'items.product', 'items.purchaseOrderItem'])->findOrFail($id);
        return view('purchase-return::show', compact('item'));
    }

    public function edit(int $id): View
    {
        $item = PurchaseReturn::with(['purchaseOrder', 'items.product', 'items.purchaseOrderItem'])->findOrFail($id);
        return view('purchase-return::edit', compact('item'));
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $request->validate([
            'return_date' => 'required|date',
            'status' => 'required|in:pending,approved,processed,rejected',
            'reason' => 'required|in:defective,damaged,wrong_item,quality_issue,other',
            'notes' => 'nullable|string',
        ]);

        $purchaseReturn = PurchaseReturn::findOrFail($id);
        $purchaseReturn->update([
            'return_date' => $request->return_date,
            'status' => $request->status,
            'reason' => $request->reason,
            'notes' => $request->notes,
        ]);

        return redirect()->route('modules.purchase-return.show', $id)->with('success', 'Purchase Return updated successfully.');
    }

    public function destroy(int $id): RedirectResponse
    {
        $item = PurchaseReturn::findOrFail($id);
        $item->delete();
        return redirect()->route('modules.purchase-return.index')->with('success', 'Purchase Return deleted.');
    }
}