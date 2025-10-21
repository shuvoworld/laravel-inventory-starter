<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Point of Sale - {{ config('app.name') }}</title>
    @vite('resources/css/app.css')
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        /* Reset and base styles */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif !important;
            background: #f8f9fa !important;
            overflow: hidden !important;
            height: 100vh !important;
            margin: 0 !important;
            padding: 0 !important;
        }

        /* Ensure no conflicting styles */
        body > *:not(.pos-container):not(.modal):not(.modal-backdrop) {
            display: none !important;
        }

        .pos-container {
            display: flex !important;
            height: 100vh !important;
            background: #ffffff !important;
            width: 100vw !important;
            position: fixed !important;
            top: 0 !important;
            left: 0 !important;
            z-index: 1000 !important;
        }

        /* Ensure modals appear above POS container */
        .modal {
            z-index: 1060 !important;
        }

        .modal-backdrop {
            z-index: 1055 !important;
        }

        /* Customer search modal styles */
        #customerResults .list-group-item:hover {
            background-color: #f8f9fa;
        }

        #customerResults .list-group-item {
            transition: background-color 0.2s ease;
        }

        /* Discount controls */
        .discount-section {
            position: relative;
        }

        .btn-edit-discount {
            background: transparent;
            border: none;
            color: #667eea;
            cursor: pointer;
            padding: 0;
            margin-right: 0.35rem;
            font-size: 0.85rem;
            transition: all 0.2s ease;
            vertical-align: middle;
        }

        .btn-edit-discount:hover {
            color: #5a67d8;
            transform: scale(1.15);
        }

        .discount-type-btn {
            transition: all 0.2s ease;
        }

        .discount-type-btn:hover {
            transform: translateY(-1px);
        }

        .discount-type-btn.active {
            background: #667eea !important;
            color: white !important;
            border-color: #667eea !important;
        }

        /* Adjustment controls */
        .adjustment-section {
            position: relative;
        }

        .btn-edit-adjustment {
            background: transparent;
            border: none;
            color: #f59e0b;
            cursor: pointer;
            padding: 0;
            margin-right: 0.35rem;
            font-size: 0.85rem;
            transition: all 0.2s ease;
            vertical-align: middle;
        }

        .btn-edit-adjustment:hover {
            color: #d97706;
            transform: scale(1.15);
        }

        /* Left Panel - Product Grid Area */
        .product-area {
            flex: 1 !important;
            display: flex !important;
            flex-direction: column !important;
            border-right: 1px solid #e9ecef !important;
            background: #ffffff !important;
            min-width: 0 !important;
            overflow: hidden !important;
        }

        .pos-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 1rem 1.5rem;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .pos-header-left {
            display: flex;
            align-items: center;
            gap: 1.5rem;
            flex: 1;
        }

        .pos-header h1 {
            font-size: 1.5rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin: 0;
        }

        .customer-section-header {
            display: flex;
            align-items: center;
            gap: 1rem;
            flex: 1;
        }

        .customer-section-header .customer-btn {
            padding: 0.5rem 1rem;
            border: 2px solid white;
            background: rgba(255, 255, 255, 0.2);
            color: white;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s ease;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            white-space: nowrap;
            font-size: 0.9rem;
        }

        .customer-section-header .customer-btn:hover {
            background: rgba(255, 255, 255, 0.3);
            border-color: white;
        }

        .customer-section-header .customer-info {
            padding: 0.5rem 1rem;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 8px;
            color: white;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            font-size: 0.9rem;
        }

        .customer-section-header .customer-info .customer-name {
            font-weight: 600;
        }

        .customer-section-header .customer-info .customer-details {
            font-size: 0.85rem;
            opacity: 0.9;
        }

        .customer-section-header .remove-customer {
            background: rgba(229, 62, 62, 0.2);
            border: 1px solid rgba(255, 255, 255, 0.5);
            color: white;
            padding: 0.5rem 0.75rem;
            border-radius: 6px;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .customer-section-header .remove-customer:hover {
            background: rgba(229, 62, 62, 0.4);
        }

        .search-section {
            padding: 1rem;
            background: #f8f9fa;
            border-bottom: 1px solid #e9ecef;
        }

        .search-bar {
            position: relative;
            margin-bottom: 1rem;
        }

        .search-bar input {
            width: 100%;
            padding: 1rem 3rem 1rem 1rem;
            border: 2px solid #e9ecef;
            border-radius: 12px;
            font-size: 1rem;
            transition: all 0.3s ease;
            background: white;
        }

        .search-bar input:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        .search-bar i {
            position: absolute;
            right: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: #6c757d;
        }

        .categories-scroll {
            display: flex;
            gap: 0.5rem;
            overflow-x: auto;
            padding-bottom: 0.5rem;
            scrollbar-width: thin;
        }

        .categories-scroll::-webkit-scrollbar {
            height: 6px;
        }

        .categories-scroll::-webkit-scrollbar-thumb {
            background: #cbd5e0;
            border-radius: 3px;
        }

        .category-btn {
            padding: 0.75rem 1.5rem;
            border: 2px solid #e9ecef;
            background: white;
            border-radius: 25px;
            cursor: pointer;
            transition: all 0.3s ease;
            white-space: nowrap;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .category-btn:hover {
            border-color: #667eea;
            background: #f8f9ff;
            transform: translateY(-1px);
        }

        .category-btn.active {
            background: #667eea;
            color: white;
            border-color: #667eea;
        }

        .products-grid {
            flex: 1;
            overflow-y: auto;
            padding: 1rem;
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
            gap: 1rem;
            align-content: start;
        }

        .product-card {
            background: white;
            border: 2px solid #e9ecef;
            border-radius: 16px;
            padding: 1rem;
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .product-card:hover {
            border-color: #667eea;
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.15);
            transform: translateY(-2px);
        }

        .product-card.out-of-stock {
            opacity: 0.6;
            cursor: not-allowed;
        }

        .product-image {
            width: 100%;
            height: 120px;
            object-fit: cover;
            border-radius: 8px;
            margin-bottom: 0.75rem;
            background: #f8f9fa;
        }

        .product-name {
            font-weight: 600;
            font-size: 0.9rem;
            margin-bottom: 0.25rem;
            line-height: 1.3;
            color: #2d3748;
        }

        .product-sku {
            font-size: 0.75rem;
            color: #6c757d;
            margin-bottom: 0.5rem;
        }

        .product-price {
            font-size: 1.1rem;
            font-weight: 700;
            color: #667eea;
        }

        .stock-badge {
            position: absolute;
            top: 0.5rem;
            right: 0.5rem;
            padding: 0.25rem 0.5rem;
            border-radius: 12px;
            font-size: 0.7rem;
            font-weight: 600;
        }

        .stock-badge.low {
            background: #fed7d7;
            color: #c53030;
        }

        .stock-badge.normal {
            background: #c6f6d5;
            color: #276749;
        }

        /* Right Panel - Cart & Checkout */
        .cart-area {
            width: 420px !important;
            max-width: 420px !important;
            min-width: 420px !important;
            display: flex !important;
            flex-direction: column !important;
            background: #ffffff !important;
            flex-shrink: 0 !important;
        }

        .cart-header {
            padding: 1.5rem;
            border-bottom: 1px solid #e9ecef;
            background: #f8f9fa;
        }

        .cart-header h2 {
            font-size: 1.25rem;
            font-weight: 600;
            color: #2d3748;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .cart-items {
            flex: 1;
            overflow-y: auto;
            padding: 1rem;
        }

        .cart-item {
            display: flex;
            align-items: center;
            padding: 1rem;
            background: #f8f9fa;
            border-radius: 12px;
            margin-bottom: 0.75rem;
            transition: all 0.3s ease;
        }

        .cart-item:hover {
            background: #e9ecef;
        }

        .cart-item-image {
            width: 50px;
            height: 50px;
            object-fit: cover;
            border-radius: 8px;
            margin-right: 1rem;
        }

        .cart-item-details {
            flex: 1;
        }

        .cart-item-name {
            font-weight: 600;
            margin-bottom: 0.25rem;
            color: #2d3748;
        }

        .cart-item-price {
            color: #6c757d;
            font-size: 0.9rem;
        }

        .quantity-controls {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            background: white;
            border-radius: 8px;
            padding: 0.25rem;
            border: 1px solid #e9ecef;
        }

        .quantity-btn {
            width: 32px;
            height: 32px;
            border: none;
            background: #667eea;
            color: white;
            border-radius: 6px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
        }

        .quantity-btn:hover {
            background: #5a67d8;
            transform: scale(1.05);
        }

        .quantity-btn:active {
            transform: scale(0.95);
        }

        .quantity-display {
            min-width: 40px;
            text-align: center;
            font-weight: 600;
        }

        .remove-item {
            width: 32px;
            height: 32px;
            border: none;
            background: #e53e3e;
            color: white;
            border-radius: 6px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-left: 0.5rem;
            transition: all 0.3s ease;
        }

        .remove-item:hover {
            background: #c53030;
            transform: scale(1.05);
        }

        .empty-cart {
            text-align: center;
            padding: 3rem 1rem;
            color: #6c757d;
        }

        .empty-cart i {
            font-size: 3rem;
            margin-bottom: 1rem;
            opacity: 0.5;
        }

        .totals-section {
            padding: 1.5rem;
            border-top: 1px solid #e9ecef;
            background: #f8f9fa;
        }

        .total-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 0.75rem;
            font-size: 0.95rem;
        }

        .total-row.grand-total {
            font-size: 1.25rem;
            font-weight: 700;
            color: #2d3748;
            padding-top: 0.75rem;
            border-top: 2px solid #e9ecef;
        }

        .payment-section {
            padding: 1.5rem;
            border-top: 1px solid #e9ecef;
            background: white;
        }

        .payment-methods {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 0.75rem;
            margin-bottom: 1rem;
        }

        .payment-btn {
            padding: 1rem;
            border: 2px solid #e9ecef;
            background: white;
            border-radius: 12px;
            cursor: pointer;
            transition: all 0.3s ease;
            font-weight: 600;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 0.5rem;
        }

        .payment-btn:hover {
            border-color: #667eea;
            background: #f8f9ff;
            transform: translateY(-2px);
        }

        .payment-btn.cash {
            border-color: #48bb78;
            color: #48bb78;
        }

        .payment-btn.card {
            border-color: #4299e1;
            color: #4299e1;
        }

        .payment-btn.selected {
            background: #667eea;
            color: white;
            border-color: #667eea;
        }

        .payment-btn i {
            font-size: 1.5rem;
        }

        .complete-payment-btn {
            width: 100%;
            padding: 1.25rem;
            border: none;
            background: linear-gradient(135deg, #48bb78 0%, #38a169 100%);
            color: white;
            border-radius: 12px;
            font-size: 1.1rem;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 6px rgba(72, 187, 120, 0.3);
        }

        .complete-payment-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 12px rgba(72, 187, 120, 0.4);
        }

        .complete-payment-btn:active {
            transform: translateY(0);
        }

        .complete-payment-btn:disabled {
            background: #cbd5e0;
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
        }

        .action-buttons {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 0.75rem;
        }

        .hold-order-btn {
            width: 100%;
            padding: 1.25rem;
            border: 2px solid #f59e0b;
            background: white;
            color: #f59e0b;
            border-radius: 12px;
            font-size: 1.1rem;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 6px rgba(245, 158, 11, 0.2);
        }

        .hold-order-btn:hover {
            background: #f59e0b;
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 6px 12px rgba(245, 158, 11, 0.3);
        }

        .hold-order-btn:active {
            transform: translateY(0);
        }

        /* Loading and animations */
        .loading {
            display: inline-block;
            width: 20px;
            height: 20px;
            border: 3px solid rgba(255,255,255,.3);
            border-radius: 50%;
            border-top-color: #fff;
            animation: spin 1s ease-in-out infinite;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        .fade-in {
            animation: fadeIn 0.3s ease-in;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* Responsive adjustments */
        @media (max-width: 1024px) {
            .cart-area {
                width: 380px !important;
                max-width: 380px !important;
                min-width: 380px !important;
            }

            .products-grid {
                grid-template-columns: repeat(auto-fill, minmax(160px, 1fr)) !important;
            }

            .pos-header {
                flex-direction: column;
                gap: 1rem;
                align-items: flex-start;
            }

            .pos-header-left {
                flex-direction: column;
                width: 100%;
                align-items: flex-start;
            }

            .customer-section-header {
                width: 100%;
            }

            .customer-section-header .customer-btn {
                width: 100%;
                justify-content: center;
            }
        }

        @media (max-width: 768px) {
            .pos-container {
                flex-direction: column !important;
            }

            .cart-area {
                width: 100% !important;
                max-width: 100% !important;
                min-width: 100% !important;
                height: 40vh !important;
            }

            .product-area {
                height: 60vh !important;
            }

            .products-grid {
                grid-template-columns: repeat(auto-fill, minmax(120px, 1fr)) !important;
            }

            .pos-header h1 {
                font-size: 1.2rem;
            }

            .pos-header-left {
                gap: 0.75rem;
            }

            .customer-section-header {
                flex-direction: column;
                align-items: flex-start;
            }

            .customer-section-header .customer-info {
                font-size: 0.8rem;
                width: 100%;
            }

            .customer-section-header .customer-info .customer-name {
                font-size: 0.85rem;
            }

            .customer-section-header .customer-info .customer-details {
                font-size: 0.75rem;
            }

            .customer-section-header .customer-btn {
                font-size: 0.85rem;
                padding: 0.4rem 0.8rem;
            }
        }

        /* Touch optimizations */
        .product-card,
        .quantity-btn,
        .remove-item,
        .payment-btn,
        .complete-payment-btn {
            -webkit-tap-highlight-color: transparent;
            touch-action: manipulation;
        }

        /* Custom scrollbar */
        .products-grid::-webkit-scrollbar,
        .cart-items::-webkit-scrollbar {
            width: 8px;
        }

        .products-grid::-webkit-scrollbar-track,
        .cart-items::-webkit-scrollbar-track {
            background: #f1f1f1;
        }

        .products-grid::-webkit-scrollbar-thumb,
        .cart-items::-webkit-scrollbar-thumb {
            background: #cbd5e0;
            border-radius: 4px;
        }

        .products-grid::-webkit-scrollbar-thumb:hover,
        .cart-items::-webkit-scrollbar-thumb:hover {
            background: #a0aec0;
        }
    </style>
</head>
<body>
    <div class="pos-container">
        <!-- Left Panel - Product Grid Area -->
        <div class="product-area">
            <header class="pos-header">
                <div class="pos-header-left">
                    <h1>
                        <i class="fas fa-cash-register"></i>
                        Point of Sale
                    </h1>

                    <!-- Customer Section in Header - Moved to Left -->
                    <div class="customer-section-header">
                        <button type="button" class="customer-btn" id="addCustomerBtnHeader">
                            <i class="fas fa-user-plus"></i>
                            Find/Add Customer
                        </button>
                        <div id="customerInfoHeader" style="display: none;"></div>
                    </div>
                </div>
            </header>

            <div class="search-section">
                <div class="search-bar">
                    <input
                        type="text"
                        id="productSearch"
                        placeholder="Search products by name or SKU..."
                        autocomplete="off"
                    >
                    <i class="fas fa-search"></i>
                </div>

                <div class="categories-scroll" id="categoriesContainer">
                    <button class="category-btn active" data-category="all">
                        <i class="fas fa-th"></i>
                        All Products
                    </button>
                    @foreach($categories as $id => $name)
                        <button class="category-btn" data-category="{{ $id }}">
                            <i class="fas fa-tag"></i>
                            {{ $name }}
                        </button>
                    @endforeach
                </div>
            </div>

            <div class="products-grid" id="productsGrid">
                <!-- Products will be loaded here -->
            </div>
        </div>

        <!-- Right Panel - Cart & Checkout -->
        <div class="cart-area">
            <div class="cart-header">
                <h2>
                    <span>Current Order</span>
                    <span id="cartCount" style="background: #667eea; color: white; padding: 0.25rem 0.75rem; border-radius: 20px; font-size: 0.9rem;">0</span>
                </h2>
            </div>

            <div class="cart-items" id="cartItems">
                <div class="empty-cart">
                    <i class="fas fa-shopping-cart"></i>
                    <p>No items in cart</p>
                    <p style="font-size: 0.9rem; margin-top: 0.5rem;">Add products to start your order</p>
                </div>
            </div>

            <div class="totals-section">
                <div class="total-row">
                    <span>Subtotal:</span>
                    <span id="subtotal">$0.00</span>
                </div>
                <div class="total-row">
                    <span>Tax (10%):</span>
                    <span id="tax">$0.00</span>
                </div>

                <!-- Discount Section -->
                <div class="total-row discount-section">
                    <span>
                        <button type="button" class="btn-edit-discount" id="editDiscountBtn" title="Add/Edit Discount">
                            <i class="fas fa-edit"></i>
                        </button>
                        Discount:
                    </span>
                    <span id="discount" style="color: #e53e3e;">-$0.00</span>
                </div>

                <div id="discountControls" style="display: none; padding: 0.75rem 0; border-top: 1px solid #e9ecef; border-bottom: 1px solid #e9ecef; margin: 0.5rem 0;">
                    <div class="discount-type-selector" style="display: flex; gap: 0.5rem; margin-bottom: 0.75rem;">
                        <button type="button" class="discount-type-btn active" data-type="percentage" style="flex: 1; padding: 0.5rem; border: 2px solid #667eea; background: #667eea; color: white; border-radius: 6px; cursor: pointer; font-size: 0.85rem; font-weight: 600;">
                            Percentage (%)
                        </button>
                        <button type="button" class="discount-type-btn" data-type="fixed" style="flex: 1; padding: 0.5rem; border: 2px solid #e9ecef; background: white; color: #2d3748; border-radius: 6px; cursor: pointer; font-size: 0.85rem; font-weight: 600;">
                            Fixed ($)
                        </button>
                    </div>
                    <div style="display: flex; gap: 0.5rem; align-items: center;">
                        <input type="number" id="discountValue" placeholder="0" min="0" step="0.01" style="flex: 1; padding: 0.5rem; border: 2px solid #e9ecef; border-radius: 6px; font-size: 0.9rem;">
                        <button type="button" id="applyDiscountBtn" style="padding: 0.5rem 1rem; background: #48bb78; color: white; border: none; border-radius: 6px; cursor: pointer; font-weight: 600; font-size: 0.85rem;">
                            <i class="fas fa-check"></i> Apply
                        </button>
                        <button type="button" id="clearDiscountBtn" style="padding: 0.5rem 1rem; background: #e53e3e; color: white; border: none; border-radius: 6px; cursor: pointer; font-weight: 600; font-size: 0.85rem;">
                            <i class="fas fa-times"></i> Clear
                        </button>
                    </div>
                </div>

                <!-- Less Adjustment Section -->
                <div class="total-row adjustment-section">
                    <span>
                        <button type="button" class="btn-edit-adjustment" id="editAdjustmentBtn" title="Add Adjustment">
                            <i class="fas fa-edit"></i>
                        </button>
                        Less Adjustment:
                    </span>
                    <span id="adjustment" style="color: #e53e3e;">-$0.00</span>
                </div>

                <div id="adjustmentControls" style="display: none; padding: 0.75rem 0; border-top: 1px solid #e9ecef; border-bottom: 1px solid #e9ecef; margin: 0.5rem 0;">
                    <div style="margin-bottom: 0.5rem;">
                        <label style="display: block; font-size: 0.75rem; font-weight: 600; margin-bottom: 0.25rem; color: #4a5568;">Adjustment Amount:</label>
                        <input type="number" id="adjustmentValue" placeholder="0.00" min="0" step="0.01" style="width: 100%; padding: 0.5rem; border: 2px solid #e9ecef; border-radius: 6px; font-size: 0.9rem;">
                    </div>
                    <div style="margin-bottom: 0.5rem;">
                        <label style="display: block; font-size: 0.75rem; font-weight: 600; margin-bottom: 0.25rem; color: #4a5568;">Reason (optional):</label>
                        <input type="text" id="adjustmentReason" placeholder="e.g., Rounding, Damage, etc." maxlength="100" style="width: 100%; padding: 0.5rem; border: 2px solid #e9ecef; border-radius: 6px; font-size: 0.85rem;">
                    </div>
                    <div style="display: flex; gap: 0.5rem; align-items: center;">
                        <button type="button" id="applyAdjustmentBtn" style="flex: 1; padding: 0.5rem 1rem; background: #48bb78; color: white; border: none; border-radius: 6px; cursor: pointer; font-weight: 600; font-size: 0.85rem;">
                            <i class="fas fa-check"></i> Apply
                        </button>
                        <button type="button" id="clearAdjustmentBtn" style="flex: 1; padding: 0.5rem 1rem; background: #e53e3e; color: white; border: none; border-radius: 6px; cursor: pointer; font-weight: 600; font-size: 0.85rem;">
                            <i class="fas fa-times"></i> Clear
                        </button>
                    </div>
                </div>

                <div class="total-row grand-total">
                    <span>Total:</span>
                    <span id="total">$0.00</span>
                </div>

                <!-- Cash Payment Amount Section (shown only for cash) -->
                <div id="cashPaymentSection" style="display: none; margin-top: 0.75rem; padding-top: 0.75rem; border-top: 2px solid #e9ecef;">
                    <div style="margin-bottom: 0.5rem;">
                        <label style="display: block; font-size: 0.85rem; font-weight: 600; margin-bottom: 0.25rem; color: #4a5568;">Amount Received:</label>
                        <div style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 0.5rem;">
                            <div class="form-check" style="display: flex; align-items: center; gap: 0.5rem;">
                                <input type="checkbox" class="form-check-input" id="fullyPaidCheckbox" style="width: 1.2rem; height: 1.2rem; cursor: pointer;">
                                <label for="fullyPaidCheckbox" class="form-check-label" style="font-size: 0.9rem; font-weight: 600; color: #4a5568; cursor: pointer; margin: 0;">
                                    Fully Paid
                                </label>
                            </div>
                        </div>
                        <input type="number" id="amountReceived" placeholder="0.00" min="0" step="0.01" style="width: 100%; padding: 0.75rem; border: 2px solid #667eea; border-radius: 8px; font-size: 1.1rem; font-weight: 600;">
                    </div>
                    <div class="total-row" style="color: #2d3748; font-weight: 600;">
                        <span>Change:</span>
                        <span id="changeAmount" style="color: #48bb78; font-size: 1.1rem;">$0.00</span>
                    </div>
                </div>
            </div>

            <div class="payment-section">
                <div class="payment-methods">
                    <button type="button" class="payment-btn cash" data-method="cash">
                        <i class="fas fa-money-bill-wave"></i>
                        Cash
                    </button>
                    <button type="button" class="payment-btn card" data-method="card">
                        <i class="fas fa-credit-card"></i>
                        Card
                    </button>
                    <button type="button" class="payment-btn" data-method="mobile_banking">
                        <i class="fas fa-mobile-alt"></i>
                        Mobile
                    </button>
                    <button type="button" class="payment-btn" data-method="bank_transfer">
                        <i class="fas fa-university"></i>
                        Bank
                    </button>
                </div>

                <div class="action-buttons">
                    <button type="button" class="hold-order-btn" id="holdOrderBtn">
                        <i class="fas fa-pause-circle"></i>
                        Hold Order
                    </button>
                    <button type="button" class="complete-payment-btn" id="completePaymentBtn" disabled>
                        <i class="fas fa-check-circle"></i>
                        Complete Payment
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Customer Search Modal -->
    <div class="modal fade" id="customerModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title"><i class="fas fa-search"></i> Find Customer</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <!-- Search Section -->
                    <div id="customerSearchSection">
                        <div class="mb-3">
                            <input type="text" class="form-control form-control-lg" id="customerSearch" placeholder="Search by name, email, or phone..." autocomplete="off">
                        </div>
                        <div id="customerResults" class="list-group"></div>
                        <div class="text-center text-muted py-3" id="customerSearchHint">
                            <i class="fas fa-info-circle"></i> Type at least 2 characters to search
                        </div>
                        <div class="text-center mt-3 pt-3 border-top">
                            <button type="button" class="btn btn-success btn-lg" id="showAddCustomerFormBtn">
                                <i class="fas fa-user-plus"></i> Add New Customer
                            </button>
                        </div>
                    </div>

                    <!-- Quick Add Customer Form -->
                    <div id="addCustomerFormSection" style="display: none;">
                        <div class="mb-3">
                            <button type="button" class="btn btn-sm btn-outline-secondary" id="backToSearchBtn">
                                <i class="fas fa-arrow-left"></i> Back to Search
                            </button>
                        </div>
                        <form id="quickAddCustomerForm">
                            <div class="row">
                                <div class="col-md-12 mb-3">
                                    <label for="quickCustomerName" class="form-label">Customer Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="quickCustomerName" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="quickCustomerEmail" class="form-label">Email</label>
                                    <input type="email" class="form-control" id="quickCustomerEmail">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="quickCustomerPhone" class="form-label">Phone <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="quickCustomerPhone" required>
                                </div>
                                <div class="col-md-12 mb-3">
                                    <label for="quickCustomerAddress" class="form-label">Address</label>
                                    <textarea class="form-control" id="quickCustomerAddress" rows="2"></textarea>
                                </div>
                            </div>
                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="fas fa-save"></i> Create & Select Customer
                                </button>
                                <button type="button" class="btn btn-outline-secondary" id="cancelAddCustomerBtn">
                                    Cancel
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Hold Order Modal -->
    <div class="modal fade" id="holdOrderModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Hold Order</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="holdOrderForm">
                        <div class="mb-3">
                            <label for="holdReason" class="form-label">Hold Reason</label>
                            <textarea class="form-control" id="holdReason" rows="3" placeholder="Enter reason for holding this order..." required></textarea>
                        </div>
                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="sendNotification">
                                <label class="form-check-label" for="sendNotification">
                                    Send notification to customer
                                </label>
                            </div>
                        </div>
                        <div class="d-flex justify-content-end gap-2">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-warning">
                                <i class="fas fa-pause-circle"></i> Hold Order
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Payment Success Modal -->
    <div class="modal fade" id="paymentSuccessModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-body text-center py-4">
                    <i class="fas fa-check-circle text-success" style="font-size: 4rem;"></i>
                    <h4 class="mt-3">Payment Complete!</h4>
                    <p id="orderSuccessMessage" class="mb-3"></p>
                    <div class="d-grid gap-2">
                        <button type="button" class="btn btn-primary btn-lg" id="printReceiptBtn">
                            <i class="fas fa-print"></i> Print Receipt
                        </button>
                        <button type="button" class="btn btn-outline-secondary" onclick="location.reload()">
                            <i class="fas fa-plus"></i> Start New Order
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // POS Application State
        let posState = {
            cart: [],
            customer: null,
            selectedPaymentMethod: null,
            currentCategory: 'all',
            searchQuery: '',
            discountType: 'percentage',
            discountValue: 0,
            adjustmentValue: 0,
            adjustmentReason: ''
        };

        // Initialize POS
        document.addEventListener('DOMContentLoaded', function() {
            console.log('POS: DOM Content Loaded');

            // Debug: Check if critical elements exist
            const productsGrid = document.getElementById('productsGrid');
            const cartItems = document.getElementById('cartItems');
            const addCustomerBtnHeader = document.getElementById('addCustomerBtnHeader');

            console.log('POS: Elements found - ProductsGrid:', !!productsGrid, 'CartItems:', !!cartItems, 'AddCustomerBtnHeader:', !!addCustomerBtnHeader);

            try {
                loadProducts();
                initializeEventListeners();
                updateCartDisplay();
                console.log('POS: Initialization completed successfully');
            } catch (error) {
                console.error('POS: Error during initialization:', error);
            }
        });

        // Load initial products
        function loadProducts() {
            try {
                const featuredProducts = @json($featuredProductsData);
                console.log('POS: Featured products loaded:', featuredProducts?.length || 0, 'items');

                if (!featuredProducts || !Array.isArray(featuredProducts)) {
                    console.error('POS: Invalid featured products data');
                    return;
                }

                renderProducts(featuredProducts);
            } catch (error) {
                console.error('POS: Error loading products:', error);
            }
        }

        // Initialize event listeners
        function initializeEventListeners() {
            // Product search
            const productSearch = document.getElementById('productSearch');
            if (productSearch) {
                productSearch.addEventListener('input', debounce(function(e) {
                    posState.searchQuery = e.target.value;
                    searchProducts();
                }, 300));
            }

            // Category filters
            document.querySelectorAll('.category-btn').forEach(btn => {
                btn.addEventListener('click', function() {
                    document.querySelectorAll('.category-btn').forEach(b => b.classList.remove('active'));
                    this.classList.add('active');
                    posState.currentCategory = this.dataset.category;
                    searchProducts();
                });
            });

            // Customer button in header
            const addCustomerBtnHeader = document.getElementById('addCustomerBtnHeader');
            console.log('POS: Customer button found:', !!addCustomerBtnHeader);
            if (addCustomerBtnHeader) {
                addCustomerBtnHeader.addEventListener('click', function(e) {
                    e.preventDefault();
                    console.log('POS: Customer button clicked');
                    showCustomerModal();
                });
                console.log('POS: Customer button event listener attached');
            }

            // Payment methods
            document.querySelectorAll('.payment-btn').forEach(btn => {
                btn.addEventListener('click', function() {
                    document.querySelectorAll('.payment-btn').forEach(b => b.classList.remove('selected'));
                    this.classList.add('selected');
                    posState.selectedPaymentMethod = this.dataset.method;

                    // Show/hide cash payment section
                    const cashSection = document.getElementById('cashPaymentSection');
                    if (this.dataset.method === 'cash') {
                        cashSection.style.display = 'block';
                        updateCashChange();
                    } else {
                        cashSection.style.display = 'none';
                    }

                    updatePaymentButton();
                });
            });

            // Discount controls
            const editDiscountBtn = document.getElementById('editDiscountBtn');
            const discountControls = document.getElementById('discountControls');
            const applyDiscountBtn = document.getElementById('applyDiscountBtn');
            const clearDiscountBtn = document.getElementById('clearDiscountBtn');
            const discountValue = document.getElementById('discountValue');

            if (editDiscountBtn) {
                editDiscountBtn.addEventListener('click', function() {
                    const isVisible = discountControls.style.display === 'block';
                    discountControls.style.display = isVisible ? 'none' : 'block';
                });
            }

            // Discount type buttons
            document.querySelectorAll('.discount-type-btn').forEach(btn => {
                btn.addEventListener('click', function() {
                    document.querySelectorAll('.discount-type-btn').forEach(b => b.classList.remove('active'));
                    this.classList.add('active');
                    posState.discountType = this.dataset.type;
                });
            });

            if (applyDiscountBtn) {
                applyDiscountBtn.addEventListener('click', applyDiscount);
            }

            if (clearDiscountBtn) {
                clearDiscountBtn.addEventListener('click', clearDiscount);
            }

            if (discountValue) {
                discountValue.addEventListener('keypress', function(e) {
                    if (e.key === 'Enter') {
                        applyDiscount();
                    }
                });
            }

            // Adjustment controls
            const editAdjustmentBtn = document.getElementById('editAdjustmentBtn');
            const adjustmentControls = document.getElementById('adjustmentControls');
            const applyAdjustmentBtn = document.getElementById('applyAdjustmentBtn');
            const clearAdjustmentBtn = document.getElementById('clearAdjustmentBtn');
            const adjustmentValue = document.getElementById('adjustmentValue');

            if (editAdjustmentBtn) {
                editAdjustmentBtn.addEventListener('click', function() {
                    const isVisible = adjustmentControls.style.display === 'block';
                    adjustmentControls.style.display = isVisible ? 'none' : 'block';
                });
            }

            if (applyAdjustmentBtn) {
                applyAdjustmentBtn.addEventListener('click', applyAdjustment);
            }

            if (clearAdjustmentBtn) {
                clearAdjustmentBtn.addEventListener('click', clearAdjustment);
            }

            if (adjustmentValue) {
                adjustmentValue.addEventListener('keypress', function(e) {
                    if (e.key === 'Enter') {
                        applyAdjustment();
                    }
                });
            }

            // Cash amount received
            const amountReceived = document.getElementById('amountReceived');
            if (amountReceived) {
                amountReceived.addEventListener('input', updateCashChange);
            }

            // Fully paid checkbox handling
            const fullyPaidCheckbox = document.getElementById('fullyPaidCheckbox');
            if (fullyPaidCheckbox) {
                fullyPaidCheckbox.addEventListener('change', handleFullyPaid);
            }

            // Complete payment
            const completePaymentBtn = document.getElementById('completePaymentBtn');
            console.log('POS: Complete payment button found:', !!completePaymentBtn);
            if (completePaymentBtn) {
                completePaymentBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    console.log('POS: Complete payment button clicked');
                    completePayment();
                });
                console.log('POS: Complete payment button event listener attached');
            }

            // Customer modal toggle between search and add form
            const showAddCustomerFormBtn = document.getElementById('showAddCustomerFormBtn');
            const backToSearchBtn = document.getElementById('backToSearchBtn');
            const cancelAddCustomerBtn = document.getElementById('cancelAddCustomerBtn');

            if (showAddCustomerFormBtn) {
                showAddCustomerFormBtn.addEventListener('click', function() {
                    document.getElementById('customerSearchSection').style.display = 'none';
                    document.getElementById('addCustomerFormSection').style.display = 'block';
                    document.querySelector('#customerModal .modal-title').innerHTML = '<i class="fas fa-user-plus"></i> Add New Customer';
                });
            }

            if (backToSearchBtn) {
                backToSearchBtn.addEventListener('click', function() {
                    document.getElementById('customerSearchSection').style.display = 'block';
                    document.getElementById('addCustomerFormSection').style.display = 'none';
                    document.querySelector('#customerModal .modal-title').innerHTML = '<i class="fas fa-search"></i> Find Customer';
                    document.getElementById('quickAddCustomerForm').reset();
                });
            }

            if (cancelAddCustomerBtn) {
                cancelAddCustomerBtn.addEventListener('click', function() {
                    document.getElementById('customerSearchSection').style.display = 'block';
                    document.getElementById('addCustomerFormSection').style.display = 'none';
                    document.querySelector('#customerModal .modal-title').innerHTML = '<i class="fas fa-search"></i> Find Customer';
                    document.getElementById('quickAddCustomerForm').reset();
                });
            }

            // Handle quick add customer form submission
            const quickAddCustomerForm = document.getElementById('quickAddCustomerForm');
            if (quickAddCustomerForm) {
                quickAddCustomerForm.addEventListener('submit', function(e) {
                    e.preventDefault();
                    quickAddCustomer();
                });
            }

            // Hold order functionality
            const holdOrderBtn = document.getElementById('holdOrderBtn');
            if (holdOrderBtn) {
                holdOrderBtn.addEventListener('click', showHoldOrderModal);
            }

            // Handle hold order form submission
            const holdOrderForm = document.getElementById('holdOrderForm');
            if (holdOrderForm) {
                holdOrderForm.addEventListener('submit', function(e) {
                    e.preventDefault();
                    holdOrder();
                });
            }
        }

        // Search products
        function searchProducts() {
            const query = posState.searchQuery;
            const category = posState.currentCategory;

            fetch(`/pos/search?q=${encodeURIComponent(query)}&category=${encodeURIComponent(category)}`)
                .then(response => response.json())
                .then(data => {
                    renderProducts(data.products);
                })
                .catch(error => {
                    console.error('Error searching products:', error);
                });
        }

        // Render products grid
        function renderProducts(products) {
            const grid = document.getElementById('productsGrid');

            if (products.length === 0) {
                grid.innerHTML = `
                    <div style="grid-column: 1 / -1; text-align: center; padding: 3rem; color: #6c757d;">
                        <i class="fas fa-search" style="font-size: 3rem; margin-bottom: 1rem; opacity: 0.5;"></i>
                        <p>No products found</p>
                    </div>
                `;
                return;
            }

            grid.innerHTML = products.map(product => `
                <div class="product-card ${product.quantity <= 0 ? 'out-of-stock' : ''}" onclick="addToCart(${product.id})">
                    <div class="stock-badge ${product.quantity <= 5 ? 'low' : 'normal'}">
                        ${product.quantity} in stock
                    </div>
                    <img src="${product.image || '/images/product-placeholder.svg'}" alt="${product.name}" class="product-image">
                    <div class="product-name">${product.name}</div>
                    <div class="product-sku">SKU: ${product.sku}</div>
                    <div class="product-price">$${parseFloat(product.price).toFixed(2)}</div>
                </div>
            `).join('');
        }

        // Add product to cart
        function addToCart(productId) {
            fetch('/pos/add-to-cart', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    product_id: productId,
                    quantity: 1
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    updateCartDisplay(data.cart);
                    showToast('Product added to cart', 'success');
                } else {
                    showToast(data.message, 'error');
                }
            })
            .catch(error => {
                console.error('Error adding to cart:', error);
                showToast('Error adding product to cart', 'error');
            });
        }

        // Update cart display
        function updateCartDisplay(cartData = null) {
            const cartItems = document.getElementById('cartItems');
            const cartCount = document.getElementById('cartCount');

            if (cartData) {
                posState.cart = cartData;
            }

            const items = posState.cart.items || [];

            if (items.length === 0) {
                cartItems.innerHTML = `
                    <div class="empty-cart">
                        <i class="fas fa-shopping-cart"></i>
                        <p>No items in cart</p>
                        <p style="font-size: 0.9rem; margin-top: 0.5rem;">Add products to start your order</p>
                    </div>
                `;
                cartCount.textContent = '0';
                updateTotals();
                return;
            }

            cartItems.innerHTML = items.map(item => `
                <div class="cart-item fade-in">
                    <img src="${item.image || '/images/product-placeholder.svg'}" alt="${item.name}" class="cart-item-image">
                    <div class="cart-item-details">
                        <div class="cart-item-name">${item.name}</div>
                        <div class="cart-item-price" style="display: flex; align-items: center; gap: 0.5rem;">
                            <span style="font-size: 0.75rem; color: #6c757d;">$</span>
                            <input
                                type="number"
                                step="0.01"
                                min="0"
                                value="${parseFloat(item.price).toFixed(2)}"
                                onchange="updatePrice(${item.id}, this.value)"
                                style="width: 80px; padding: 0.25rem 0.5rem; border: 1px solid #dee2e6; border-radius: 4px; font-size: 0.9rem;"
                                onclick="event.stopPropagation();"
                            />
                            <span style="font-size: 0.75rem; color: #6c757d;">each</span>
                        </div>
                    </div>
                    <div class="quantity-controls">
                        <button class="quantity-btn" onclick="updateQuantity(${item.id}, ${item.quantity - 1})">
                            <i class="fas fa-minus"></i>
                        </button>
                        <div class="quantity-display">${item.quantity}</div>
                        <button class="quantity-btn" onclick="updateQuantity(${item.id}, ${item.quantity + 1})">
                            <i class="fas fa-plus"></i>
                        </button>
                    </div>
                    <button class="remove-item" onclick="removeFromCart(${item.id})">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            `).join('');

            cartCount.textContent = posState.cart.count || items.length;
            updateTotals();
        }

        // Update item quantity
        function updateQuantity(productId, newQuantity) {
            if (newQuantity < 1) return;

            fetch('/pos/update-cart', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    product_id: productId,
                    quantity: newQuantity
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    updateCartDisplay(data.cart);
                } else {
                    showToast(data.message, 'error');
                }
            })
            .catch(error => {
                console.error('Error updating cart:', error);
                showToast('Error updating cart', 'error');
            });
        }

        // Update item price
        function updatePrice(productId, newPrice) {
            if (newPrice < 0) {
                showToast('Price cannot be negative', 'error');
                return;
            }

            fetch('/pos/update-cart', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    product_id: productId,
                    custom_price: parseFloat(newPrice)
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    updateCartDisplay(data.cart);
                    showToast('Price updated successfully', 'success');
                } else {
                    showToast(data.message, 'error');
                }
            })
            .catch(error => {
                console.error('Error updating price:', error);
                showToast('Error updating price', 'error');
            });
        }

        // Remove item from cart
        function removeFromCart(productId) {
            fetch('/pos/remove-from-cart', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    product_id: productId
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    updateCartDisplay(data.cart);
                    showToast('Item removed from cart', 'success');
                }
            })
            .catch(error => {
                console.error('Error removing from cart:', error);
                showToast('Error removing item', 'error');
            });
        }

        // Update totals
        function updateTotals() {
            const cartData = posState.cart;
            const subtotal = cartData.subtotal || 0;
            const discount = cartData.discount || 0;
            const tax = cartData.tax || 0;
            const adjustment = posState.adjustmentValue || 0;

            // Calculate total with adjustment
            const totalBeforeAdjustment = cartData.total || (subtotal + tax - discount);
            const total = totalBeforeAdjustment - adjustment;

            document.getElementById('subtotal').textContent = `$${parseFloat(subtotal).toFixed(2)}`;
            document.getElementById('tax').textContent = `$${parseFloat(tax).toFixed(2)}`;
            document.getElementById('discount').textContent = `-$${parseFloat(discount).toFixed(2)}`;
            document.getElementById('adjustment').textContent = `-$${parseFloat(adjustment).toFixed(2)}`;
            document.getElementById('total').textContent = `$${parseFloat(total).toFixed(2)}`;

            updatePaymentButton();
            updateCashChange(); // Update change if cash payment selected
        }

        // Update payment button state
        function updatePaymentButton() {
            const completeBtn = document.getElementById('completePaymentBtn');
            const holdBtn = document.getElementById('holdOrderBtn');
            const hasItems = posState.cart.items && posState.cart.items.length > 0;
            const hasPaymentMethod = posState.selectedPaymentMethod;

            completeBtn.disabled = !(hasItems && hasPaymentMethod);
            holdBtn.disabled = !hasItems;
        }

        // Customer search
        function showCustomerModal() {
            console.log('POS: showCustomerModal called');

            const modalElement = document.getElementById('customerModal');
            console.log('POS: Modal element found:', !!modalElement);

            if (!modalElement) {
                console.error('POS: Customer modal element not found');
                showToast('Error: Customer modal not found', 'error');
                return;
            }

            try {
                const modal = new bootstrap.Modal(modalElement);
                console.log('POS: Bootstrap modal created');

                // Ensure the customer search input has the event listener
                const customerSearchInput = document.getElementById('customerSearch');
                console.log('POS: Customer search input found:', !!customerSearchInput);

                if (customerSearchInput && !customerSearchInput.hasAttribute('data-listener-attached')) {
                    customerSearchInput.addEventListener('input', debounce(searchCustomers, 300));
                    customerSearchInput.setAttribute('data-listener-attached', 'true');
                    console.log('POS: Customer search listener attached');
                }

                // Clear previous results and focus on input
                const resultsDiv = document.getElementById('customerResults');
                const hintDiv = document.getElementById('customerSearchHint');

                if (resultsDiv) resultsDiv.innerHTML = '';
                if (hintDiv) hintDiv.style.display = 'block';
                if (customerSearchInput) {
                    customerSearchInput.value = '';
                    setTimeout(() => customerSearchInput.focus(), 100);
                }

                console.log('POS: Showing modal');
                modal.show();
            } catch (error) {
                console.error('POS: Error showing customer modal:', error);
                showToast('Error opening customer search: ' + error.message, 'error');
            }
        }

        function searchCustomers() {
            const query = document.getElementById('customerSearch').value;
            const resultsDiv = document.getElementById('customerResults');
            const hintDiv = document.getElementById('customerSearchHint');

            if (query.length < 2) {
                resultsDiv.innerHTML = '';
                hintDiv.style.display = 'block';
                return;
            }

            hintDiv.style.display = 'none';
            resultsDiv.innerHTML = '<div class="text-center py-3"><div class="spinner-border spinner-border-sm text-primary"></div> Searching...</div>';

            fetch(`/pos/customer-search?q=${encodeURIComponent(query)}`)
                .then(response => response.json())
                .then(data => {
                    if (data.customers.length === 0) {
                        resultsDiv.innerHTML = '<div class="text-center text-muted py-3"><i class="fas fa-user-slash"></i> No customers found</div>';
                        return;
                    }

                    resultsDiv.innerHTML = data.customers.map(customer => `
                        <div class="list-group-item list-group-item-action" onclick="selectCustomer(${customer.id})" style="cursor: pointer;">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <div class="fw-bold"><i class="fas fa-user text-primary"></i> ${customer.name}</div>
                                    <div class="text-muted small">
                                        ${customer.email ? '<i class="fas fa-envelope"></i> ' + customer.email : ''}
                                        ${customer.phone ? '<i class="fas fa-phone"></i> ' + customer.phone : ''}
                                    </div>
                                </div>
                                <i class="fas fa-chevron-right text-muted"></i>
                            </div>
                        </div>
                    `).join('');
                })
                .catch(error => {
                    console.error('Error searching customers:', error);
                    resultsDiv.innerHTML = '<div class="alert alert-danger"><i class="fas fa-exclamation-triangle"></i> Error searching customers</div>';
                });
        }

        // Select customer
        function selectCustomer(customerId) {
            fetch('/pos/add-customer', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    customer_id: customerId
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    posState.customer = data.customer;
                    updateCustomerDisplay();
                    bootstrap.Modal.getInstance(document.getElementById('customerModal')).hide();
                    showToast('Customer added to order', 'success');
                }
            })
            .catch(error => {
                console.error('Error adding customer:', error);
                showToast('Error adding customer', 'error');
            });
        }

        // Update customer display
        function updateCustomerDisplay() {
            const customerSection = document.getElementById('customerInfoHeader');
            const addBtn = document.getElementById('addCustomerBtnHeader');

            if (posState.customer) {
                customerSection.innerHTML = `
                    <div class="customer-info">
                        <div>
                            <div class="customer-name"><i class="fas fa-user"></i> ${posState.customer.name}</div>
                            <div class="customer-details">${posState.customer.email || posState.customer.phone || 'No contact info'}</div>
                        </div>
                        <button class="remove-customer" onclick="removeCustomer()">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                `;
                customerSection.style.display = 'block';
                addBtn.style.display = 'none';
            } else {
                customerSection.style.display = 'none';
                addBtn.style.display = 'block';
            }
        }

        // Remove customer from order
        function removeCustomer() {
            fetch('/pos/remove-customer', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    posState.customer = null;
                    updateCustomerDisplay();
                    showToast('Customer removed from order', 'info');
                }
            })
            .catch(error => {
                console.error('Error removing customer:', error);
                showToast('Error removing customer', 'error');
            });
        }

        // Quick add customer
        function quickAddCustomer() {
            const name = document.getElementById('quickCustomerName').value.trim();
            const email = document.getElementById('quickCustomerEmail').value.trim();
            const phone = document.getElementById('quickCustomerPhone').value.trim();
            const address = document.getElementById('quickCustomerAddress').value.trim();

            if (!name || !phone) {
                showToast('Please fill in required fields (Name and Phone)', 'error');
                return;
            }

            // Disable submit button
            const submitBtn = document.querySelector('#quickAddCustomerForm button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Creating...';

            fetch('/pos/quick-add-customer', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    name: name,
                    email: email || null,
                    phone: phone,
                    address: address || null
                })
            })
            .then(response => response.json())
            .then(data => {
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;

                if (data.success) {
                    // Auto-select the newly created customer
                    posState.customer = data.customer;
                    updateCustomerDisplay();

                    // Close modal and reset form
                    bootstrap.Modal.getInstance(document.getElementById('customerModal')).hide();
                    document.getElementById('quickAddCustomerForm').reset();
                    document.getElementById('customerSearchSection').style.display = 'block';
                    document.getElementById('addCustomerFormSection').style.display = 'none';
                    document.querySelector('#customerModal .modal-title').innerHTML = '<i class="fas fa-search"></i> Find Customer';

                    showToast('Customer created and added to order', 'success');
                } else {
                    showToast(data.message || 'Failed to create customer', 'error');
                }
            })
            .catch(error => {
                console.error('Error creating customer:', error);
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
                showToast('Error creating customer', 'error');
            });
        }

        // Complete payment
        function completePayment() {
            console.log('POS: completePayment called');
            console.log('POS: Selected payment method:', posState.selectedPaymentMethod);
            console.log('POS: Cart items:', posState.cart);
            console.log('POS: Customer:', posState.customer);

            if (!posState.selectedPaymentMethod) {
                showToast('Please select a payment method', 'error');
                return;
            }

            const totalElement = document.getElementById('total');
            if (!totalElement) {
                showToast('Error: Total amount not found', 'error');
                return;
            }

            const total = totalElement.textContent.replace('$', '').replace(',', '');
            console.log('POS: Total amount:', total);

            // Disable button and show loading
            const completeBtn = document.getElementById('completePaymentBtn');
            const originalText = completeBtn.innerHTML;
            completeBtn.disabled = true;
            completeBtn.innerHTML = '<span class="loading"></span> Processing...';

            // Get cash handling data if cash payment
            const amountReceivedInput = document.getElementById('amountReceived');
            let paidAmount = parseFloat(total);
            let changeAmount = 0;

            if (posState.selectedPaymentMethod === 'cash' && amountReceivedInput) {
                paidAmount = parseFloat(amountReceivedInput.value) || parseFloat(total);
                changeAmount = paidAmount - parseFloat(total);
            }

            fetch('/pos/complete-payment', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    payment_method: posState.selectedPaymentMethod,
                    customer_id: posState.customer ? posState.customer.id : null,
                    paid_amount: paidAmount,
                    change_amount: changeAmount,
                    adjustment_amount: posState.adjustmentValue || 0,
                    adjustment_reason: posState.adjustmentReason || ''
                })
            })
            .then(response => {
                console.log('POS: Payment response status:', response.status);
                return response.json();
            })
            .then(data => {
                console.log('POS: Payment response data:', data);
                completeBtn.innerHTML = originalText;
                completeBtn.disabled = false;

                if (data.success) {
                    showPaymentSuccess(data.order_number, data.order_id);
                } else {
                    showToast(data.message || 'Payment failed', 'error');
                }
            })
            .catch(error => {
                console.error('POS: Error completing payment:', error);
                completeBtn.innerHTML = originalText;
                completeBtn.disabled = false;
                showToast('Error completing payment: ' + error.message, 'error');
            });
        }

        // Discount functions
        function applyDiscount() {
            const discountValueInput = document.getElementById('discountValue');
            const value = parseFloat(discountValueInput.value) || 0;

            if (value < 0) {
                showToast('Discount value cannot be negative', 'error');
                return;
            }

            posState.discountValue = value;

            fetch('/pos/apply-discount', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    type: posState.discountType,
                    value: value
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    updateCartDisplay(data.cart);
                    showToast('Discount applied successfully', 'success');
                    document.getElementById('discountControls').style.display = 'none';
                } else {
                    showToast(data.message || 'Failed to apply discount', 'error');
                }
            })
            .catch(error => {
                console.error('Error applying discount:', error);
                showToast('Error applying discount', 'error');
            });
        }

        function clearDiscount() {
            posState.discountValue = 0;
            document.getElementById('discountValue').value = '';

            fetch('/pos/apply-discount', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    type: 'percentage',
                    value: 0
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    updateCartDisplay(data.cart);
                    showToast('Discount cleared', 'info');
                    document.getElementById('discountControls').style.display = 'none';

                    // Reset to percentage type
                    document.querySelectorAll('.discount-type-btn').forEach(b => b.classList.remove('active'));
                    document.querySelector('[data-type="percentage"]').classList.add('active');
                    posState.discountType = 'percentage';
                }
            })
            .catch(error => {
                console.error('Error clearing discount:', error);
                showToast('Error clearing discount', 'error');
            });
        }

        // Adjustment functions
        function applyAdjustment() {
            const adjustmentValueInput = document.getElementById('adjustmentValue');
            const adjustmentReasonInput = document.getElementById('adjustmentReason');
            const value = parseFloat(adjustmentValueInput.value) || 0;
            const reason = adjustmentReasonInput.value.trim();

            if (value < 0) {
                showToast('Adjustment value cannot be negative', 'error');
                return;
            }

            posState.adjustmentValue = value;
            posState.adjustmentReason = reason;

            // Update adjustment display
            document.getElementById('adjustment').textContent = `-$${value.toFixed(2)}`;
            document.getElementById('adjustmentControls').style.display = 'none';

            // Recalculate totals
            updateTotals();

            showToast('Adjustment applied successfully', 'success');
        }

        function clearAdjustment() {
            posState.adjustmentValue = 0;
            posState.adjustmentReason = '';
            document.getElementById('adjustmentValue').value = '';
            document.getElementById('adjustmentReason').value = '';
            document.getElementById('adjustment').textContent = '-$0.00';
            document.getElementById('adjustmentControls').style.display = 'none';

            // Recalculate totals
            updateTotals();

            showToast('Adjustment cleared', 'info');
        }

        // Cash handling
        function updateCashChange() {
            const amountReceivedInput = document.getElementById('amountReceived');
            const totalElement = document.getElementById('total');
            const changeElement = document.getElementById('changeAmount');

            if (!amountReceivedInput || !totalElement || !changeElement) return;

            const amountReceived = parseFloat(amountReceivedInput.value) || 0;
            const total = parseFloat(totalElement.textContent.replace('$', '').replace(',', '')) || 0;
            const change = amountReceived - total;

            changeElement.textContent = `$${change.toFixed(2)}`;
            changeElement.style.color = change >= 0 ? '#48bb78' : '#e53e3e';
        }

        function handleFullyPaid() {
            const amountReceivedInput = document.getElementById('amountReceived');
            const totalElement = document.getElementById('total');
            const fullyPaidCheckbox = document.getElementById('fullyPaidCheckbox');

            if (!amountReceivedInput || !totalElement || !fullyPaidCheckbox) return;

            if (fullyPaidCheckbox.checked) {
                const total = parseFloat(totalElement.textContent.replace('$', '').replace(',', '')) || 0;
                amountReceivedInput.value = total.toFixed(2);
                amountReceivedInput.dispatchEvent(new Event('input'));
            } else {
                amountReceivedInput.value = '';
                amountReceivedInput.dispatchEvent(new Event('input'));
            }
        }

        // Print receipt
        function printReceipt(orderId) {
            console.log('POS: Printing receipt for order:', orderId);

            // Open print window
            const printWindow = window.open(`/pos/print-receipt/${orderId}`, '_blank', 'width=800,height=600');

            if (!printWindow) {
                showToast('Please allow popups to print receipt', 'error');
                return;
            }

            // Auto print when loaded
            printWindow.onload = function() {
                setTimeout(() => {
                    printWindow.print();
                }, 500);
            };
        }

        // Show payment success
        function showPaymentSuccess(orderNumber, orderId) {
            document.getElementById('orderSuccessMessage').textContent =
                `Order #${orderNumber} has been successfully created.`;

            // Store order ID for printing
            posState.lastOrderId = orderId;
            posState.lastOrderNumber = orderNumber;

            // Setup print button
            const printBtn = document.getElementById('printReceiptBtn');
            if (printBtn) {
                printBtn.onclick = function() {
                    printReceipt(orderId);
                };
            }

            // Reset POS state (but keep lastOrderId for printing)
            const lastOrderId = posState.lastOrderId;
            const lastOrderNumber = posState.lastOrderNumber;

            posState.customer = null;
            posState.selectedPaymentMethod = null;
            posState.cart = [];
            posState.discountValue = 0;
            posState.discountType = 'percentage';
            posState.adjustmentValue = 0;
            posState.adjustmentReason = '';
            posState.lastOrderId = lastOrderId;
            posState.lastOrderNumber = lastOrderNumber;

            // Update UI
            updateCustomerDisplay();
            updateCartDisplay();

            // Clear payment method selection
            document.querySelectorAll('.payment-btn').forEach(btn => btn.classList.remove('selected'));

            // Clear discount
            document.getElementById('discountValue').value = '';
            document.getElementById('discountControls').style.display = 'none';

            // Clear adjustment
            document.getElementById('adjustmentValue').value = '';
            document.getElementById('adjustmentReason').value = '';
            document.getElementById('adjustment').textContent = '-$0.00';
            document.getElementById('adjustmentControls').style.display = 'none';

            // Clear cash payment
            document.getElementById('cashPaymentSection').style.display = 'none';
            document.getElementById('amountReceived').value = '';

            // Clear fully paid checkbox
            const fullyPaidCheckbox = document.getElementById('fullyPaidCheckbox');
            if (fullyPaidCheckbox) {
                fullyPaidCheckbox.checked = false;
            }

            const modal = new bootstrap.Modal(document.getElementById('paymentSuccessModal'));
            modal.show();
        }

        // Hold Order Functions
        function showHoldOrderModal() {
            if (!posState.cart.items || posState.cart.items.length === 0) {
                showToast('Cart is empty. Add items before holding the order.', 'error');
                return;
            }

            const modal = new bootstrap.Modal(document.getElementById('holdOrderModal'));
            modal.show();
        }

        function hideHoldOrderModal() {
            const modal = bootstrap.Modal.getInstance(document.getElementById('holdOrderModal'));
            if (modal) {
                modal.hide();
            }
        }

        function holdOrder() {
            const holdReason = document.getElementById('holdReason').value;
            const sendNotification = document.getElementById('sendNotification').checked;

            if (!holdReason.trim()) {
                showToast('Please provide a reason for holding this order.', 'error');
                return;
            }

            fetch('/pos/hold-order', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    hold_reason: holdReason,
                    send_notification: sendNotification,
                    customer_id: posState.customer ? posState.customer.id : null
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    hideHoldOrderModal();
                    showToast('Order has been placed on hold successfully', 'success');

                    // Clear cart and reset POS after a short delay
                    setTimeout(() => {
                        location.reload();
                    }, 2000);
                } else {
                    showToast(data.message, 'error');
                }
            })
            .catch(error => {
                console.error('Error holding order:', error);
                showToast('Error holding order', 'error');
            });
        }

        // Utility functions
        function debounce(func, wait) {
            let timeout;
            return function executedFunction(...args) {
                const later = () => {
                    clearTimeout(timeout);
                    func(...args);
                };
                clearTimeout(timeout);
                timeout = setTimeout(later, wait);
            };
        }

        function showToast(message, type = 'info') {
            // Simple toast implementation
            const toast = document.createElement('div');
            toast.className = `alert alert-${type} position-fixed top-0 end-0 m-3`;
            toast.style.zIndex = '9999';
            toast.textContent = message;
            document.body.appendChild(toast);

            setTimeout(() => {
                toast.remove();
            }, 3000);
        }

        // CSRF Token
        const csrfToken = document.querySelector('meta[name="csrf-token"]');
        if (!csrfToken) {
            const meta = document.createElement('meta');
            meta.name = 'csrf-token';
            meta.content = '{{ csrf_token() }}';
            document.head.appendChild(meta);
        }
    </script>
</body>
</html>