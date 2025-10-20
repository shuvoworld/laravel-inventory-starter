<?php

namespace App\Modules\PointOfSale\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Products\Models\Product;
use App\Modules\Customers\Models\Customer;
use App\Modules\SalesOrder\Models\SalesOrder;
use App\Modules\SalesOrderItem\Models\SalesOrderItem;
use App\Services\SalesService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Session;
use Carbon\Carbon;

class PointOfSaleController extends Controller
{
    private function getCart()
    {
        return session('pos_cart', []);
    }

    private function setCart($cart)
    {
        session(['pos_cart' => $cart]);
    }

    private function clearCartSession()
    {
        session()->forget('pos_cart');
    }

    private function addItemToCart($product, $quantity)
    {
        $cart = $this->getCart();
        $productId = $product->id;

        if (isset($cart[$productId])) {
            $cart[$productId]['quantity'] += $quantity;
        } else {
            $cart[$productId] = [
                'id' => $product->id,
                'name' => $product->name,
                'sku' => $product->sku,
                'price' => $product->target_price ?? $product->price,
                'cost_price' => $product->cost_price,
                'floor_price' => $product->floor_price,
                'target_price' => $product->target_price,
                'quantity' => $quantity,
                'image' => $product->image_url,
                'brand' => $product->brand ? $product->brand->name : null,
            ];
        }

        $this->setCart($cart);
        return $cart[$productId];
    }

    private function updateCartItem($productId, $quantity)
    {
        $cart = $this->getCart();

        if (isset($cart[$productId])) {
            $cart[$productId]['quantity'] = $quantity;
            $this->setCart($cart);
            return $cart[$productId];
        }

        return null;
    }

    private function removeItemFromCart($productId)
    {
        $cart = $this->getCart();

        if (isset($cart[$productId])) {
            unset($cart[$productId]);
            $this->setCart($cart);
            return true;
        }

        return false;
    }

    public function index(): View
    {
        // Clear any existing cart for fresh POS session
        $this->clearCartSession();

        // Get featured products and categories
        $featuredProducts = Product::where('quantity_on_hand', '>', 0)
            ->with('brand')
            ->orderBy('name')
            ->take(20)
            ->get();

        $categories = $this->getProductCategories();
        $recentProducts = Product::where('quantity_on_hand', '>', 0)
            ->with('brand')
            ->orderBy('updated_at', 'desc')
            ->take(12)
            ->get();

        // Prepare products data for JavaScript
        $featuredProductsData = $featuredProducts->map(function($product) {
            return [
                'id' => $product->id,
                'name' => $product->name,
                'sku' => $product->sku,
                'price' => $product->target_price ?? $product->price,
                'floor_price' => $product->floor_price,
                'target_price' => $product->target_price,
                'quantity' => $product->quantity_on_hand,
                'image' => $product->image_url,
                'brand' => $product->brand ? $product->brand->name : null
            ];
        });

        return view('point-of-sale::index', compact('featuredProductsData', 'categories', 'recentProducts'));
    }

    public function pos2(): View
    {
        // Clear any existing cart for fresh POS session
        $this->clearCartSession();

        // Get featured products and categories
        $featuredProducts = Product::where('quantity_on_hand', '>', 0)
            ->with('brand')
            ->orderBy('name')
            ->take(20)
            ->get();

        $categories = $this->getProductCategories();
        $recentProducts = Product::where('quantity_on_hand', '>', 0)
            ->with('brand')
            ->orderBy('updated_at', 'desc')
            ->take(12)
            ->get();

        // Prepare products data for JavaScript
        $featuredProductsData = $featuredProducts->map(function($product) {
            return [
                'id' => $product->id,
                'name' => $product->name,
                'sku' => $product->sku,
                'price' => $product->target_price ?? $product->price,
                'floor_price' => $product->floor_price,
                'target_price' => $product->target_price,
                'quantity' => $product->quantity_on_hand,
                'image' => $product->image_url,
                'brand' => $product->brand ? $product->brand->name : null
            ];
        });

        return view('point-of-sale::pos2', compact('featuredProductsData', 'categories', 'recentProducts'));
    }

