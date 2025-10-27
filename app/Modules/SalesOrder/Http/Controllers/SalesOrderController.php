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
use App\Services\StockMovementService;
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
                    'pending' => 'bg-opacity-25 text-warning border border-warning-subtle',
                    'on_hold' => 'bg-opacity-25 text-secondary border border-secondary-subtle',
                    'confirmed' => 'bg-opacity-25 text-info border border-info-subtle',
                    'processing' => 'bg-opacity-25 text-primary border border-primary-subtle',
                    'shipped' => 'bg-opacity-75 text-dark border border-secondary',
                    'delivered' => 'bg-opacity-25 text-success border border-success-subtle',
                    'cancelled' => 'bg-danger bg-opacity-25 text-danger border border-danger-subtle'
                ];
                $class = $badges[$item->status] ?? 'bg-secondary bg-opacity-25 text-secondary';
                $statusText = str_replace('_', ' ', $item->status);

                // Add icons for better visual recognition
                $icons = [
                    'pending' => '<i class="fas fa-clock me-1"></i>',
                    'on_hold' => '<i class="fas fa-pause me-1"></i>',
                    'confirmed' => '<i class="fas fa-check-circle me-1"></i>',
                    'processing' => '<i class="fas fa-cog fa-spin me-1"></i>',
                    'shipped' => '<i class="fas fa-truck me-1"></i>',
                    'delivered' => '<i class="fas fa-check-double me-1"></i>',
                    'cancelled' => '<i class="fas fa-times-circle me-1"></i>'
                ];
                $icon = $icons[$item->status] ?? '';

                return "<span class='badge {$class} fw-semibold px-3 py-2'>{$icon}" . ucfirst($statusText) . "</span>";
            })
            ->addColumn('payment_method', function (SalesOrder $item) {
                $methods = $item->getPaymentMethods();
                return $methods[$item->payment_method] ?? ucfirst($item->payment_method);
            })
            ->addColumn('payment_status_badge', function (SalesOrder $item) {
                $badges = [
                    'pending' => 'bg-warning bg-opacity-25 text-white border border-warning-subtle',
                    'partial' => 'bg-info bg-opacity-25 text-white border border-info-subtle',
                    'paid' => 'bg-success bg-opacity-25 text-white border border-success-subtle',
                    'overpaid' => 'bg-primary bg-opacity-25 text-white border border-primary-subtle',
                    'refunded' => 'bg-danger bg-opacity-25 text-white border border-danger-subtle'
                ];
                $class = $badges[$item->payment_status] ?? 'bg-secondary bg-opacity-25 text-secondary';

                // Add icons for payment status
                $icons = [
                    'pending' => '<i class="fas fa-hourglass-half me-1"></i>',
                    'partial' => '<i class="fas fa-coins me-1"></i>',
                    'paid' => '<i class="fas fa-check-circle me-1"></i>',
                    'overpaid' => '<i class="fas fa-money-bill-wave me-1"></i>',
                    'refunded' => '<i class="fas fa-undo me-1"></i>'
                ];
                $icon = $icons[$item->payment_status] ?? '';

                return "<span class='badge {$class} fw-semibold px-3 py-2'>{$icon}" . ucfirst($item->payment_status) . "</span>";
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
            ->rawColumns(['actions', 'status_badge', 'payment_status_badge'])
            ->toJson();
    }

    public function create(): View
    {
        $customers = Customer::orderBy('name')->get();
        $products = Product::with('variants')->where(function($query) {
            $query->where('quantity_on_hand', '>', 0)
                  ->orWhere('has_variants', true); // Include products with variants even if product-level stock is 0
        })->orderBy('name')->get();

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
            'items.*.variant_id' => 'nullable|exists:product_variants,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.discount_type' => 'nullable|in:fixed,percentage,none',
            'items.*.discount_rate' => 'nullable|numeric|min:0',
            'items.*.discount_reason' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        DB::transaction(function () use ($request) {
            // Calculate totals before creating the sales order
            $subtotal = 0;
            foreach ($request->items as $itemData) {
                $subtotal += $itemData['quantity'] * $itemData['unit_price'];
            }

            // Calculate discount amount
            $discountAmount = 0;
            if ($request->discount_type === 'percentage' && $request->discount_rate) {
                $discountAmount = $subtotal * ($request->discount_rate / 100);
            } elseif ($request->discount_type === 'fixed' && $request->discount_amount) {
                $discountAmount = $request->discount_amount;
            }

            $totalAmount = $subtotal - $discountAmount;

            // Create sales order with calculated totals
            $salesOrder = SalesOrder::create([
                'customer_id' => $request->customer_id,
                'order_date' => $request->order_date,
                'status' => 'pending',
                'payment_method' => $request->payment_method,
                'paid_amount' => $request->paid_amount,
                'discount_type' => $request->discount_type,
                'discount_rate' => $request->discount_type === 'percentage' ? $request->discount_rate : 0,
                'discount_reason' => $request->discount_reason,
                'reference_number' => $request->reference_number,
                'notes' => $request->notes,
                'subtotal' => $subtotal,
                'discount_amount' => $discountAmount,
                'total_amount' => $totalAmount,
                'tax_amount' => 0, // Can be calculated later if needed
                'cogs_amount' => 0, // Will be calculated after items are created
                'profit_amount' => 0, // Will be calculated after items are created
            ]);

            // Create sales order items and update stock
            foreach ($request->items as $itemData) {
                $product = Product::findOrFail($itemData['product_id']);
                $variantId = $itemData['variant_id'] ?? null;

                // Check stock availability
                if ($variantId) {
                    $variant = \App\Modules\Products\Models\ProductVariant::findOrFail($variantId);
                    if ($variant->quantity_on_hand < $itemData['quantity']) {
                        throw new \Exception("Insufficient stock for variant: {$variant->variant_name}. Available: {$variant->quantity_on_hand}, Required: {$itemData['quantity']}");
                    }
                } else {
                    if ($product->quantity_on_hand < $itemData['quantity']) {
                        throw new \Exception("Insufficient stock for product: {$product->name}. Available: {$product->quantity_on_hand}, Required: {$itemData['quantity']}");
                    }
                }

                // Create sales order item
                SalesOrderItem::create([
                    'sales_order_id' => $salesOrder->id,
                    'product_id' => $itemData['product_id'],
                    'variant_id' => $variantId,
                    'quantity' => $itemData['quantity'],
                    'unit_price' => $itemData['unit_price'],
                    'discount_type' => $itemData['discount_type'] ?? 'none',
                    'discount_rate' => $itemData['discount_rate'] ?? 0,
                    'discount_reason' => $itemData['discount_reason'] ?? null,
                ]);

                // Create stock movement (outbound) using service
                StockMovementService::recordSale(
                    $itemData['product_id'],
                    $variantId,
                    $itemData['quantity'],
                    $salesOrder->id,
                    "Sale - Order #{$salesOrder->order_number}"
                );
            }

            // Recalculate totals with actual item data (including COGS and profit)
            $salesOrder->calculateTotals();

            // Calculate payment status and update order status
            if ($request->paid_amount >= $salesOrder->total_amount) {
                $salesOrder->payment_status = 'paid';
                $salesOrder->change_amount = $request->paid_amount - $salesOrder->total_amount;
                $salesOrder->status = 'delivered'; // Auto-complete if fully paid
            } elseif ($request->paid_amount > 0) {
                $salesOrder->payment_status = 'partial';
                $salesOrder->change_amount = 0;
                $salesOrder->status = 'confirmed'; // Confirmed if partially paid
            } else {
                $salesOrder->payment_status = 'pending';
                $salesOrder->change_amount = 0;
                $salesOrder->status = 'pending'; // Keep pending if no payment
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
        $item = SalesOrder::with(['customer', 'items.product', 'items.variant'])->findOrFail($id);
        return view('sales-order::show', compact('item'));
    }

    public function edit(int $id): View
    {
        $item = SalesOrder::with(['customer', 'items.product', 'items.variant'])->findOrFail($id);
        $customers = Customer::orderBy('name')->get();
        $products = Product::with('variants')->where(function($query) {
            $query->where('quantity_on_hand', '>', 0)
                  ->orWhere('has_variants', true); // Include products with variants even if product-level stock is 0
        })->orderBy('name')->get();
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
            'items.*.variant_id' => 'nullable|exists:product_variants,id',
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
                        'variant_id' => $itemData['variant_id'] ?? null,
                        'quantity' => $newQuantity,
                        'unit_price' => $itemData['unit_price'],
                        'discount_type' => $itemData['discount_type'] ?? 'none',
                        'discount_rate' => $itemData['discount_rate'] ?? 0,
                        'discount_reason' => $itemData['discount_reason'] ?? null,
                    ]);

                    // Adjust stock if quantity changed
                    if ($quantityDiff != 0) {
                        $movementType = $quantityDiff > 0 ? 'out' : 'in';
                        $variantId = $itemData['variant_id'] ?? null;

                        if ($movementType === 'out') {
                            // Stock going out (sale/adjustment)
                            \App\Services\StockMovementService::recordSale(
                                $itemData['product_id'],
                                $variantId,
                                abs($quantityDiff),
                                $salesOrder->id,
                                "Quantity adjustment (OUT) - Order #{$salesOrder->order_number}"
                            );
                        } else {
                            // Stock coming back (return/adjustment)
                            \App\Services\StockMovementService::recordSaleReturn(
                                $itemData['product_id'],
                                $variantId,
                                abs($quantityDiff),
                                $salesOrder->id,
                                "Quantity adjustment (IN) - Order #{$salesOrder->order_number}"
                            );
                        }
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

            // Calculate payment status and auto-adjust order status
            if ($request->paid_amount >= $salesOrder->total_amount) {
                $salesOrder->payment_status = 'paid';
                $salesOrder->change_amount = $request->paid_amount - $salesOrder->total_amount;

                // Auto-complete if fully paid and status allows
                if ($request->status !== 'cancelled' && $request->status !== 'delivered') {
                    $salesOrder->status = 'delivered';
                } else {
                    $salesOrder->status = $request->status;
                }
            } elseif ($request->paid_amount > 0) {
                $salesOrder->payment_status = 'partial';
                $salesOrder->change_amount = 0;
                $salesOrder->status = $request->status; // Use submitted status
            } else {
                $salesOrder->payment_status = 'pending';
                $salesOrder->change_amount = 0;
                $salesOrder->status = $request->status; // Use submitted status
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
        $item = SalesOrder::with(['customer', 'items.product', 'items.variant'])->findOrFail($id);
        return view('sales-order::invoice', compact('item'));
    }

    public function posPrint(int $id): View
    {
        $item = SalesOrder::with(['customer', 'items.product', 'items.variant'])->findOrFail($id);
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

        // Update payment status and order status
        if ($request->paid_amount >= $salesOrder->total_amount) {
            $salesOrder->payment_status = 'paid';
            $salesOrder->change_amount = $request->paid_amount - $salesOrder->total_amount;

            // Auto-complete order if fully paid and not already delivered
            if ($salesOrder->status !== 'delivered' && $salesOrder->status !== 'cancelled') {
                $salesOrder->status = 'delivered';
            }
        } elseif ($request->paid_amount > 0) {
            $salesOrder->payment_status = 'partial';
            $salesOrder->change_amount = 0;

            // Move to confirmed if currently pending
            if ($salesOrder->status === 'pending') {
                $salesOrder->status = 'confirmed';
            }
        } else {
            $salesOrder->payment_status = 'pending';
            $salesOrder->change_amount = 0;
        }

        $salesOrder->save();

        return back()->with('success', 'Payment information updated successfully. Order status: ' . ucfirst($salesOrder->status));
    }

    /**
     * Handle sales return - create return and restore stock
     */
    public function return(Request $request, int $id): RedirectResponse
    {
        $request->validate([
            'return_items' => 'required|array|min:1',
            'return_items.*.product_id' => 'required|exists:products,id',
            'return_items.*.quantity' => 'required|integer|min:1',
            'return_items.*.reason' => 'nullable|string',
            'refund_amount' => 'required|numeric|min:0',
            'notes' => 'nullable|string'
        ]);

        $salesOrder = SalesOrder::findOrFail($id);

        if ($salesOrder->status === 'cancelled') {
            return back()->with('error', 'Cannot return items from a cancelled order.');
        }

        DB::transaction(function () use ($request, $salesOrder) {
            foreach ($request->return_items as $item) {
                // Verify the item exists in the original order
                $orderItem = $salesOrder->items()
                    ->where('product_id', $item['product_id'])
                    ->first();

                if (!$orderItem) {
                    throw new \Exception("Product not found in original order.");
                }

                // Check if return quantity doesn't exceed original quantity
                if ($item['quantity'] > $orderItem->quantity) {
                    throw new \Exception("Return quantity exceeds original order quantity.");
                }

                // Record stock movement (inbound) for return
                $variantId = $orderItem->variant_id ?? null;
                StockMovementService::recordSaleReturn(
                    $item['product_id'],
                    $variantId,
                    $item['quantity'],
                    $salesOrder->id,
                    "Return - Order #{$salesOrder->order_number}. Reason: " . ($item['reason'] ?? 'No reason provided')
                );
            }

            // Update payment status to refunded if full refund
            if ($request->refund_amount >= $salesOrder->paid_amount) {
                $salesOrder->payment_status = 'refunded';
            } else {
                $salesOrder->payment_status = 'partial_refund';
            }

            $salesOrder->paid_amount -= $request->refund_amount;
            $salesOrder->save();

            // Add notes to the order about the return
            $returnNotes = "Return processed: Refunded amount \${$request->refund_amount}. Items returned: " . count($request->return_items);
            if ($request->notes) {
                $returnNotes .= ". Notes: {$request->notes}";
            }

            // You could store this in a separate returns table or add it to the order notes
        });

        return back()->with('success', 'Sales return processed successfully. Stock has been restored.');
    }
}
