<?php

namespace App\Modules\PurchaseOrder\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\PurchaseOrder\Http\Requests\StorePurchaseOrderRequest;
use App\Modules\PurchaseOrder\Http\Requests\UpdatePurchaseOrderRequest;
use App\Modules\PurchaseOrder\Models\PurchaseOrder;
use App\Modules\PurchaseOrderItem\Models\PurchaseOrderItem;
use App\Modules\StockMovement\Models\StockMovement;
use App\Modules\Products\Models\Product;
use App\Modules\Suppliers\Models\Supplier;
use App\Services\StockMovementService;
use App\Services\AccountingService;
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
        $query = PurchaseOrder::with(['items', 'supplier']);

        return DataTables::eloquent($query)
            ->addColumn('items_count', function (PurchaseOrder $item) {
                return $item->items->count();
            })
            ->addColumn('supplier_name', function (PurchaseOrder $item) {
                return $item->supplier ? $item->supplier->name : ($item->supplier_name ?: 'Unknown');
            })
            ->addColumn('status_badge', function (PurchaseOrder $item) {
                $badges = [
                    'pending' => 'bg-warning text-dark',
                    'confirmed' => 'bg-info text-dark',
                    'processing' => 'bg-primary',
                    'received' => 'bg-success',
                    'cancelled' => 'bg-danger'
                ];
                $class = $badges[$item->status] ?? 'bg-secondary';
                return "<span class='badge {$class}'>" . ucfirst($item->status) . "</span>";
            })
            ->addColumn('payment_status_badge', function (PurchaseOrder $item) {
                $badges = [
                    'unpaid' => 'bg-danger',
                    'partial' => 'bg-warning text-dark',
                    'paid' => 'bg-success'
                ];
                $class = $badges[$item->payment_status] ?? 'bg-secondary';
                return "<span class='badge {$class}'>" . ucfirst($item->payment_status) . "</span>";
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
            ->editColumn('paid_amount', function (PurchaseOrder $item) {
                return '$' . number_format($item->paid_amount, 2);
            })
            ->rawColumns(['actions', 'status_badge', 'payment_status_badge'])
            ->toJson();
    }

    public function create(): View
    {
        $products = Product::orderBy('name')->get();
        $suppliers = Supplier::active()->orderBy('name')->get();
        return view('purchase-order::create', compact('products', 'suppliers'));
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'supplier_name' => 'nullable|string|max:255',
            'order_date' => 'required|date',
            'status' => 'required|in:pending,confirmed,processing,received,cancelled',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.variant_id' => 'nullable|exists:product_variants,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        DB::transaction(function () use ($request) {
            // Get supplier details
            $supplier = Supplier::findOrFail($request->supplier_id);

            // Calculate totals
            $subtotal = 0;
            foreach ($request->items as $item) {
                $subtotal += $item['quantity'] * $item['unit_price'];
            }

            // Create purchase order
            $purchaseOrder = PurchaseOrder::create([
                'supplier_id' => $supplier->id,
                'supplier_name' => $supplier->name,
                'order_date' => $request->order_date,
                'status' => $request->status ?? 'pending',
                'subtotal' => $subtotal,
                'tax_amount' => 0,
                'discount_amount' => 0,
                'total_amount' => $subtotal,
                'notes' => $request->notes,
            ]);

            // Create purchase order items and update stock
            foreach ($request->items as $itemData) {
                // Validate variant belongs to product if provided
                if (isset($itemData['variant_id'])) {
                    $variant = \App\Modules\Products\Models\ProductVariant::find($itemData['variant_id']);
                    if (!$variant || $variant->product_id != $itemData['product_id']) {
                        throw new \Exception('Variant does not belong to the specified product');
                    }
                }

                // Create purchase order item
                PurchaseOrderItem::create([
                    'purchase_order_id' => $purchaseOrder->id,
                    'product_id' => $itemData['product_id'],
                    'variant_id' => $itemData['variant_id'] ?? null,
                    'quantity' => $itemData['quantity'],
                    'unit_price' => $itemData['unit_price'],
                    'total_price' => $itemData['quantity'] * $itemData['unit_price'],
                ]);

                // Create stock movement (inbound) using service
                StockMovementService::recordPurchase(
                    $itemData['product_id'],
                    $itemData['variant_id'] ?? null,
                    $itemData['quantity'],
                    $purchaseOrder->id,
                    "Purchase - Order #{$purchaseOrder->po_number}"
                );
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
        $item = PurchaseOrder::with(['items.product', 'supplier'])->findOrFail($id);
        $products = Product::orderBy('name')->get();
        $suppliers = Supplier::active()->orderBy('name')->get();
        return view('purchase-order::edit', compact('item', 'products', 'suppliers'));
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'order_date' => 'required|date',
            'status' => 'required|in:pending,confirmed,processing,received,cancelled',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.variant_id' => 'nullable|exists:product_variants,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        DB::transaction(function () use ($request, $id) {
            $purchaseOrder = PurchaseOrder::findOrFail($id);

            // Get supplier details
            $supplier = Supplier::findOrFail($request->supplier_id);

            // Calculate totals
            $subtotal = 0;
            foreach ($request->items as $item) {
                $subtotal += $item['quantity'] * $item['unit_price'];
            }

            // Update purchase order
            $purchaseOrder->update([
                'supplier_id' => $supplier->id,
                'supplier_name' => $supplier->name,
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
                    // Reverse stock movement for deleted item (purchase return)
                    StockMovementService::recordPurchaseReturn(
                        $existingItem->product_id,
                        $existingItem->quantity,
                        $purchaseOrder->id,
                        "Returned stock - Item removed from Order #{$purchaseOrder->po_number}"
                    );
                    $existingItem->delete();
                }
            }

            // Update or create items
            foreach ($request->items as $itemData) {
                // Validate variant belongs to product if provided
                if (isset($itemData['variant_id'])) {
                    $variant = \App\Modules\Products\Models\ProductVariant::find($itemData['variant_id']);
                    if (!$variant || $variant->product_id != $itemData['product_id']) {
                        throw new \Exception('Variant does not belong to the specified product');
                    }
                }

                if (isset($itemData['id']) && $existingItems->has($itemData['id'])) {
                    // Update existing item
                    $existingItem = $existingItems->get($itemData['id']);
                    $oldQuantity = $existingItem->quantity;
                    $newQuantity = $itemData['quantity'];
                    $quantityDiff = $newQuantity - $oldQuantity;

                    $existingItem->update([
                        'product_id' => $itemData['product_id'],
                        'variant_id' => $itemData['variant_id'] ?? null,
                        'quantity' => $newQuantity,
                        'unit_price' => $itemData['unit_price'],
                        'total_price' => $newQuantity * $itemData['unit_price'],
                    ]);

                    // Adjust stock if quantity changed
                    if ($quantityDiff != 0) {
                        if ($quantityDiff > 0) {
                            // Additional stock coming in
                            StockMovementService::recordPurchase(
                                $itemData['product_id'],
                                $itemData['variant_id'] ?? null,
                                $quantityDiff,
                                $purchaseOrder->id,
                                "Quantity adjustment (+) - Order #{$purchaseOrder->po_number}"
                            );
                        } else {
                            // Stock being returned
                            StockMovementService::recordPurchaseReturn(
                                $itemData['product_id'],
                                $itemData['variant_id'] ?? null,
                                abs($quantityDiff),
                                $purchaseOrder->id,
                                "Quantity adjustment (-) - Order #{$purchaseOrder->po_number}"
                            );
                        }
                    }
                } else {
                    // Create new item
                    PurchaseOrderItem::create([
                        'purchase_order_id' => $purchaseOrder->id,
                        'product_id' => $itemData['product_id'],
                        'variant_id' => $itemData['variant_id'] ?? null,
                        'quantity' => $itemData['quantity'],
                        'unit_price' => $itemData['unit_price'],
                        'total_price' => $itemData['quantity'] * $itemData['unit_price'],
                    ]);

                    // Create stock movement for new item
                    StockMovementService::recordPurchase(
                        $itemData['product_id'],
                        $itemData['variant_id'] ?? null,
                        $itemData['quantity'],
                        $purchaseOrder->id,
                        "Purchase - Order #{$purchaseOrder->po_number}"
                    );
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

    public function addPayment(Request $request, int $id): RedirectResponse
    {
        $purchaseOrder = PurchaseOrder::findOrFail($id);

        $request->validate([
            'payment_date' => 'required|date',
            'amount' => 'required|numeric|min:0.01|max:' . $purchaseOrder->due_amount,
            'payment_method' => 'nullable|string|in:cash,bank_transfer,check,credit_card,other',
            'reference_number' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        DB::transaction(function () use ($request, $purchaseOrder) {
            // Update purchase order payment directly
            $purchaseOrder->paid_amount += $request->amount;
            $purchaseOrder->updatePaymentStatus();

            // TODO: Create payment history table if needed for tracking individual payments
            // For now, payment tracking is handled in the purchase_orders table
        });

        return redirect()->route('modules.purchase-order.show', $id)->with('success', 'Payment added successfully.');
    }
}