    public function search(Request $request): JsonResponse
    {
        $query = $request->get('q', '');
        $categoryId = $request->get('category', '');

        $products = Product::where('quantity_on_hand', '>', 0)
            ->with('brand')
            ->where(function ($q) use ($query) {
                $q->where('name', 'LIKE', "%{$query}%")
                  ->orWhere('sku', 'LIKE', "%{$query}%");
            })
            ->when($categoryId && $categoryId !== 'all', function ($q) use ($categoryId) {
                return $q->whereHas('brand', function ($brandQuery) use ($categoryId) {
                    $brandQuery->where('id', $categoryId);
                });
            })
            ->orderBy('name')
            ->take(50)
            ->get()
            ->map(function ($product) {
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'sku' => $product->sku,
                    'price' => $product->target_price ?? $product->price,
                    'cost_price' => $product->cost_price,
                    'floor_price' => $product->floor_price,
                    'target_price' => $product->target_price,
                    'quantity' => $product->quantity_on_hand,
                    'image' => $product->image_url,
                    'brand' => $product->brand ? $product->brand->name : null,
                ];
            });

        return response()->json([
            'products' => $products,
            'count' => $products->count()
        ]);
    }

    public function addToCart(Request $request): JsonResponse
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1'
        ]);

        $product = Product::findOrFail($request->product_id);

        if ($product->quantity_on_hand < $request->quantity) {
            return response()->json([
                'success' => false,
                'message' => 'Insufficient stock. Only ' . $product->quantity_on_hand . ' units available.'
            ], 400);
        }

        $cart = $this->getCart();
        $currentQuantity = $cart[$product->id]['quantity'] ?? 0;
        $newQuantity = $currentQuantity + $request->quantity;

        if ($product->quantity_on_hand < $newQuantity) {
            return response()->json([
                'success' => false,
                'message' => 'Insufficient stock. Only ' . $product->quantity_on_hand . ' units available.'
            ], 400);
        }

        $this->addItemToCart($product, $request->quantity);

        return response()->json([
            'success' => true,
            'message' => 'Product added to cart',
            'cart' => $this->getCartData(),
            'cart_count' => $this->getCartCount()
        ]);
    }

    public function updateCart(Request $request): JsonResponse
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'nullable|integer|min:1',
            'custom_price' => 'nullable|numeric|min:0'
        ]);

        $productId = $request->product_id;
        $cart = $this->getCart();

        if (!isset($cart[$productId])) {
            return response()->json([
                'success' => false,
                'message' => 'Item not found in cart'
            ], 404);
        }

        // Update quantity if provided
        if ($request->has('quantity')) {
            $quantity = $request->quantity;
            $product = Product::findOrFail($productId);

            if ($product->quantity_on_hand < $quantity) {
                return response()->json([
                    'success' => false,
                    'message' => 'Insufficient stock. Only ' . $product->quantity_on_hand . ' units available.'
                ], 400);
            }

            $cart[$productId]['quantity'] = $quantity;
        }

        // Update custom price if provided
        if ($request->has('custom_price')) {
            $customPrice = $request->custom_price;
            $product = Product::findOrFail($productId);

            // Validate that custom price is not below floor price
            if ($product->floor_price && $customPrice < $product->floor_price) {
                return response()->json([
                    'success' => false,
                    'message' => 'Price cannot be below floor price (' . number_format($product->floor_price, 2) . ')'
                ], 400);
            }

            $cart[$productId]['price'] = $customPrice;
        }

        $this->setCart($cart);

        return response()->json([
            'success' => true,
            'message' => 'Cart updated',
            'cart' => $this->getCartData()
        ]);
    }

    public function removeFromCart(Request $request): JsonResponse
    {
        $request->validate([
            'product_id' => 'required|exists:products,id'
        ]);

        $this->removeItemFromCart($request->product_id);

        return response()->json([
            'success' => true,
            'message' => 'Item removed from cart',
            'cart' => $this->getCartData(),
            'cart_count' => $this->getCartCount()
        ]);
    }

    public function clearCart(Request $request): JsonResponse
    {
        $this->clearCartSession();

        return response()->json([
            'success' => true,
            'message' => 'Cart cleared',
            'cart' => $this->getCartData(),
            'cart_count' => 0
        ]);
    }

    public function applyDiscount(Request $request): JsonResponse
    {
        $request->validate([
            'type' => 'required|in:percentage,fixed',
            'value' => 'required|numeric|min:0'
        ]);

        if ($request->type === 'percentage') {
            session(['pos_percentage_discount' => $request->value]);
            session()->forget('pos_fixed_discount');
        } else {
            session(['pos_fixed_discount' => $request->value]);
            session()->forget('pos_percentage_discount');
        }

        return response()->json([
            'success' => true,
            'message' => 'Discount applied',
            'cart' => $this->getCartData()
        ]);
    }

    public function completePayment(Request $request): JsonResponse
    {
        $request->validate([
            'payment_method' => 'required|in:cash,card,mobile_banking,bank_transfer,cheque',
            'customer_id' => 'nullable|exists:customers,id',
            'paid_amount' => 'required|numeric|min:0',
            'change_amount' => 'nullable|numeric',
            'adjustment_amount' => 'nullable|numeric',
            'adjustment_reason' => 'nullable|string|max:255',
            'discount_value' => 'nullable|numeric|min:0',
            'discount_type' => 'nullable|in:percentage,fixed,none'
        ]);

        $cart = $this->getCart();
        if (empty($cart)) {
            return response()->json([
                'success' => false,
                'message' => 'Cart is empty'
            ], 400);
        }

        try {
            $cartData = $this->getCartData();

            // Get customer_id from request or session
            $customerId = $request->customer_id ?? session('pos_customer_id');

            // Calculate COGS and profit
            $cogsAmount = 0;
            foreach ($cart as $item) {
                $cogsAmount += ($item['cost_price'] * $item['quantity']);
            }

            // Calculate discount from request values (from direct input fields)
            $cartSubtotal = collect($cart)->sum(function($item) {
                return $item['price'] * $item['quantity'];
            });

            $discountValue = $request->discount_value ?? 0;
            $discountType = $request->discount_type ?? 'none';

            $discountAmount = 0;
            if ($discountType === 'percentage' && $discountValue > 0) {
                $discountAmount = $cartSubtotal * ($discountValue / 100);
            } elseif ($discountType === 'fixed' && $discountValue > 0) {
                $discountAmount = $discountValue;
            }

            $adjustmentAmount = $request->adjustment_amount ?? 0;

            // Calculate total with adjustment
            $subtotalAfterDiscount = $cartSubtotal - $discountAmount;
            $totalAmount = $subtotalAfterDiscount - $adjustmentAmount;
            $profitAmount = $totalAmount - $cogsAmount;

            // Determine payment status and order status
            $paidAmount = $request->paid_amount;

            // Payment status logic
            if ($paidAmount >= $totalAmount) {
                $paymentStatus = 'paid';
                $orderStatus = 'delivered'; // Delivered for POS transactions (items given to customer immediately)
            } elseif ($paidAmount > 0) {
                $paymentStatus = 'partial';
                $orderStatus = 'confirmed'; // Confirmed but not complete
            } else {
                $paymentStatus = 'pending';
                $orderStatus = 'pending';
            }

            // Create sales order
            $salesOrder = SalesOrder::create([
                'store_id' => auth()->user()->currentStoreId(),
                'customer_id' => $customerId,
                'order_date' => Carbon::now(),
                'status' => $orderStatus,
                'payment_method' => $request->payment_method,
                'payment_status' => $paymentStatus,
                'paid_amount' => $paidAmount,
                'change_amount' => $request->change_amount ?? 0,
                'subtotal' => $cartSubtotal,
                'discount_amount' => $discountAmount,
                'discount_type' => $discountType,
                'discount_rate' => $discountValue,
                'total_amount' => $totalAmount,
                'cogs_amount' => $cogsAmount,
                'profit_amount' => $profitAmount,
                'notes' => $request->adjustment_reason ? 'Adjustment: ' . $request->adjustment_reason . ' (-$' . number_format($adjustmentAmount, 2) . ')' : null,
            ]);

            // Add order items
            foreach ($cart as $item) {
                $itemTotal = $item['price'] * $item['quantity'];
                $itemCogs = $item['cost_price'] * $item['quantity'];
                $itemProfit = $itemTotal - $itemCogs;

                SalesOrderItem::create([
                    'store_id' => auth()->user()->currentStoreId(),
                    'sales_order_id' => $salesOrder->id,
                    'product_id' => $item['id'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['price'],
                    'cost_price' => $item['cost_price'],
                    'total_price' => $itemTotal,
                    'final_price' => $itemTotal,
                    'cogs_amount' => $itemCogs,
                    'profit_amount' => $itemProfit,
                ]);

                // Update product stock
                $product = Product::find($item['id']);
                $product->decrement('quantity_on_hand', $item['quantity']);
            }

            // Clear cart and session data
            $this->clearCartSession();
            session()->forget('pos_fixed_discount');
            session()->forget('pos_percentage_discount');
            session()->forget('pos_customer_id');

            return response()->json([
                'success' => true,
                'message' => 'Payment completed successfully',
                'order_id' => $salesOrder->id,
                'order_number' => $salesOrder->order_number
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error completing payment: ' . $e->getMessage()
            ], 500);
        }
    }

    public function searchCustomers(Request $request): JsonResponse
    {
        $query = $request->get('q', '');

        $customers = Customer::where('store_id', auth()->user()->currentStoreId())
            ->where(function ($q) use ($query) {
                $q->where('name', 'LIKE', "%{$query}%")
                  ->orWhere('email', 'LIKE', "%{$query}%")
                  ->orWhere('phone', 'LIKE', "%{$query}%");
            })
            ->orderBy('name')
            ->take(10)
            ->get()
            ->map(function ($customer) {
                return [
                    'id' => $customer->id,
                    'name' => $customer->name,
                    'email' => $customer->email,
                    'phone' => $customer->phone,
                ];
            });

        return response()->json([
            'customers' => $customers
        ]);
    }

    public function addCustomer(Request $request): JsonResponse
    {
        $request->validate([
            'customer_id' => 'required|exists:customers,id'
        ]);

        session(['pos_customer_id' => $request->customer_id]);

        $customer = Customer::find($request->customer_id);

        return response()->json([
            'success' => true,
            'message' => 'Customer added to order',
            'customer' => [
                'id' => $customer->id,
                'name' => $customer->name,
                'email' => $customer->email,
                'phone' => $customer->phone,
            ]
        ]);
    }

    public function removeCustomer(Request $request): JsonResponse
    {
        session()->forget('pos_customer_id');

        return response()->json([
            'success' => true,
            'message' => 'Customer removed from order'
        ]);
    }

    public function holdOrder(Request $request): JsonResponse
    {
        $request->validate([
            'hold_reason' => 'required|string|max:500',
            'send_notification' => 'boolean',
            'customer_id' => 'nullable|exists:customers,id'
        ]);

        $cart = $this->getCart();
        if (empty($cart)) {
            return response()->json([
                'success' => false,
                'message' => 'Cart is empty'
            ], 400);
        }

        try {
            $cartData = $this->getCartData();

            // Create sales order with hold status
            $salesOrder = SalesOrder::create([
                'store_id' => auth()->user()->currentStoreId(),
                'customer_id' => $request->customer_id,
                'order_date' => Carbon::now(),
                'status' => 'on_hold',
                'payment_status' => 'pending',
                'subtotal' => $cartData['subtotal'],
                'discount_amount' => $this->calculateTotalDiscount(),
                'total_amount' => $cartData['total'],
                'hold_reason' => $request->hold_reason,
                'hold_date' => Carbon::now(),
                'held_by' => auth()->user()->name,
            ]);

            // Add order items
            foreach ($cart as $item) {
                SalesOrderItem::create([
                    'store_id' => auth()->user()->currentStoreId(),
                    'sales_order_id' => $salesOrder->id,
                    'product_id' => $item['id'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['price'],
                    'cost_price' => $item['cost_price'],
                    'total_price' => $item['price'] * $item['quantity'],
                    'final_price' => $item['price'] * $item['quantity'],
                ]);
            }

            // Send notification if requested
            if ($request->send_notification && $request->customer_id) {
                // Here you could implement email/SMS notification logic
                // For now, we'll just log that a notification should be sent
                \Log::info("Hold order notification should be sent to customer {$request->customer_id} for order {$salesOrder->id}");
            }

            // Clear cart
            $this->clearCartSession();
            session()->forget('pos_fixed_discount');
            session()->forget('pos_percentage_discount');
            session()->forget('pos_customer_id');

            return response()->json([
                'success' => true,
                'message' => 'Order has been placed on hold successfully',
                'order_id' => $salesOrder->id,
                'order_number' => $salesOrder->order_number ?? 'ORD-' . str_pad($salesOrder->id, 6, '0', STR_PAD_LEFT)
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error holding order: ' . $e->getMessage()
            ], 500);
        }
    }

    private function getProductCategories(): array
    {
        // Get unique brands as categories
        return Product::whereHas('brand')
            ->with('brand')
            ->get()
            ->pluck('brand.name', 'brand.id')
            ->unique()
            ->toArray();
    }

    private function getCartData(): array
    {
        $cart = $this->getCart();
        $subtotal = 0;
        $items = [];

        foreach ($cart as $productId => $item) {
            $itemSubtotal = $item['price'] * $item['quantity'];
            $subtotal += $itemSubtotal;

            $items[] = [
                'id' => $item['id'],
                'name' => $item['name'],
                'sku' => $item['sku'],
                'quantity' => $item['quantity'],
                'price' => $item['price'],
                'cost_price' => $item['cost_price'],
                'floor_price' => $item['floor_price'] ?? null,
                'target_price' => $item['target_price'] ?? null,
                'subtotal' => $itemSubtotal,
                'image' => $item['image'],
                'brand' => $item['brand'],
            ];
        }

        $percentageDiscount = session('pos_percentage_discount', 0);
        $fixedDiscount = session('pos_fixed_discount', 0);

        $percentageDiscountAmount = $subtotal * ($percentageDiscount / 100);
        $totalDiscount = $percentageDiscountAmount + $fixedDiscount;
        $total = $subtotal - $totalDiscount;

        return [
            'items' => $items,
            'subtotal' => $subtotal,
            'discount' => $totalDiscount,
            'total' => $total,
            'tax' => 0,
            'count' => count($cart),
        ];
    }

    private function calculateTotalDiscount(): float
    {
        $cartData = $this->getCartData();
        return $cartData['discount'];
    }

    private function getCartCount(): int
    {
        return count($this->getCart());
    }

    public function printReceipt($id)
    {
        $salesOrder = SalesOrder::with(['items.product', 'customer'])
            ->where('store_id', auth()->user()->currentStoreId())
            ->findOrFail($id);

        return view('point-of-sale::print-receipt', compact('salesOrder'));
    }

    public function quickAddCustomer(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255|unique:customers,email',
            'phone' => 'required|string|max:20',
            'address' => 'nullable|string|max:500'
        ]);

        try {
            $customer = \App\Modules\Customers\Models\Customer::create([
                'store_id' => auth()->user()->currentStoreId(),
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'address' => $request->address,
                'customer_type' => 'individual',
                'status' => 'active'
            ]);

            // Add customer to POS session
            session(['pos_customer_id' => $customer->id]);

            return response()->json([
                'success' => true,
                'message' => 'Customer created successfully',
                'customer' => [
                    'id' => $customer->id,
                    'name' => $customer->name,
                    'email' => $customer->email,
                    'phone' => $customer->phone
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error creating customer: ' . $e->getMessage()
            ], 500);
        }
    }
}