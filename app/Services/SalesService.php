<?php

namespace App\Services;

use App\Modules\SalesOrder\Models\SalesOrder;
use App\Modules\SalesOrderItem\Models\SalesOrderItem;
use App\Modules\StockMovement\Models\StockMovement;
use App\Modules\Products\Models\Product;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class SalesService
{
    /**
     * Create a new sales order with items
     *
     * @param array $orderData
     * @param array $items
     * @return SalesOrder
     * @throws \Exception
     */
    public function createSalesOrder(array $orderData, array $items): SalesOrder
    {
        return DB::transaction(function () use ($orderData, $items) {
            // Validate stock availability first
            foreach ($items as $item) {
                $product = Product::findOrFail($item['product_id']);
                if ($product->quantity_on_hand < $item['quantity']) {
                    throw new \Exception("Insufficient stock for product: {$product->name}. Available: {$product->quantity_on_hand}, Required: {$item['quantity']}");
                }
            }

            // Calculate order totals
            $subtotal = 0;
            $totalCOGS = 0;
            foreach ($items as $item) {
                $product = Product::find($item['product_id']);
                $itemTotal = $item['quantity'] * $item['unit_price'];
                $itemCOGS = $item['quantity'] * ($item['cost_price'] ?? $product->cost_price ?? 0);

                $subtotal += $itemTotal;
                $totalCOGS += $itemCOGS;
            }

            // Apply discount
            $discountAmount = $this->calculateDiscount($subtotal, $orderData);
            $totalAmount = $subtotal - $discountAmount;

            // Create sales order
            $salesOrder = SalesOrder::create([
                'store_id' => $orderData['store_id'] ?? auth()->user()->currentStoreId(),
                'customer_id' => $orderData['customer_id'] ?? null,
                'order_date' => $orderData['order_date'] ?? Carbon::now(),
                'status' => $orderData['status'] ?? 'confirmed',
                'payment_method' => $orderData['payment_method'] ?? 'cash',
                'paid_amount' => $orderData['paid_amount'] ?? 0,
                'subtotal' => $subtotal,
                'discount_type' => $orderData['discount_type'] ?? 'none',
                'discount_rate' => $orderData['discount_rate'] ?? 0,
                'discount_amount' => $discountAmount,
                'discount_reason' => $orderData['discount_reason'] ?? null,
                'total_amount' => $totalAmount,
                'reference_number' => $orderData['reference_number'] ?? null,
                'notes' => $orderData['notes'] ?? null,
                'hold_reason' => $orderData['hold_reason'] ?? null,
                'hold_date' => $orderData['hold_date'] ?? null,
                'held_by' => $orderData['held_by'] ?? null,
            ]);

            // Calculate payment status
            $this->updatePaymentStatus($salesOrder, $orderData['paid_amount'] ?? 0);

            // Create order items
            foreach ($items as $item) {
                $this->createOrderItem($salesOrder, $item);
            }

            // Recalculate with actual COGS
            $salesOrder->calculateTotals();
            $salesOrder->save();

            return $salesOrder->fresh(['items', 'customer']);
        });
    }

    /**
     * Update an existing sales order
     *
     * @param SalesOrder $salesOrder
     * @param array $orderData
     * @param array $items
     * @return SalesOrder
     * @throws \Exception
     */
    public function updateSalesOrder(SalesOrder $salesOrder, array $orderData, array $items): SalesOrder
    {
        return DB::transaction(function () use ($salesOrder, $orderData, $items) {
            // Update basic order info
            $salesOrder->update([
                'customer_id' => $orderData['customer_id'] ?? $salesOrder->customer_id,
                'order_date' => $orderData['order_date'] ?? $salesOrder->order_date,
                'status' => $orderData['status'] ?? $salesOrder->status,
                'payment_method' => $orderData['payment_method'] ?? $salesOrder->payment_method,
                'paid_amount' => $orderData['paid_amount'] ?? $salesOrder->paid_amount,
                'discount_type' => $orderData['discount_type'] ?? $salesOrder->discount_type,
                'discount_rate' => $orderData['discount_rate'] ?? $salesOrder->discount_rate,
                'discount_reason' => $orderData['discount_reason'] ?? $salesOrder->discount_reason,
                'reference_number' => $orderData['reference_number'] ?? $salesOrder->reference_number,
                'notes' => $orderData['notes'] ?? $salesOrder->notes,
            ]);

            // Handle item updates
            $existingItems = $salesOrder->items->keyBy('id');
            $submittedItemIds = collect($items)->pluck('id')->filter();

            // Delete removed items and restore stock
            foreach ($existingItems as $existingItem) {
                if (!$submittedItemIds->contains($existingItem->id)) {
                    $this->deleteOrderItem($existingItem, $salesOrder);
                }
            }

            // Update or create items
            foreach ($items as $itemData) {
                if (isset($itemData['id']) && $existingItems->has($itemData['id'])) {
                    $this->updateOrderItem($existingItems->get($itemData['id']), $itemData, $salesOrder);
                } else {
                    $this->createOrderItem($salesOrder, $itemData);
                }
            }

            // Recalculate totals and payment status
            $salesOrder->calculateTotals();
            $this->updatePaymentStatus($salesOrder, $orderData['paid_amount'] ?? $salesOrder->paid_amount);
            $salesOrder->save();

            return $salesOrder->fresh(['items', 'customer']);
        });
    }

    /**
     * Create an order item and handle stock movement
     *
     * @param SalesOrder $salesOrder
     * @param array $itemData
     * @return SalesOrderItem
     */
    private function createOrderItem(SalesOrder $salesOrder, array $itemData): SalesOrderItem
    {
        $product = Product::findOrFail($itemData['product_id']);

        // Validate stock
        if ($product->quantity_on_hand < $itemData['quantity']) {
            throw new \Exception("Insufficient stock for product: {$product->name}");
        }

        // Create sales order item
        $orderItem = SalesOrderItem::create([
            'store_id' => $salesOrder->store_id,
            'sales_order_id' => $salesOrder->id,
            'product_id' => $itemData['product_id'],
            'quantity' => $itemData['quantity'],
            'unit_price' => $itemData['unit_price'],
            'cost_price' => $itemData['cost_price'] ?? $product->cost_price ?? 0,
            'total_price' => $itemData['quantity'] * $itemData['unit_price'],
            'final_price' => $itemData['quantity'] * $itemData['unit_price'],
            'discount_type' => $itemData['discount_type'] ?? 'none',
            'discount_rate' => $itemData['discount_rate'] ?? 0,
            'discount_reason' => $itemData['discount_reason'] ?? null,
        ]);

        // Create stock movement (decrease inventory)
        if ($salesOrder->status !== 'on_hold') {
            $this->createStockMovement([
                'product_id' => $itemData['product_id'],
                'type' => 'out',
                'quantity' => $itemData['quantity'],
                'reference_type' => 'sales_order',
                'reference_id' => $salesOrder->id,
                'notes' => "Sale - Order #{$salesOrder->order_number}",
            ]);
        }

        return $orderItem;
    }

    /**
     * Update an existing order item
     *
     * @param SalesOrderItem $orderItem
     * @param array $itemData
     * @param SalesOrder $salesOrder
     * @return SalesOrderItem
     */
    private function updateOrderItem(SalesOrderItem $orderItem, array $itemData, SalesOrder $salesOrder): SalesOrderItem
    {
        $oldQuantity = $orderItem->quantity;
        $newQuantity = $itemData['quantity'];
        $quantityDiff = $newQuantity - $oldQuantity;

        // Update item
        $orderItem->update([
            'product_id' => $itemData['product_id'],
            'quantity' => $newQuantity,
            'unit_price' => $itemData['unit_price'],
            'cost_price' => $itemData['cost_price'] ?? $orderItem->cost_price,
            'total_price' => $newQuantity * $itemData['unit_price'],
            'final_price' => $newQuantity * $itemData['unit_price'],
            'discount_type' => $itemData['discount_type'] ?? 'none',
            'discount_rate' => $itemData['discount_rate'] ?? 0,
            'discount_reason' => $itemData['discount_reason'] ?? null,
        ]);

        // Adjust stock if quantity changed and order is not on hold
        if ($quantityDiff != 0 && $salesOrder->status !== 'on_hold') {
            $this->createStockMovement([
                'product_id' => $itemData['product_id'],
                'type' => $quantityDiff > 0 ? 'out' : 'in',
                'quantity' => abs($quantityDiff),
                'reference_type' => 'sales_order_adjustment',
                'reference_id' => $salesOrder->id,
                'notes' => "Quantity adjustment - Order #{$salesOrder->order_number}",
            ]);
        }

        return $orderItem;
    }

    /**
     * Delete an order item and restore stock
     *
     * @param SalesOrderItem $orderItem
     * @param SalesOrder $salesOrder
     * @return void
     */
    private function deleteOrderItem(SalesOrderItem $orderItem, SalesOrder $salesOrder): void
    {
        // Restore stock if order is not on hold
        if ($salesOrder->status !== 'on_hold') {
            $this->createStockMovement([
                'product_id' => $orderItem->product_id,
                'type' => 'in',
                'quantity' => $orderItem->quantity,
                'reference_type' => 'sales_order_adjustment',
                'reference_id' => $salesOrder->id,
                'notes' => "Returned stock - Item removed from Order #{$salesOrder->order_number}",
            ]);
        }

        $orderItem->delete();
    }

    /**
     * Create a stock movement record
     *
     * @param array $data
     * @return StockMovement
     */
    private function createStockMovement(array $data): StockMovement
    {
        return StockMovement::create($data);
    }

    /**
     * Calculate discount amount based on type
     *
     * @param float $subtotal
     * @param array $orderData
     * @return float
     */
    private function calculateDiscount(float $subtotal, array $orderData): float
    {
        $discountType = $orderData['discount_type'] ?? 'none';
        $discountRate = $orderData['discount_rate'] ?? 0;

        if ($discountType === 'percentage' && $discountRate) {
            return $subtotal * ($discountRate / 100);
        } elseif ($discountType === 'fixed') {
            return $discountRate;
        }

        // Handle old format (percentage_discount and fixed_discount)
        if (isset($orderData['percentage_discount'])) {
            return $subtotal * ($orderData['percentage_discount'] / 100);
        } elseif (isset($orderData['fixed_discount'])) {
            return $orderData['fixed_discount'];
        }

        return 0;
    }

    /**
     * Update payment status based on paid amount
     *
     * @param SalesOrder $salesOrder
     * @param float $paidAmount
     * @return void
     */
    private function updatePaymentStatus(SalesOrder $salesOrder, float $paidAmount): void
    {
        if ($paidAmount >= $salesOrder->total_amount) {
            $salesOrder->payment_status = 'paid';
            $salesOrder->change_amount = $paidAmount - $salesOrder->total_amount;
        } elseif ($paidAmount > 0) {
            $salesOrder->payment_status = 'partial';
            $salesOrder->change_amount = 0;
        } else {
            $salesOrder->payment_status = 'pending';
            $salesOrder->change_amount = 0;
        }

        if ($paidAmount > 0 && !$salesOrder->payment_date) {
            $salesOrder->payment_date = now();
        }
    }

    /**
     * Hold an order
     *
     * @param int $orderId
     * @param string $reason
     * @return SalesOrder
     */
    public function holdOrder(int $orderId, string $reason): SalesOrder
    {
        $salesOrder = SalesOrder::findOrFail($orderId);

        if ($salesOrder->status === 'on_hold') {
            throw new \Exception('Order is already on hold.');
        }

        $salesOrder->update([
            'status' => 'on_hold',
            'hold_reason' => $reason,
            'hold_date' => now(),
            'held_by' => auth()->user()->name ?? auth()->id(),
        ]);

        return $salesOrder;
    }

    /**
     * Release an order from hold
     *
     * @param int $orderId
     * @return SalesOrder
     */
    public function releaseOrder(int $orderId): SalesOrder
    {
        $salesOrder = SalesOrder::findOrFail($orderId);

        if ($salesOrder->status !== 'on_hold') {
            throw new \Exception('Order is not on hold.');
        }

        $salesOrder->update([
            'status' => 'pending',
            'release_date' => now(),
            'released_by' => auth()->id(),
        ]);

        return $salesOrder;
    }
}
