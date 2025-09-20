<?php

namespace App\Modules\PurchaseOrder\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\PurchaseOrder\Http\Requests\StorePurchaseOrderRequest;
use App\Modules\PurchaseOrder\Http\Requests\UpdatePurchaseOrderRequest;
use App\Modules\PurchaseOrder\Models\PurchaseOrder;
use App\Modules\PurchaseOrderItem\Models\PurchaseOrderItem;
use App\Modules\StockMovement\Models\StockMovement;
use App\Modules\Products\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

/**
 * Controller for managing PurchaseOrder CRUD pages and DataTables endpoint.
 */
class PurchaseOrderController extends Controller
{
    public function index(Request $request): View
    {
        return view('purchase-order::index');
    }

    /** DataTables server-side endpoint (Yajra) */
    public function data(Request $request)
    {
        $query = PurchaseOrder::with(['items']);

        return DataTables::eloquent($query)
            ->addColumn('items_count', function (PurchaseOrder $item) {
                return $item->items->count();
            })
            ->addColumn('status_badge', function (PurchaseOrder $item) {
                $badges = [
                    'pending' => 'badge-warning',
                    'confirmed' => 'badge-info',
                    'processing' => 'badge-primary',
                    'received' => 'badge-success',
                    'cancelled' => 'badge-danger'
                ];
                $class = $badges[$item->status] ?? 'badge-secondary';
                return "<span class='badge {$class}'>" . ucfirst($item->status) . "</span>";
            })
            ->addColumn('actions', function (PurchaseOrder $item) {
                return view('purchase-order::partials.actions', ['id' => $item->id])->render();
            })
            ->editColumn('order_date', function (PurchaseOrder $item) {
                return $item->order_date?->format('Y-m-d');
            })
            ->editColumn('total_amount', function (PurchaseOrder $item) {
                return '$' . number_format($item->total_amount, 2);
            })
            ->rawColumns(['actions', 'status_badge'])
            ->toJson();
    }

    public function create(): View
    {
        $products = Product::orderBy('name')->get();
        return view('purchase-order::create', compact('products'));
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'supplier_name' => 'required|string|max:255',
            'order_date' => 'required|date',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        DB::transaction(function () use ($request) {
            // Calculate totals
            $subtotal = 0;
            foreach ($request->items as $item) {
                $subtotal += $item['quantity'] * $item['unit_price'];
            }

            // Create purchase order
            $purchaseOrder = PurchaseOrder::create([
                'supplier_name' => $request->supplier_name,
                'order_date' => $request->order_date,
                'status' => 'pending',
                'subtotal' => $subtotal,
                'tax_amount' => 0,
                'discount_amount' => 0,
                'total_amount' => $subtotal,
                'notes' => $request->notes,
            ]);

            // Create purchase order items and update stock
            foreach ($request->items as $itemData) {
                // Create purchase order item
                PurchaseOrderItem::create([
                    'purchase_order_id' => $purchaseOrder->id,
                    'product_id' => $itemData['product_id'],
                    'quantity' => $itemData['quantity'],
                    'unit_price' => $itemData['unit_price'],
                    'total_price' => $itemData['quantity'] * $itemData['unit_price'],
                ]);

                // Create stock movement (inbound)
                StockMovement::create([
                    'product_id' => $itemData['product_id'],
                    'type' => 'in',
                    'quantity' => $itemData['quantity'],
                    'reference_type' => 'purchase_order',
                    'reference_id' => $purchaseOrder->id,
                    'notes' => "Purchase - Order #{$purchaseOrder->po_number}",
                ]);
            }
        });

        return redirect()->route('modules.purchase-order.index')->with('success', 'Purchase Order created successfully.');
    }

    public function show(int $id): View
    {
        $item = PurchaseOrder::with(['items.product'])->findOrFail($id);
        return view('purchase-order::show', compact('item'));
    }

