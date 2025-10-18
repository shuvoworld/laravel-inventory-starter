<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Point of Sale 2 - {{ config('app.name') }}</title>
    @vite('resources/css/app.css')
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://unpkg.com/phosphor-icons@1.4.2/src/css/phosphor.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        /* ========================================
           GLOBAL RESET & BASE STYLES
        ======================================== */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif !important;
            background: #f8f9fa !important;
            overflow: hidden !important;
            height: 100vh !important;
            color: #1f2937 !important;
        }

        body > *:not(.pos2-wrapper):not(.modal):not(.modal-backdrop) {
            display: none !important;
        }

        .modal {
            z-index: 2060 !important;
        }

        .modal-backdrop {
            z-index: 2055 !important;
        }

        /* ========================================
           MAIN POS CONTAINER
        ======================================== */
        .pos2-wrapper {
            display: grid !important;
            grid-template-columns: 1fr 480px 380px;
            gap: 20px;
            height: 100vh;
            padding: 20px;
            background: linear-gradient(135deg, #f0f4f8 0%, #e6eef5 100%);
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            z-index: 2000;
        }

        /* ========================================
           LEFT COLUMN - PRODUCT SELECTION
        ======================================== */
        .product-selection-panel {
            background: #ffffff;
            border-radius: 24px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }

        .panel-header {
            background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
            padding: 24px 28px;
            color: white;
        }

        .panel-header h2 {
            font-size: 26px;
            font-weight: 700;
            margin: 0;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .panel-header h2 i {
            font-size: 32px;
        }

        .search-section-modern {
            padding: 20px 24px;
            background: #ffffff;
            border-bottom: 1px solid #e5e7eb;
        }

        .search-bar-modern {
            position: relative;
            margin-bottom: 16px;
        }

        .search-bar-modern input {
            width: 100%;
            padding: 14px 20px 14px 48px;
            border: 2px solid #e5e7eb;
            border-radius: 16px;
            font-size: 15px;
            font-weight: 500;
            transition: all 0.3s ease;
            background: #f9fafb;
        }

        .search-bar-modern input:focus {
            outline: none;
            border-color: #4f46e5;
            background: #ffffff;
            box-shadow: 0 0 0 4px rgba(79, 70, 229, 0.1);
        }

        .search-bar-modern i {
            position: absolute;
            left: 16px;
            top: 50%;
            transform: translateY(-50%);
            font-size: 20px;
            color: #9ca3af;
        }

        .category-filters {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .category-btn-modern {
            padding: 10px 20px;
            border: 2px solid #e5e7eb;
            background: #ffffff;
            color: #4b5563;
            border-radius: 12px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .category-btn-modern:hover {
            border-color: #4f46e5;
            color: #4f46e5;
            background: #f0f1ff;
        }

        .category-btn-modern.active {
            background: #4f46e5;
            color: white;
            border-color: #4f46e5;
            box-shadow: 0 4px 12px rgba(79, 70, 229, 0.3);
        }

        .products-grid-modern {
            flex: 1;
            overflow-y: auto;
            padding: 20px 24px;
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
            gap: 16px;
            align-content: start;
        }

        .products-grid-modern::-webkit-scrollbar {
            width: 8px;
        }

        .products-grid-modern::-webkit-scrollbar-track {
            background: #f1f5f9;
            border-radius: 4px;
        }

        .products-grid-modern::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 4px;
        }

        .product-card-modern {
            background: #ffffff;
            border: 2px solid #f3f4f6;
            border-radius: 16px;
            padding: 16px;
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .product-card-modern::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #4f46e5, #7c3aed);
            transform: scaleX(0);
            transition: transform 0.3s ease;
        }

        .product-card-modern:hover {
            border-color: #4f46e5;
            box-shadow: 0 8px 24px rgba(79, 70, 229, 0.2);
            transform: translateY(-4px);
        }

        .product-card-modern:hover::before {
            transform: scaleX(1);
        }

        .product-card-modern.selected {
            border-color: #4f46e5;
            background: linear-gradient(135deg, #f0f1ff 0%, #e8e9ff 100%);
            box-shadow: 0 8px 24px rgba(79, 70, 229, 0.3);
        }

        .product-card-modern.selected::before {
            transform: scaleX(1);
        }

        .product-image-modern {
            width: 100%;
            height: 100px;
            object-fit: cover;
            border-radius: 12px;
            margin-bottom: 12px;
            background: #f9fafb;
        }

        .product-name-modern {
            font-size: 14px;
            font-weight: 600;
            color: #1f2937;
            margin-bottom: 6px;
            line-height: 1.3;
            min-height: 36px;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .product-price-modern {
            font-size: 18px;
            font-weight: 700;
            color: #4f46e5;
            margin-bottom: 4px;
        }

        .product-stock-modern {
            font-size: 12px;
            color: #6b7280;
            font-weight: 500;
        }

        /* ========================================
           CENTER COLUMN - CURRENT ORDER / CART
        ======================================== */
        .order-panel {
            background: #ffffff;
            border-radius: 24px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }

        .order-header {
            background: linear-gradient(135deg, #14b8a6 0%, #0d9488 100%);
            padding: 20px 24px;
            color: white;
        }

        .order-header h3 {
            font-size: 18px;
            font-weight: 600;
            margin: 0;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .customer-display {
            margin-top: 12px;
            padding: 12px 16px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 12px;
            backdrop-filter: blur(10px);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .customer-name {
            font-size: 15px;
            font-weight: 600;
        }

        .customer-change-btn {
            background: white;
            color: #14b8a6;
            border: none;
            padding: 6px 14px;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .customer-change-btn:hover {
            background: #f0fdfa;
            transform: scale(1.05);
        }

        .cart-items-modern {
            flex: 1;
            overflow-y: auto;
            padding: 16px;
        }

        .cart-items-modern::-webkit-scrollbar {
            width: 6px;
        }

        .cart-items-modern::-webkit-scrollbar-track {
            background: #f9fafb;
        }

        .cart-items-modern::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 3px;
        }

        .cart-item-modern {
            background: #f9fafb;
            border: 1px solid #e5e7eb;
            border-radius: 14px;
            padding: 14px;
            margin-bottom: 12px;
            transition: all 0.2s ease;
        }

        .cart-item-modern:hover {
            border-color: #14b8a6;
            box-shadow: 0 4px 12px rgba(20, 184, 166, 0.15);
        }

        .cart-item-header {
            display: flex;
            justify-content: space-between;
            align-items: start;
            margin-bottom: 10px;
        }

        .cart-item-name {
            font-size: 15px;
            font-weight: 600;
            color: #1f2937;
            flex: 1;
            line-height: 1.4;
        }

        .cart-item-remove {
            background: #fee2e2;
            color: #dc2626;
            border: none;
            width: 28px;
            height: 28px;
            border-radius: 8px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s ease;
            margin-left: 8px;
        }

        .cart-item-remove:hover {
            background: #dc2626;
            color: white;
            transform: scale(1.1);
        }

        .cart-item-controls {
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .quantity-controls {
            display: flex;
            align-items: center;
            gap: 8px;
            background: white;
            border-radius: 10px;
            padding: 4px;
        }

        .qty-btn {
            width: 32px;
            height: 32px;
            border: none;
            background: #4f46e5;
            color: white;
            border-radius: 8px;
            cursor: pointer;
            font-size: 16px;
            font-weight: 700;
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .qty-btn:hover {
            background: #4338ca;
            transform: scale(1.05);
        }

        .qty-display {
            min-width: 40px;
            text-align: center;
            font-weight: 700;
            font-size: 15px;
            color: #1f2937;
        }

        .cart-item-price {
            font-size: 18px;
            font-weight: 700;
            color: #4f46e5;
        }

        .empty-cart-modern {
            text-align: center;
            padding: 60px 20px;
            color: #9ca3af;
        }

        .empty-cart-modern i {
            font-size: 80px;
            margin-bottom: 16px;
            opacity: 0.4;
        }

        .empty-cart-modern p {
            font-size: 16px;
            font-weight: 500;
        }

        .order-summary {
            padding: 20px 24px;
            background: #f9fafb;
            border-top: 2px solid #e5e7eb;
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 12px;
            font-size: 15px;
        }

        .summary-row.discount-row,
        .summary-row.adjustment-row {
            color: #dc2626;
        }

        .summary-row.discount-row .edit-icon,
        .summary-row.adjustment-row .edit-icon {
            margin-right: 6px;
            cursor: pointer;
            color: #4f46e5;
            transition: all 0.2s ease;
        }

        .discount-input-container,
        .adjustment-input-container {
            display: flex;
            align-items: center;
            gap: 4px;
        }

        .discount-input,
        .adjustment-input {
            width: 80px;
            padding: 4px 8px;
            border: 1px solid #d1d5db;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 600;
            text-align: right;
            background: #ffffff;
            transition: all 0.2s ease;
        }

        .discount-input:focus,
        .adjustment-input:focus {
            outline: none;
            border-color: #4f46e5;
            box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
        }

        .discount-type-select {
            padding: 4px 6px;
            border: 1px solid #d1d5db;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 600;
            background: #ffffff;
            cursor: pointer;
        }

        .discount-type-select:focus {
            outline: none;
            border-color: #4f46e5;
            box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
        }

        .summary-row.total-row {
            font-size: 24px;
            font-weight: 800;
            color: #1f2937;
            padding-top: 12px;
            border-top: 2px solid #d1d5db;
            margin-top: 8px;
            margin-bottom: 0;
        }

        .summary-row.total-row .amount {
            color: #4f46e5;
        }

        /* ========================================
           RIGHT COLUMN - ACTIONS & PAYMENT
        ======================================== */
        .payment-panel {
            background: #ffffff;
            border-radius: 24px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }

        .payment-header {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
            padding: 20px 24px;
            color: white;
        }

        .payment-header h3 {
            font-size: 18px;
            font-weight: 600;
            margin: 0;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .customer-section-modern {
            padding: 16px 20px;
            border-bottom: 2px solid #f3f4f6;
        }

        .add-customer-btn-modern {
            width: 100%;
            padding: 12px 16px;
            border: 2px dashed #cbd5e1;
            background: #f9fafb;
            color: #6b7280;
            border-radius: 12px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .add-customer-btn-modern:hover {
            border-color: #14b8a6;
            background: #f0fdfa;
            color: #14b8a6;
            border-style: solid;
        }

        .customer-info-card {
            background: linear-gradient(135deg, #f0fdfa 0%, #ccfbf1 100%);
            border: 2px solid #14b8a6;
            border-radius: 12px;
            padding: 12px 16px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .customer-info-card .info {
            font-size: 14px;
            font-weight: 600;
            color: #0d9488;
        }

        .customer-info-card .remove-btn {
            background: #dc2626;
            color: white;
            border: none;
            width: 24px;
            height: 24px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 12px;
            transition: all 0.2s ease;
        }

        .customer-info-card .remove-btn:hover {
            background: #b91c1c;
            transform: scale(1.1);
        }

        .payment-methods {
            padding: 16px 20px;
            border-bottom: 2px solid #f3f4f6;
        }

        .payment-methods h4 {
            font-size: 14px;
            font-weight: 700;
            color: #6b7280;
            margin-bottom: 12px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .payment-method-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
        }

        .payment-method-btn {
            padding: 14px 12px;
            border: 2px solid #e5e7eb;
            background: #ffffff;
            color: #4b5563;
            border-radius: 12px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 6px;
        }

        .payment-method-btn i {
            font-size: 24px;
        }

        .payment-method-btn:hover {
            border-color: #4f46e5;
            background: #f0f1ff;
            color: #4f46e5;
        }

        .payment-method-btn.active {
            background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
            color: white;
            border-color: #4f46e5;
            box-shadow: 0 6px 16px rgba(79, 70, 229, 0.4);
        }

        .cash-input-section {
            padding: 16px 20px;
            background: #fef3c7;
            border-bottom: 2px solid #fbbf24;
            display: none;
        }

        .cash-input-section.active {
            display: block;
        }

        .cash-input-section h4 {
            font-size: 13px;
            font-weight: 700;
            color: #92400e;
            margin-bottom: 10px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .cash-input-modern {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid #fbbf24;
            border-radius: 10px;
            font-size: 20px;
            font-weight: 700;
            text-align: center;
            color: #1f2937;
            background: white;
            margin-bottom: 10px;
        }

        .cash-input-modern:focus {
            outline: none;
            border-color: #f59e0b;
            box-shadow: 0 0 0 4px rgba(245, 158, 11, 0.2);
        }

        .change-display {
            background: white;
            padding: 10px 14px;
            border-radius: 10px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-weight: 600;
        }

        .change-display .label {
            color: #92400e;
            font-size: 14px;
        }

        .change-display .amount {
            color: #14b8a6;
            font-size: 18px;
        }

  
        .action-buttons-modern {
            padding: 20px;
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .charge-btn {
            width: 100%;
            padding: 18px;
            border: none;
            background: linear-gradient(90deg, #7c3aed 0%, #4f46e5 100%);
            color: white;
            border-radius: 16px;
            font-size: 18px;
            font-weight: 800;
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 0 8px 20px rgba(79, 70, 229, 0.4);
            text-transform: uppercase;
            letter-spacing: 1px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        .charge-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 12px 28px rgba(79, 70, 229, 0.5);
        }

        .charge-btn:active {
            transform: translateY(-1px);
        }

        .charge-btn:disabled {
            background: #e5e7eb;
            color: #9ca3af;
            cursor: not-allowed;
            box-shadow: none;
            transform: none;
        }

        .charge-btn i {
            font-size: 24px;
        }

        .secondary-actions {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
        }

        .hold-btn,
        .clear-btn {
            padding: 12px;
            border: 2px solid #e5e7eb;
            background: white;
            color: #6b7280;
            border-radius: 12px;
            font-size: 14px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .hold-btn:hover {
            border-color: #f59e0b;
            color: #f59e0b;
            background: #fffbeb;
        }

        .clear-btn:hover {
            border-color: #dc2626;
            color: #dc2626;
            background: #fef2f2;
        }

        /* ========================================
           LOADING & UTILITY STYLES
        ======================================== */
        .loading-spinner {
            display: inline-block;
            width: 20px;
            height: 20px;
            border: 3px solid rgba(255, 255, 255, 0.3);
            border-radius: 50%;
            border-top-color: white;
            animation: spin 0.8s linear infinite;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        .fade-in {
            animation: fadeIn 0.4s ease-in;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* ========================================
           RESPONSIVE ADJUSTMENTS
        ======================================== */
        @media (max-width: 1400px) {
            .pos2-wrapper {
                grid-template-columns: 1fr 400px 340px;
            }

            .products-grid-modern {
                grid-template-columns: repeat(auto-fill, minmax(140px, 1fr));
            }
        }
    </style>
</head>
<body>
    <div class="pos2-wrapper">
        <!-- ============================================
             LEFT PANEL - PRODUCT SELECTION
        ============================================= -->
        <div class="product-selection-panel">
            <div class="panel-header">
                <h2>
                    <i class="ph-storefront"></i>
                    Products
                </h2>
            </div>

            <div class="search-section-modern">
                <div class="search-bar-modern">
                    <i class="ph-magnifying-glass"></i>
                    <input type="text" id="productSearchModern" placeholder="Search products by name or SKU..." autocomplete="off">
                </div>

                <div class="category-filters" id="categoryFiltersModern">
                    <button class="category-btn-modern active" data-category="all">
                        <i class="ph-stack"></i>
                        All Products
                    </button>
                    @foreach($categories as $id => $name)
                        <button class="category-btn-modern" data-category="{{ $id }}">
                            <i class="ph-tag"></i>
                            {{ $name }}
                        </button>
                    @endforeach
                </div>
            </div>

            <div class="products-grid-modern" id="productsGridModern">
                <!-- Products will be loaded here dynamically -->
            </div>
        </div>

        <!-- ============================================
             CENTER PANEL - CURRENT ORDER / CART
        ============================================= -->
        <div class="order-panel">
            <div class="order-header">
                <h3>
                    <i class="ph-shopping-cart"></i>
                    Current Order
                </h3>
                <div class="customer-display" id="customerDisplayModern">
                    <span class="customer-name">Guest Order</span>
                </div>
            </div>

            <div class="cart-items-modern" id="cartItemsModern">
                <div class="empty-cart-modern">
                    <i class="ph-shopping-cart-simple"></i>
                    <p>Cart is empty<br>Add products to start</p>
                </div>
            </div>

            <div class="order-summary" id="orderSummaryModern">
                <div class="summary-row">
                    <span>Subtotal:</span>
                    <span id="subtotalModern">$0.00</span>
                </div>
                <div class="summary-row discount-row">
                    <span>Discount:</span>
                    <div class="discount-input-container">
                        <input type="number" class="discount-input" id="discountInputModern"
                               placeholder="0.00" step="0.01" min="0" max="9999">
                        <select class="discount-type-select" id="discountTypeModern">
                            <option value="percentage">%</option>
                            <option value="fixed">$</option>
                        </select>
                    </div>
                </div>
                <div class="summary-row adjustment-row">
                    <span>Adjustment:</span>
                    <div class="adjustment-input-container">
                        <input type="number" class="adjustment-input" id="adjustmentInputModern"
                               placeholder="0.00" step="0.01" min="-9999" max="9999">
                    </div>
                </div>
                <div class="summary-row total-row">
                    <span>TOTAL:</span>
                    <span class="amount" id="totalModern">$0.00</span>
                </div>
            </div>
        </div>

        <!-- ============================================
             RIGHT PANEL - ACTIONS & PAYMENT
        ============================================= -->
        <div class="payment-panel">
            <div class="payment-header">
                <h3>
                    <i class="ph-wallet"></i>
                    Payment
                </h3>
            </div>

            <div class="customer-section-modern">
                <button class="add-customer-btn-modern" id="addCustomerBtnModern">
                    <i class="ph-user-plus"></i>
                    Search / Add Customer
                </button>
            </div>

            <div class="payment-methods">
                <h4>Payment Method</h4>
                <div class="payment-method-grid">
                    <button class="payment-method-btn" data-method="cash">
                        <i class="ph-money"></i>
                        Cash
                    </button>
                    <button class="payment-method-btn" data-method="card">
                        <i class="ph-credit-card"></i>
                        Card
                    </button>
                    <button class="payment-method-btn" data-method="mobile_banking">
                        <i class="ph-device-mobile"></i>
                        Mobile
                    </button>
                    <button class="payment-method-btn" data-method="bank_transfer">
                        <i class="ph-bank"></i>
                        Bank
                    </button>
                </div>
            </div>

            <div class="cash-input-section" id="cashInputSectionModern">
                <h4>Cash Received</h4>
                <input type="number" class="cash-input-modern" id="cashInputModern" placeholder="0.00" step="0.01" min="0">
                <div class="change-display">
                    <span class="label">Change:</span>
                    <span class="amount" id="changeAmountModern">$0.00</span>
                </div>
            </div>

    
            <div class="action-buttons-modern">
                <button class="charge-btn" id="chargeBtnModern" disabled>
                    <i class="ph-lightning"></i>
                    Complete Payment
                </button>
                <div class="secondary-actions">
                    <button class="hold-btn" id="holdBtnModern">
                        <i class="ph-clock"></i> Hold
                    </button>
                    <button class="clear-btn" id="clearCartBtnModern">
                        <i class="ph-trash"></i> Clear
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- MODALS -->
    <!-- Customer Modal -->
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
        // ============================================
        // POS STATE MANAGEMENT
        // ============================================
        let posState = {
            cart: [],
            customer: null,
            selectedPaymentMethod: null,
            currentCategory: 'all',
            searchQuery: '',
            discountType: 'percentage',
            discountValue: 0,
            adjustmentValue: 0,
            adjustmentReason: '',
            cashReceived: 0,
            lastOrderId: null
        };

        // ============================================
        // INITIALIZATION
        // ============================================
        document.addEventListener('DOMContentLoaded', function() {
            initializeEventListeners();
            loadInitialProducts();
        });

        function initializeEventListeners() {
            // Product search
            const productSearch = document.getElementById('productSearchModern');
            if (productSearch) {
                productSearch.addEventListener('input', debounce(function(e) {
                    posState.searchQuery = e.target.value;
                    searchProducts();
                }, 300));
            }

            // Category filters
            document.querySelectorAll('.category-btn-modern').forEach(btn => {
                btn.addEventListener('click', function() {
                    document.querySelectorAll('.category-btn-modern').forEach(b => b.classList.remove('active'));
                    this.classList.add('active');
                    posState.currentCategory = this.dataset.category;
                    searchProducts();
                });
            });

            // Payment method selection
            document.querySelectorAll('.payment-method-btn').forEach(btn => {
                btn.addEventListener('click', function() {
                    document.querySelectorAll('.payment-method-btn').forEach(b => b.classList.remove('active'));
                    this.classList.add('active');
                    posState.selectedPaymentMethod = this.dataset.method;

                    // Show cash input if cash is selected
                    const cashSection = document.getElementById('cashInputSectionModern');
                    if (this.dataset.method === 'cash') {
                        cashSection.classList.add('active');
                    } else {
                        cashSection.classList.remove('active');
                    }

                    updateChargeButton();
                    calculateChange(); // Update change calculation when payment method changes
                });
            });

            // Cash input handling
            const cashInput = document.getElementById('cashInputModern');
            if (cashInput) {
                cashInput.addEventListener('input', function() {
                    posState.cashReceived = parseFloat(this.value) || 0;
                    calculateChange();
                });
            }

            // Direct discount editing
            const discountInput = document.getElementById('discountInputModern');
            const discountTypeSelect = document.getElementById('discountTypeModern');

            if (discountInput) {
                discountInput.addEventListener('input', function() {
                    const value = parseFloat(this.value) || 0;
                    posState.discountValue = value;
                    calculateTotals();
                    calculateChange(); // Recalculate change when discount changes
                    applyDiscountToCart();
                });
            }

            if (discountTypeSelect) {
                discountTypeSelect.addEventListener('change', function() {
                    posState.discountType = this.value;
                    calculateTotals();
                    calculateChange(); // Recalculate change when discount type changes
                    applyDiscountToCart();
                });
            }

            // Direct adjustment editing
            const adjustmentInput = document.getElementById('adjustmentInputModern');
            if (adjustmentInput) {
                adjustmentInput.addEventListener('input', function() {
                    const value = parseFloat(this.value) || 0;
                    posState.adjustmentValue = value;
                    calculateTotals();
                    calculateChange(); // Recalculate change when adjustment changes
                });
            }

            // Customer button
            const addCustomerBtn = document.getElementById('addCustomerBtnModern');
            if (addCustomerBtn) {
                addCustomerBtn.addEventListener('click', function() {
                    showCustomerModal();
                });
            }

            // Charge button
            const chargeBtn = document.getElementById('chargeBtnModern');
            if (chargeBtn) {
                chargeBtn.addEventListener('click', function() {
                    if (!this.disabled) {
                        completePayment();
                    }
                });
            }

            // Clear cart
            const clearCartBtn = document.getElementById('clearCartBtnModern');
            if (clearCartBtn) {
                clearCartBtn.addEventListener('click', function() {
                    if (confirm('Are you sure you want to clear the cart?')) {
                        clearCart();
                    }
                });
            }

            // Hold order
            const holdBtn = document.getElementById('holdBtnModern');
            if (holdBtn) {
                holdBtn.addEventListener('click', function() {
                    showHoldOrderModal();
                });
            }

            // Customer search in modal
            const customerSearch = document.getElementById('customerSearch');
            if (customerSearch) {
                customerSearch.addEventListener('input', debounce(searchCustomers, 300));
            }

            // Toggle between search and add form
            const showAddForm = document.getElementById('showAddCustomerFormBtn');
            const backToSearch = document.getElementById('backToSearchBtn');
            const cancelAdd = document.getElementById('cancelAddCustomerBtn');

            if (showAddForm) {
                showAddForm.addEventListener('click', () => {
                    document.getElementById('customerSearchSection').style.display = 'none';
                    document.getElementById('addCustomerFormSection').style.display = 'block';
                });
            }

            if (backToSearch || cancelAdd) {
                [backToSearch, cancelAdd].forEach(btn => {
                    if (btn) {
                        btn.addEventListener('click', () => {
                            document.getElementById('customerSearchSection').style.display = 'block';
                            document.getElementById('addCustomerFormSection').style.display = 'none';
                            document.getElementById('quickAddCustomerForm').reset();
                        });
                    }
                });
            }

            // Quick add customer form
            const quickAddForm = document.getElementById('quickAddCustomerForm');
            if (quickAddForm) {
                quickAddForm.addEventListener('submit', function(e) {
                    e.preventDefault();
                    quickAddCustomer();
                });
            }

            // Hold order form
            const holdOrderForm = document.getElementById('holdOrderForm');
            if (holdOrderForm) {
                holdOrderForm.addEventListener('submit', function(e) {
                    e.preventDefault();
                    submitHoldOrder();
                });
            }

            // Print receipt
            const printReceipt = document.getElementById('printReceiptBtn');
            if (printReceipt) {
                printReceipt.addEventListener('click', function() {
                    const orderId = posState.lastOrderId;
                    if (orderId) {
                        window.open(`/pos/print-receipt/${orderId}`, '_blank');
                    }
                });
            }
        }

        // ============================================
        // PRODUCT FUNCTIONS
        // ============================================
        function loadInitialProducts() {
            const featuredProducts = @json($featuredProductsData);
            renderProducts(featuredProducts);
        }

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

        function renderProducts(products) {
            const grid = document.getElementById('productsGridModern');

            if (products.length === 0) {
                grid.innerHTML = `
                    <div style="grid-column: 1 / -1; text-align: center; padding: 3rem; color: #9ca3af;">
                        <i class="ph-magnifying-glass" style="font-size: 4rem; margin-bottom: 1rem; opacity: 0.4;"></i>
                        <p style="font-size: 16px; font-weight: 500;">No products found</p>
                    </div>
                `;
                return;
            }

            grid.innerHTML = products.map(product => `
                <div class="product-card-modern" onclick="addProductToCart(${product.id})">
                    <img src="${product.image || 'https://placehold.co/200x200/e5e7eb/6b7280?text=No+Image'}"
                         alt="${product.name}"
                         class="product-image-modern">
                    <div class="product-name-modern">${product.name}</div>
                    <div class="product-price-modern">$${parseFloat(product.price).toFixed(2)}</div>
                    <div class="product-stock-modern">Stock: ${product.quantity}</div>
                </div>
            `).join('');
        }

        function addProductToCart(productId) {
            fetch('/pos/add-to-cart', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({
                    product_id: productId,
                    quantity: 1
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Update cart state with backend data
                    posState.cart = data.cart.items || [];
                    updateCartDisplay();
                } else {
                    alert(data.message || 'Error adding product to cart');
                }
            })
            .catch(error => {
                console.error('Error adding to cart:', error);
                alert('Error adding product to cart');
            });
        }

        // ============================================
        // CART FUNCTIONS
        // ============================================
        function updateCartDisplay() {
            const cartContainer = document.getElementById('cartItemsModern');
            const subtotalEl = document.getElementById('subtotalModern');
            const totalEl = document.getElementById('totalModern');

            if (!posState.cart || posState.cart.length === 0) {
                cartContainer.innerHTML = `
                    <div class="empty-cart-modern">
                        <i class="ph-shopping-cart-simple"></i>
                        <p>Cart is empty<br>Add products to start</p>
                    </div>
                `;
                subtotalEl.textContent = '$0.00';
                totalEl.textContent = '$0.00';
                updateChargeButton();
                return;
            }

            // Render cart items
            cartContainer.innerHTML = posState.cart.map(item => `
                <div class="cart-item-modern fade-in">
                    <div class="cart-item-header">
                        <div class="cart-item-name">${item.name}</div>
                        <button class="cart-item-remove" onclick="removeFromCart(${item.id})">
                            <i class="ph-x"></i>
                        </button>
                    </div>
                    <div class="cart-item-controls">
                        <div class="quantity-controls">
                            <button class="qty-btn" onclick="updateQuantity(${item.id}, ${item.quantity - 1})">-</button>
                            <div class="qty-display">${item.quantity}</div>
                            <button class="qty-btn" onclick="updateQuantity(${item.id}, ${item.quantity + 1})">+</button>
                        </div>
                        <div class="cart-item-price">$${(item.price * item.quantity).toFixed(2)}</div>
                    </div>
                </div>
            `).join('');

            // Update totals
            calculateTotals();
            calculateChange(); // Recalculate change when cart changes
            updateChargeButton();
        }

        function calculateTotals() {
            if (!posState.cart || posState.cart.length === 0) {
                // Reset input fields when cart is empty
                const discountInput = document.getElementById('discountInputModern');
                const adjustmentInput = document.getElementById('adjustmentInputModern');
                if (discountInput) discountInput.value = '';
                if (adjustmentInput) adjustmentInput.value = '';

                const subtotalEl = document.getElementById('subtotalModern');
                if (subtotalEl) subtotalEl.textContent = '$0.00';

                const totalEl = document.getElementById('totalModern');
                if (totalEl) totalEl.textContent = '$0.00';

                return { subtotal: 0, discountAmount: 0, total: 0 };
            }

            let subtotal = posState.cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);

            let discountAmount = 0;
            if (posState.discountType === 'percentage') {
                discountAmount = subtotal * (posState.discountValue / 100);
            } else {
                discountAmount = posState.discountValue;
            }

            let total = subtotal - discountAmount - posState.adjustmentValue;

            // Update display elements with null checks
            const subtotalEl = document.getElementById('subtotalModern');
            if (subtotalEl) subtotalEl.textContent = '$' + subtotal.toFixed(2);

            const totalEl = document.getElementById('totalModern');
            if (totalEl) totalEl.textContent = '$' + total.toFixed(2);

            // Sync input fields with current values (avoid infinite loop)
            const discountInput = document.getElementById('discountInputModern');
            const adjustmentInput = document.getElementById('adjustmentInputModern');
            const discountTypeSelect = document.getElementById('discountTypeModern');

            if (discountInput && parseFloat(discountInput.value) !== posState.discountValue) {
                discountInput.value = posState.discountValue || '';
            }
            if (adjustmentInput && parseFloat(adjustmentInput.value) !== posState.adjustmentValue) {
                adjustmentInput.value = posState.adjustmentValue || '';
            }
            if (discountTypeSelect && discountTypeSelect.value !== posState.discountType) {
                discountTypeSelect.value = posState.discountType;
            }

            return { subtotal, discountAmount, total };
        }

        function calculateChange() {
            const totals = calculateTotals();

            // Only calculate change if payment method is cash
            if (posState.selectedPaymentMethod === 'cash') {
                const change = Math.max(0, posState.cashReceived - totals.total);
                const changeElement = document.getElementById('changeAmountModern');
                if (changeElement) {
                    changeElement.textContent = '$' + change.toFixed(2);
                }
            } else {
                const changeElement = document.getElementById('changeAmountModern');
                if (changeElement) {
                    changeElement.textContent = '$0.00';
                }
            }
        }

        function updateQuantity(productId, quantity) {
            if (quantity < 1) return;

            fetch('/pos/update-cart', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({
                    product_id: productId,
                    quantity: quantity
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    posState.cart = data.cart.items || [];
                    updateCartDisplay();
                } else {
                    alert(data.message || 'Error updating cart');
                }
            })
            .catch(error => {
                console.error('Error updating cart:', error);
            });
        }

        function removeFromCart(productId) {
            fetch('/pos/remove-from-cart', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({
                    product_id: productId
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    posState.cart = data.cart.items || [];
                    updateCartDisplay();
                }
            })
            .catch(error => {
                console.error('Error removing from cart:', error);
            });
        }

        function clearCart() {
            fetch('/pos/clear-cart', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    posState.cart = [];
                    posState.discountValue = 0;
                    posState.adjustmentValue = 0;
                    updateCartDisplay();
                }
            })
            .catch(error => {
                console.error('Error clearing cart:', error);
            });
        }

        function applyDiscountToCart() {
            fetch('/pos/apply-discount', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({
                    type: posState.discountType,
                    value: posState.discountValue
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Update the display with new discount
                    calculateTotals();
                }
            })
            .catch(error => {
                console.error('Error applying discount:', error);
            });
        }

        // ============================================
        // PAYMENT FUNCTIONS
        // ============================================
        function updateChargeButton() {
            const chargeBtn = document.getElementById('chargeBtnModern');
            const hasItems = posState.cart.length > 0;
            const hasPaymentMethod = posState.selectedPaymentMethod !== null;

            chargeBtn.disabled = !(hasItems && hasPaymentMethod);
        }

        function completePayment() {
            const totals = calculateTotals();
            const cashReceived = posState.selectedPaymentMethod === 'cash' ? posState.cashReceived : totals.total;
            const change = posState.selectedPaymentMethod === 'cash' ? Math.max(0, cashReceived - totals.total) : 0;

            const requestData = {
                payment_method: posState.selectedPaymentMethod,
                customer_id: posState.customer ? posState.customer.id : null,
                paid_amount: cashReceived,
                change_amount: change,
                adjustment_amount: posState.adjustmentValue,
                adjustment_reason: posState.adjustmentReason,
                discount_value: posState.discountValue,
                discount_type: posState.discountType
            };

            fetch('/pos/complete-payment', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify(requestData)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showPaymentSuccess(data);
                    resetPOS();
                } else {
                    alert(data.message || 'Error completing payment');
                }
            })
            .catch(error => {
                console.error('Error completing payment:', error);
                alert('Error completing payment: ' + error.message);
            });
        }

        function showPaymentSuccess(data) {
            posState.lastOrderId = data.order_id;
            const messageEl = document.getElementById('orderSuccessMessage');
            if (messageEl) {
                messageEl.textContent = `Order #${data.order_number} completed successfully!`;
            }

            const modalElement = document.getElementById('paymentSuccessModal');
            if (modalElement) {
                const modal = new bootstrap.Modal(modalElement);
                modal.show();
            } else {
                alert(`Payment completed successfully!\nOrder: ${data.order_number}`);
            }
        }

        function resetPOS() {
            posState.cart = [];
            posState.customer = null;
            posState.selectedPaymentMethod = null;
            posState.discountValue = 0;
            posState.adjustmentValue = 0;
            posState.adjustmentReason = '';
            posState.cashReceived = 0;

            // Safely reset DOM elements with null checks
            document.querySelectorAll('.payment-method-btn').forEach(b => b.classList.remove('active'));

            const cashInput = document.getElementById('cashInputModern');
            if (cashInput) cashInput.value = '';

            const discountInput = document.getElementById('discountInputModern');
            if (discountInput) discountInput.value = '';

            const adjustmentInput = document.getElementById('adjustmentInputModern');
            if (adjustmentInput) adjustmentInput.value = '';

            const discountTypeSelect = document.getElementById('discountTypeModern');
            if (discountTypeSelect) discountTypeSelect.value = 'percentage';

            const cashInputSection = document.getElementById('cashInputSectionModern');
            if (cashInputSection) cashInputSection.classList.remove('active');

            updateCustomerDisplay(null);
            updateCartDisplay();
        }

        // ============================================
        // MODAL FUNCTIONS
        // ============================================
        function showCustomerModal() {
            // Trigger the existing customer modal
            const modalElement = document.getElementById('customerModal');
            if (modalElement) {
                const modal = new bootstrap.Modal(modalElement);
                modal.show();
            }
        }

        function showHoldOrderModal() {
            const modalElement = document.getElementById('holdOrderModal');
            if (modalElement) {
                const modal = new bootstrap.Modal(modalElement);
                modal.show();
            }
        }

        // ============================================
        // CUSTOMER FUNCTIONS
        // ============================================
        function searchCustomers() {
            const query = document.getElementById('customerSearch').value;
            const resultsContainer = document.getElementById('customerResults');
            const hint = document.getElementById('customerSearchHint');

            if (query.length < 2) {
                resultsContainer.innerHTML = '';
                hint.style.display = 'block';
                return;
            }

            hint.style.display = 'none';

            fetch(`/pos/customer-search?q=${encodeURIComponent(query)}`)
                .then(response => response.json())
                .then(data => {
                    if (data.customers && data.customers.length > 0) {
                        resultsContainer.innerHTML = data.customers.map(customer => `
                            <a href="#" class="list-group-item list-group-item-action" onclick="selectCustomer(${customer.id}, '${customer.name.replace(/'/g, "\\'")}'); return false;">
                                <div class="d-flex w-100 justify-content-between">
                                    <h6 class="mb-1">${customer.name}</h6>
                                    <small>${customer.phone || ''}</small>
                                </div>
                                ${customer.email ? `<small class="text-muted">${customer.email}</small>` : ''}
                            </a>
                        `).join('');
                    } else {
                        resultsContainer.innerHTML = '<div class="text-center text-muted py-3">No customers found</div>';
                    }
                })
                .catch(error => {
                    console.error('Error searching customers:', error);
                });
        }

        function selectCustomer(customerId, customerName) {
            fetch('/pos/add-customer', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({ customer_id: customerId })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    posState.customer = data.customer;
                    updateCustomerDisplay(customerName || 'Guest');
                    const modal = document.getElementById('customerModal');
                    if (modal) {
                        bootstrap.Modal.getInstance(modal).hide();
                    }
                }
            })
            .catch(error => {
                console.error('Error selecting customer:', error);
            });
        }

        function updateCustomerDisplay(customerName) {
            const display = document.getElementById('customerDisplayModern');
            if (customerName) {
                display.innerHTML = `
                    <span class="customer-name">${customerName}</span>
                    <button class="customer-change-btn" onclick="removeCustomer()">Change</button>
                `;
            } else {
                display.innerHTML = '<span class="customer-name">Guest Order</span>';
            }
        }

        function removeCustomer() {
            fetch('/pos/remove-customer', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    posState.customer = null;
                    updateCustomerDisplay(null);
                }
            });
        }

        function quickAddCustomer() {
            const name = document.getElementById('quickCustomerName').value;
            const email = document.getElementById('quickCustomerEmail').value;
            const phone = document.getElementById('quickCustomerPhone').value;
            const address = document.getElementById('quickCustomerAddress').value;

            fetch('/pos/quick-add-customer', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({ name, email, phone, address })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    posState.customer = data.customer;
                    const customerName = data.customer ? data.customer.name : 'Guest';
                    updateCustomerDisplay(customerName);
                    const modal = document.getElementById('customerModal');
                    if (modal) {
                        bootstrap.Modal.getInstance(modal).hide();
                    }
                    const form = document.getElementById('quickAddCustomerForm');
                    if (form) form.reset();
                } else {
                    alert(data.message || 'Error creating customer');
                }
            })
            .catch(error => {
                console.error('Error creating customer:', error);
                alert('Error creating customer');
            });
        }

        // ============================================
        // HOLD ORDER FUNCTION
        // ============================================
        function submitHoldOrder() {
            const reason = document.getElementById('holdReason').value;
            const sendNotification = document.getElementById('sendNotification').checked;

            fetch('/pos/hold-order', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({
                    hold_reason: reason,
                    send_notification: sendNotification,
                    customer_id: posState.customer ? posState.customer.id : null
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert(`Order ${data.order_number} has been held successfully`);
                    bootstrap.Modal.getInstance(document.getElementById('holdOrderModal')).hide();
                    resetPOS();
                } else {
                    alert(data.message || 'Error holding order');
                }
            })
            .catch(error => {
                console.error('Error holding order:', error);
                alert('Error holding order');
            });
        }

        // ============================================
        // UTILITY FUNCTIONS
        // ============================================
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
    </script>
</body>
</html>
