<?php

namespace App\Modules\SalesOrder\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\SalesOrder\Http\Requests\StoreSalesOrderRequest;
use App\Modules\SalesOrder\Http\Requests\UpdateSalesOrderRequest;
use App\Modules\SalesOrder\Models\SalesOrder;
use App\Modules\SalesOrderItem\Models\SalesOrderItem;
use App\Modules\StockMovement\Models\StockMovement;
use App\Modules\Products\Models\Product;
use App\Modules\Customers\Models\Customer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

/**
 * Controller for managing SalesOrder CRUD pages and DataTables endpoint.
 */
class SalesOrderController extends Controller
{
    public function index(Request $request): View
    {
        return view('sales-order::index');
    }

    /** DataTables server-side endpoint (Yajra) */
    public function data(Request $request)
    {
        $query = SalesOrder::with(['customer', 'items', 'heldBy', 'releasedBy']);

        return DataTables::eloquent($query)
            ->addColumn('customer_name', function (SalesOrder $item) {
                return $item->customer->name ?? 'N/A';
            })
            ->addColumn('items_count', function (SalesOrder $item) {
                return $item->items->count();
            })
            ->addColumn('status_badge', function (SalesOrder $item) {
                $badges = [
                    'pending' => 'badge-warning',
                    'on_hold' => 'badge-secondary',
                    'confirmed' => 'badge-info',
                    'processing' => 'badge-primary',
                    'shipped' => 'badge-light',
                    'delivered' => 'badge-success',
                    'cancelled' => 'badge-danger'
                ];
                $class = $badges[$item->status] ?? 'badge-secondary';
                $statusText = str_replace('_', ' ', $item->status);
                return "<span class='badge {$class}'>" . ucfirst($statusText) . "</span>";
            })
            ->addColumn('payment_method', function (SalesOrder $item) {
                $methods = $item->getPaymentMethods();
                return $methods[$item->payment_method] ?? ucfirst($item->payment_method);
            })
            ->addColumn('payment_status_badge', function (SalesOrder $item) {
                $badges = [
                    'pending' => 'badge-warning',
                    'partial' => 'badge-info',
                    'paid' => 'badge-success',
                    'overpaid' => 'badge-primary',
                    'refunded' => 'badge-danger'
                ];
                $class = $badges[$item->payment_status] ?? 'badge-secondary';
                return "<span class='badge {$class}'>" . ucfirst($item->payment_status) . "</span>";
            })
            ->addColumn('actions', function (SalesOrder $item) {
                return view('sales-order::partials.actions', ['id' => $item->id])->render();
            })
            ->editColumn('order_date', function (SalesOrder $item) {
                return $item->order_date?->format('Y-m-d');
            })
            ->editColumn('total_amount', function (SalesOrder $item) {
                return '$' . number_format($item->total_amount, 2);
            })
            ->rawColumns(['actions', 'status_badge'])
            ->toJson();
    }

    public function create(): View
    {
        $customers = Customer::orderBy('name')->get();
        $products = Product::where('quantity_on_hand', '>', 0)->orderBy('name')->get();
        $salesOrder = new SalesOrder();
        $paymentMethods = $salesOrder->getPaymentMethods();
        $discountTypes = $salesOrder->getDiscountTypes();
        return view('sales-order::create', compact('customers', 'products', 'paymentMethods', 'discountTypes'));
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'order_date' => 'required|date',
            'payment_method' => 'required|in:cash,card,mobile_banking,bank_transfer,cheque',
            'paid_amount' => 'required|numeric|min:0',
            'discount_type' => 'nullable|in:fixed,percentage,none',
            'discount_rate' => 'nullable|numeric|min:0',
            'discount_reason' => 'nullable|string',
            'reference_number' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.discount_type' => 'nullable|in:fixed,percentage,none',
            'items.*.discount_rate' => 'nullable|numeric|min:0',
            'items.*.discount_reason' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        DB::transaction(function () use ($request) {
            // Create sales order with basic info
            $salesOrder = SalesOrder::create([
                'customer_id' => $request->customer_id,
                'order_date' => $request->order_date,
                'status' => 'pending',
                'payment_method' => $request->payment_method,
                'paid_amount' => $request->paid_amount,
                'discount_type' => $request->discount_type,
                'discount_rate' => $request->discount_rate,
                'discount_reason' => $request->discount_reason,
                'reference_number' => $request->reference_number,
                'notes' => $request->notes,
            ]);

            // Create sales order items and update stock
            foreach ($request->items as $itemData) {
                $product = Product::findOrFail($itemData['product_id']);

                // Check stock availability
                if ($product->quantity_on_hand < $itemData['quantity']) {
                    throw new \Exception("Insufficient stock for product: {$product->name}. Available: {$product->quantity_on_hand}, Required: {$itemData['quantity']}");
                }

                // Create sales order item
                SalesOrderItem::create([
                    'sales_order_id' => $salesOrder->id,
                    'product_id' => $itemData['product_id'],
                    'quantity' => $itemData['quantity'],
                    'unit_price' => $itemData['unit_price'],
                    'discount_type' => $itemData['discount_type'] ?? 'none',
                    'discount_rate' => $itemData['discount_rate'] ?? 0,
                    'discount_reason' => $itemData['discount_reason'] ?? null,
                ]);

                // Create stock movement (outbound)
                StockMovement::create([
                    'product_id' => $itemData['product_id'],
                    'type' => 'out',
                    'quantity' => $itemData['quantity'],
                    'reference_type' => 'sales_order',
                    'reference_id' => $salesOrder->id,
                    'notes' => "Sale - Order #{$salesOrder->order_number}",
                ]);
            }

            // Calculate totals and payment status
            $salesOrder->calculateTotals();

            // Calculate payment status
            if ($request->paid_amount >= $salesOrder->total_amount) {
                $salesOrder->payment_status = 'paid';
                $salesOrder->change_amount = $request->paid_amount - $salesOrder->total_amount;
            } elseif ($request->paid_amount > 0) {
                $salesOrder->payment_status = 'partial';
                $salesOrder->change_amount = 0;
            } else {
                $salesOrder->payment_status = 'pending';
                $salesOrder->change_amount = 0;
            }

            if ($request->paid_amount > 0) {
                $salesOrder->payment_date = now();
            }

            $salesOrder->save();
        });

        return redirect()->route('modules.sales-order.index')->with('success', 'Sales Order created successfully.');
    }