    public function edit(int $id): View
    {
        $item = PurchaseOrder::with(['items.product'])->findOrFail($id);
        $products = Product::orderBy('name')->get();
        return view('purchase-order::edit', compact('item', 'products'));
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $request->validate([
            'supplier_name' => 'required|string|max:255',
            'order_date' => 'required|date',
            'status' => 'required|in:pending,confirmed,processing,received,cancelled',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        DB::transaction(function () use ($request, $id) {
            $purchaseOrder = PurchaseOrder::findOrFail($id);

            // Calculate totals
            $subtotal = 0;
            foreach ($request->items as $item) {
                $subtotal += $item['quantity'] * $item['unit_price'];
            }

            // Update purchase order
            $purchaseOrder->update([
                'supplier_name' => $request->supplier_name,
                'order_date' => $request->order_date,
                'status' => $request->status,
                'subtotal' => $subtotal,
                'total_amount' => $subtotal,
                'notes' => $request->notes,
            ]);

            // Get existing items
            $existingItems = $purchaseOrder->items->keyBy('id');
            $submittedItemIds = collect($request->items)->pluck('id')->filter();

            // Delete removed items and reverse stock
            foreach ($existingItems as $existingItem) {
                if (!$submittedItemIds->contains($existingItem->id)) {
                    // Reverse stock movement for deleted item
                    StockMovement::create([
                        'product_id' => $existingItem->product_id,
                        'type' => 'out',
                        'quantity' => $existingItem->quantity,
                        'reference_type' => 'purchase_order_adjustment',
                        'reference_id' => $purchaseOrder->id,
                        'notes' => "Returned stock - Item removed from Order #{$purchaseOrder->po_number}",
                    ]);
                    $existingItem->delete();
                }
            }

            // Update or create items
            foreach ($request->items as $itemData) {
                if (isset($itemData['id']) && $existingItems->has($itemData['id'])) {
                    // Update existing item
                    $existingItem = $existingItems->get($itemData['id']);
                    $oldQuantity = $existingItem->quantity;
                    $newQuantity = $itemData['quantity'];
                    $quantityDiff = $newQuantity - $oldQuantity;

                    $existingItem->update([
                        'product_id' => $itemData['product_id'],
                        'quantity' => $newQuantity,
                        'unit_price' => $itemData['unit_price'],
                        'total_price' => $newQuantity * $itemData['unit_price'],
                    ]);

                    // Adjust stock if quantity changed
                    if ($quantityDiff != 0) {
                        StockMovement::create([
                            'product_id' => $itemData['product_id'],
                            'type' => $quantityDiff > 0 ? 'in' : 'out',
                            'quantity' => abs($quantityDiff),
                            'reference_type' => 'purchase_order_adjustment',
                            'reference_id' => $purchaseOrder->id,
                            'notes' => "Quantity adjustment - Order #{$purchaseOrder->po_number}",
                        ]);
                    }
                } else {
                    // Create new item
                    PurchaseOrderItem::create([
                        'purchase_order_id' => $purchaseOrder->id,
                        'product_id' => $itemData['product_id'],
                        'quantity' => $itemData['quantity'],
                        'unit_price' => $itemData['unit_price'],
                        'total_price' => $itemData['quantity'] * $itemData['unit_price'],
                    ]);

                    // Create stock movement for new item
                    StockMovement::create([
                        'product_id' => $itemData['product_id'],
                        'type' => 'in',
                        'quantity' => $itemData['quantity'],
                        'reference_type' => 'purchase_order',
                        'reference_id' => $purchaseOrder->id,
                        'notes' => "Purchase - Order #{$purchaseOrder->po_number}",
                    ]);
                }
            }
        });

        return redirect()->route('modules.purchase-order.show', $id)->with('success', 'Purchase Order updated successfully.');
    }

    public function destroy(int $id): RedirectResponse
    {
        $item = PurchaseOrder::findOrFail($id);
        $item->delete();
        return redirect()->route('modules.purchase-order.index')->with('success', 'PurchaseOrder deleted.');
    }
}
