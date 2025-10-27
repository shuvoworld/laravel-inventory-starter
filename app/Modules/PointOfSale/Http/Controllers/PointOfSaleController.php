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

    private function addItemToCart($product, $quantity, $variant = null)
    {
        $cart = $this->getCart();

        // Create unique cart key: product_id or product_id-variant_id
        $cartKey = $variant ? "{$product->id}-{$variant->id}" : (string)$product->id;

        if (isset($cart[$cartKey])) {
            $cart[$cartKey]['quantity'] += $quantity;
        } else {
            if ($variant) {
                // Adding variant to cart
                $cart[$cartKey] = [
                    'product_id' => $product->id,
                    'variant_id' => $variant->id,
                    'product_name' => $product->name,
                    'variant_name' => $variant->variant_name,
                    'name' => $product->name . ' (' . $variant->variant_name . ')',
                    'sku' => $variant->sku ?? $product->sku,
                    'price' => $variant->getEffectiveTargetPrice(),
                    'cost_price' => $variant->getEffectiveCostPrice(),
                    'floor_price' => $variant->getEffectiveFloorPrice(),
                    'target_price' => $variant->getEffectiveTargetPrice(),
                    'quantity' => $quantity,
                    'image' => $variant->image_url ?? $product->image_url,
                    'brand' => $product->brand ? $product->brand->name : null,
                ];
            } else {
                // Adding regular product to cart
                $cart[$cartKey] = [
                    'product_id' => $product->id,
                    'variant_id' => null,
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
        }

        $this->setCart($cart);
        return $cart[$cartKey];
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
            ->orWhereHas('activeVariants', function($q) {
                $q->where('quantity_on_hand', '>', 0);
            })
            ->with(['brand', 'activeVariants.optionValues.option'])
            ->orderBy('name')
            ->take(20)
            ->get();

        $categories = $this->getProductCategories();
        $recentProducts = Product::where('quantity_on_hand', '>', 0)
            ->orWhereHas('activeVariants', function($q) {
                $q->where('quantity_on_hand', '>', 0);
            })
            ->with(['brand', 'activeVariants.optionValues.option'])
            ->orderBy('updated_at', 'desc')
            ->take(12)
            ->get();

        // Prepare products data for JavaScript
        $featuredProductsData = $featuredProducts->map(function($product) {
            return $this->formatProductForPOS($product);
        });

        return view('point-of-sale::index', compact('featuredProductsData', 'categories', 'recentProducts'));
    }

    /**
     * Format product data for POS with variant support
     */
    private function formatProductForPOS($product): array
    {
        $data = [
            'id' => $product->id,
            'name' => $product->name,
            'sku' => $product->sku,
            'has_variants' => $product->has_variants,
            'image' => $product->image_url,
            'brand' => $product->brand ? $product->brand->name : null,
        ];

        if ($product->has_variants && $product->activeVariants->isNotEmpty()) {
            // Product has variants - show aggregate data
            $data['price'] = $product->activeVariants->min(function($v) {
                return $v->getEffectiveTargetPrice();
            });
            $data['max_price'] = $product->activeVariants->max(function($v) {
                return $v->getEffectiveTargetPrice();
            });
            $data['quantity'] = $product->activeVariants->sum('quantity_on_hand');
            $data['variants_count'] = $product->activeVariants->count();
            $data['variants'] = $product->activeVariants->map(function($variant) {
                return [
                    'id' => $variant->id,
                    'name' => $variant->variant_name,
                    'sku' => $variant->sku,
                    'price' => $variant->getEffectiveTargetPrice(),
                    'floor_price' => $variant->getEffectiveFloorPrice(),
                    'target_price' => $variant->getEffectiveTargetPrice(),
                    'cost_price' => $variant->getEffectiveCostPrice(),
                    'quantity' => $variant->quantity_on_hand,
                    'image' => $variant->image_url,
                    'is_default' => $variant->is_default,
                    'options' => $variant->optionValues->map(function($optionValue) {
                        return [
                            'option' => $optionValue->option->name,
                            'value' => $optionValue->value
                        ];
                    }),
                ];
            })->toArray();
        } else {
            // Regular product without variants
            $data['price'] = $product->target_price ?? $product->price;
            $data['floor_price'] = $product->floor_price;
            $data['target_price'] = $product->target_price;
            $data['quantity'] = $product->quantity_on_hand;
            $data['variants'] = [];
        }

        return $data;
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

        $products = Product::where(function($q) {
                $q->where('quantity_on_hand', '>', 0)
                  ->orWhereHas('activeVariants', function($vq) {
                      $vq->where('quantity_on_hand', '>', 0);
                  });
            })
            ->with(['brand', 'activeVariants.optionValues.option'])
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
                return $this->formatProductForPOS($product);
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
            'variant_id' => 'nullable|exists:product_variants,id',
            'quantity' => 'required|integer|min:1'
        ]);

        $product = Product::with('activeVariants')->findOrFail($request->product_id);
        $variant = null;
        $availableStock = 0;

        // Check if adding a variant or regular product
        if ($request->variant_id) {
            $variant = \App\Modules\Products\Models\ProductVariant::findOrFail($request->variant_id);

            // Verify variant belongs to product
            if ($variant->product_id != $product->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid variant for this product.'
                ], 400);
            }

            $availableStock = $variant->quantity_on_hand;
            $cartKey = "{$product->id}-{$variant->id}";
        } else {
            // Regular product without variants
            if ($product->has_variants) {
                return response()->json([
                    'success' => false,
                    'message' => 'Please select a variant for this product.'
                ], 400);
            }

            $availableStock = $product->quantity_on_hand;
            $cartKey = (string)$product->id;
        }

        // Check stock availability
        if ($availableStock < $request->quantity) {
            return response()->json([
                'success' => false,
                'message' => 'Insufficient stock. Only ' . $availableStock . ' units available.'
            ], 400);
        }

        // Check cart quantity + new quantity
        $cart = $this->getCart();
        $currentQuantity = $cart[$cartKey]['quantity'] ?? 0;
        $newQuantity = $currentQuantity + $request->quantity;

        if ($availableStock < $newQuantity) {
            return response()->json([
                'success' => false,
                'message' => 'Insufficient stock. Only ' . $availableStock . ' units available.'
            ], 400);
        }

        $this->addItemToCart($product, $request->quantity, $variant);

        return response()->json([
            'success' => true,
            'message' => $variant ? 'Variant added to cart' : 'Product added to cart',
            'cart' => $this->getCartData(),
            'cart_count' => $this->getCartCount()
        ]);
    }

    public function updateCart(Request $request): JsonResponse
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'variant_id' => 'nullable|exists:product_variants,id',
            'quantity' => 'nullable|integer|min:1',
            'custom_price' => 'nullable|numeric|min:0'
        ]);

        // Create cart key based on whether it's a variant or not
        $cartKey = $request->variant_id ? "{$request->product_id}-{$request->variant_id}" : (string)$request->product_id;
        $cart = $this->getCart();

        if (!isset($cart[$cartKey])) {
            return response()->json([
                'success' => false,
                'message' => 'Item not found in cart'
            ], 404);
        }

        // Update quantity if provided
        if ($request->has('quantity')) {
            $quantity = $request->quantity;
            $availableStock = 0;

            if ($request->variant_id) {
                $variant = \App\Modules\Products\Models\ProductVariant::findOrFail($request->variant_id);
                $availableStock = $variant->quantity_on_hand;
            } else {
                $product = Product::findOrFail($request->product_id);
                $availableStock = $product->quantity_on_hand;
            }

            if ($availableStock < $quantity) {
                return response()->json([
                    'success' => false,
                    'message' => 'Insufficient stock. Only ' . $availableStock . ' units available.'
                ], 400);
            }

            $cart[$cartKey]['quantity'] = $quantity;
        }

        // Update custom price if provided
        if ($request->has('custom_price')) {
            $customPrice = $request->custom_price;
            $floorPrice = 0;

            if ($request->variant_id) {
                $variant = \App\Modules\Products\Models\ProductVariant::findOrFail($request->variant_id);
                $floorPrice = $variant->getEffectiveFloorPrice();
            } else {
                $product = Product::findOrFail($request->product_id);
                $floorPrice = $product->floor_price;
            }

            // Validate that custom price is not below floor price
            if ($floorPrice && $customPrice < $floorPrice) {
                return response()->json([
                    'success' => false,
                    'message' => 'Price cannot be below floor price (' . number_format($floorPrice, 2) . ')'
                ], 400);
            }

            $cart[$cartKey]['price'] = $customPrice;
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
            'product_id' => 'required|exists:products,id',
            'variant_id' => 'nullable|exists:product_variants,id'
        ]);

        // Create cart key based on whether it's a variant or not
        $cartKey = $request->variant_id ? "{$request->product_id}-{$request->variant_id}" : (string)$request->product_id;

        $this->removeItemFromCart($cartKey);

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
                    'product_id' => $item['product_id'],
                    'variant_id' => $item['variant_id'] ?? null,
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['price'],
                    'cost_price' => $item['cost_price'],
                    'total_price' => $itemTotal,
                    'final_price' => $itemTotal,
                    'cogs_amount' => $itemCogs,
                    'profit_amount' => $itemProfit,
                ]);

                // Update stock using StockMovementService for proper tracking
                \App\Services\StockMovementService::recordSale(
                    $item['product_id'],
                    $item['variant_id'] ?? null,
                    $item['quantity'],
                    $salesOrder->id,
                    "POS Sale - Order #{$salesOrder->order_number}"
                );
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

        foreach ($cart as $cartKey => $item) {
            $itemSubtotal = $item['price'] * $item['quantity'];
            $subtotal += $itemSubtotal;

            $items[] = [
                'cart_key' => $cartKey, // Add cart key for operations
                'id' => $item['product_id'], // Use product_id for id field
                'product_id' => $item['product_id'],
                'variant_id' => $item['variant_id'] ?? null,
                'name' => $item['name'],
                'variant_name' => $item['variant_name'] ?? null, // Add variant name
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