    public function show(int $id): View
    {
        $item = SalesOrder::with(['customer', 'items.product'])->findOrFail($id);
        return view('sales-order::show', compact('item'));
    }

    public function edit(int $id): View
    {
        $item = SalesOrder::with(['customer', 'items.product'])->findOrFail($id);
        $customers = Customer::orderBy('name')->get();
        $products = Product::orderBy('name')->get();
        $paymentMethods = $item->getPaymentMethods();
        $discountTypes = $item->getDiscountTypes();
        return view('sales-order::edit', compact('item', 'customers', 'products', 'paymentMethods', 'discountTypes'));
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'order_date' => 'required|date',
            'status' => 'required|in:pending,on_hold,confirmed,processing,shipped,delivered,cancelled',
            'payment_method' => 'required|in:cash,card,mobile_banking,bank_transfer,cheque',
            'paid_amount' => 'required|numeric|min:0',
            'discount_type' => 'nullable|in:fixed,percentage,none',
            'discount_rate' => 'nullable|numeric|min:0',
            'discount_reason' => 'nullable|string',
            'reference_number' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.discount_type' => 'nullable|in:fixed,percentage,none',
            'items.*.discount_rate' => 'nullable|numeric|min:0',
            'items.*.discount_reason' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        DB::transaction(function () use ($request, $id) {
            $salesOrder = SalesOrder::findOrFail($id);

            // Calculate totals
            $subtotal = 0;
            foreach ($request->items as $item) {
                $subtotal += $item['quantity'] * $item['unit_price'];
            }

            // Update sales order
            $salesOrder->update([
                'customer_id' => $request->customer_id,
                'order_date' => $request->order_date,
                'status' => $request->status,
                'payment_method' => $request->payment_method,
                'paid_amount' => $request->paid_amount,
                'discount_type' => $request->discount_type,
                'discount_rate' => $request->discount_rate,
                'discount_reason' => $request->discount_reason,
                'reference_number' => $request->reference_number,
                'notes' => $request->notes,
            ]);

            // Get existing items
            $existingItems = $salesOrder->items->keyBy('id');
            $submittedItemIds = collect($request->items)->pluck('id')->filter();

            // Delete removed items and reverse stock
            foreach ($existingItems as $existingItem) {
                if (!$submittedItemIds->contains($existingItem->id)) {
                    // Reverse stock movement for deleted item
                    StockMovement::create([
                        'product_id' => $existingItem->product_id,
                        'type' => 'in',
                        'quantity' => $existingItem->quantity,
                        'reference_type' => 'sales_order_adjustment',
                        'reference_id' => $salesOrder->id,
                        'notes' => "Returned stock - Item removed from Order #{$salesOrder->order_number}",
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
                        'discount_type' => $itemData['discount_type'] ?? 'none',
                        'discount_rate' => $itemData['discount_rate'] ?? 0,
                        'discount_reason' => $itemData['discount_reason'] ?? null,
                    ]);

                    // Adjust stock if quantity changed
                    if ($quantityDiff != 0) {
                        StockMovement::create([
                            'product_id' => $itemData['product_id'],
                            'type' => $quantityDiff > 0 ? 'out' : 'in',
                            'quantity' => abs($quantityDiff),
                            'reference_type' => 'sales_order_adjustment',
                            'reference_id' => $salesOrder->id,
                            'notes' => "Quantity adjustment - Order #{$salesOrder->order_number}",
                        ]);
                    }
                } else {
                    // Create new item
                    $product = Product::findOrFail($itemData['product_id']);

                    // Check stock availability for new items
                    if ($product->quantity_on_hand < $itemData['quantity']) {
                        throw new \Exception("Insufficient stock for product: {$product->name}. Available: {$product->quantity_on_hand}, Required: {$itemData['quantity']}");
                    }

                    SalesOrderItem::create([
                        'sales_order_id' => $salesOrder->id,
                        'product_id' => $itemData['product_id'],
                        'quantity' => $itemData['quantity'],
                        'unit_price' => $itemData['unit_price'],
                        'discount_type' => $itemData['discount_type'] ?? 'none',
                        'discount_rate' => $itemData['discount_rate'] ?? 0,
                        'discount_reason' => $itemData['discount_reason'] ?? null,
                    ]);

                    // Create stock movement for new item
                    StockMovement::create([
                        'product_id' => $itemData['product_id'],
                        'type' => 'out',
                        'quantity' => $itemData['quantity'],
                        'reference_type' => 'sales_order',
                        'reference_id' => $salesOrder->id,
                        'notes' => "Sale - Order #{$salesOrder->order_number}",
                    ]);
                }
            }

            // Calculate totals and payment status
            $salesOrder->calculateTotals();

            // Calculate payment status
            if ($request->paid_amount >= $salesOrder->total_amount) {
                $salesOrder->payment_status = 'paid';
                $salesOrder->change_amount = $request->paid_amount - $salesOrder->total_amount;
            } elseif ($request->paid_amount > 0) {
                $salesOrder->payment_status = 'partial';
                $salesOrder->change_amount = 0;
            } else {
                $salesOrder->payment_status = 'pending';
                $salesOrder->change_amount = 0;
            }

            if ($request->paid_amount > 0) {
                $salesOrder->payment_date = now();
            }

            $salesOrder->save();
        });

        return redirect()->route('modules.sales-order.show', $id)->with('success', 'Sales Order updated successfully.');
    }

    public function destroy(int $id): RedirectResponse
    {
        $item = SalesOrder::findOrFail($id);
        $item->delete();
        return redirect()->route('modules.sales-order.index')->with('success', 'SalesOrder deleted.');
    }

    public function invoice(int $id): View
    {
        $item = SalesOrder::with(['customer', 'items.product'])->findOrFail($id);
        return view('sales-order::invoice', compact('item'));
    }

    public function posPrint(int $id): View
    {
        $item = SalesOrder::with(['customer', 'items.product'])->findOrFail($id);
        return view('sales-order::pos-print', compact('item'));
    }

    public function holdOrder(Request $request, int $id): RedirectResponse
    {
        $request->validate([
            'hold_reason' => 'required|string|min:5'
        ]);

        $salesOrder = SalesOrder::findOrFail($id);

        if ($salesOrder->status === 'on_hold') {
            return back()->with('error', 'Order is already on hold.');
        }

        $salesOrder->update([
            'status' => 'on_hold',
            'hold_reason' => $request->hold_reason,
            'hold_date' => now(),
            'held_by' => auth()->id()
        ]);

        return back()->with('success', 'Order placed on hold successfully.');
    }

    public function releaseOrder(int $id): RedirectResponse
    {
        $salesOrder = SalesOrder::findOrFail($id);

        if ($salesOrder->status !== 'on_hold') {
            return back()->with('error', 'Order is not on hold.');
        }

        $salesOrder->update([
            'status' => 'pending',
            'release_date' => now(),
            'released_by' => auth()->id()
        ]);

        return back()->with('success', 'Order released from hold successfully.');
    }

    public function updatePayment(Request $request, int $id): RedirectResponse
    {
        $request->validate([
            'paid_amount' => 'required|numeric|min:0',
            'payment_method' => 'required|in:cash,card,mobile_banking,bank_transfer,cheque',
            'reference_number' => 'nullable|string'
        ]);

        $salesOrder = SalesOrder::findOrFail($id);

        $salesOrder->update([
            'paid_amount' => $request->paid_amount,
            'payment_method' => $request->payment_method,
            'reference_number' => $request->reference_number,
            'payment_date' => now()
        ]);

        // Update payment status
        if ($request->paid_amount >= $salesOrder->total_amount) {
            $salesOrder->payment_status = 'paid';
            $salesOrder->change_amount = $request->paid_amount - $salesOrder->total_amount;
        } elseif ($request->paid_amount > 0) {
            $salesOrder->payment_status = 'partial';
            $salesOrder->change_amount = 0;
        } else {
            $salesOrder->payment_status = 'pending';
            $salesOrder->change_amount = 0;
        }

        $salesOrder->save();

        return back()->with('success', 'Payment information updated successfully.');
    }
}
