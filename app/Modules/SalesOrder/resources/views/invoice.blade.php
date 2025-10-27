<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice - Order #{{ $item->order_number }}</title>
    <style>
        @page {
            size: A4;
            margin: 15mm 20mm 15mm 20mm;
        }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 0;
            color: #333;
            line-height: 1.3;
            font-size: 11px;
            background: #f5f5f5;
        }
        .invoice-container {
            max-width: 210mm;
            width: 210mm;
            min-height: 297mm;
            margin: 0 auto;
            background: white;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            padding: 15mm 20mm;
            box-sizing: border-box;
        }
        .invoice-header {
            border-bottom: 2px solid #007bff;
            padding-bottom: 10px;
            margin-bottom: 15px;
        }
        .company-info {
            float: left;
            width: 50%;
        }
        .invoice-info {
            float: right;
            width: 45%;
            text-align: right;
        }
        .clearfix::after {
            content: "";
            display: table;
            clear: both;
        }
        .company-name {
            font-size: 20px;
            font-weight: bold;
            color: #007bff;
            margin-bottom: 3px;
        }
        .company-details {
            color: #666;
            font-size: 10px;
            line-height: 1.2;
        }
        .invoice-title {
            font-size: 24px;
            font-weight: bold;
            color: #333;
            margin-bottom: 5px;
        }
        .invoice-number {
            font-size: 14px;
            color: #007bff;
            font-weight: bold;
        }
        .invoice-date {
            color: #666;
            margin-top: 3px;
            font-size: 10px;
        }
        .billing-section {
            margin: 15px 0;
        }
        .billing-to {
            float: left;
            width: 48%;
        }
        .billing-details {
            float: right;
            width: 48%;
        }
        .section-title {
            font-size: 12px;
            font-weight: bold;
            color: #007bff;
            margin-bottom: 5px;
            border-bottom: 1px solid #eee;
            padding-bottom: 2px;
        }
        .customer-info {
            background: #f8f9fa;
            padding: 8px;
            border-radius: 3px;
            font-size: 10px;
        }
        .customer-name {
            font-weight: bold;
            font-size: 12px;
            margin-bottom: 3px;
        }
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0;
            border: 1px solid #ddd;
            font-size: 10px;
        }
        .items-table th {
            background: #007bff;
            color: white;
            padding: 6px 8px;
            text-align: left;
            font-weight: bold;
            font-size: 10px;
        }
        .items-table td {
            padding: 6px 8px;
            border-bottom: 1px solid #eee;
            font-size: 10px;
        }
        .items-table tr:nth-child(even) {
            background: #f8f9fa;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .total-section {
            float: right;
            width: 220px;
            margin-top: 15px;
        }
        .total-row {
            display: flex;
            justify-content: space-between;
            padding: 4px 0;
            border-bottom: 1px solid #eee;
            font-size: 10px;
        }
        .total-row.final {
            border-top: 2px solid #007bff;
            border-bottom: 2px solid #007bff;
            font-weight: bold;
            font-size: 14px;
            color: #007bff;
            margin-top: 5px;
            padding: 6px 0;
        }
        .status-badge {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 12px;
            font-size: 9px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .status-pending { background: #fff3cd; color: #856404; }
        .status-confirmed { background: #d1ecf1; color: #0c5460; }
        .status-processing { background: #cce5ff; color: #004085; }
        .status-shipped { background: #e2e3e5; color: #383d41; }
        .status-delivered { background: #d4edda; color: #155724; }
        .status-cancelled { background: #f8d7da; color: #721c24; }
        .notes-section {
            margin-top: 15px;
            padding: 8px;
            background: #f8f9fa;
            border-radius: 3px;
            border-left: 3px solid #007bff;
            font-size: 10px;
        }
        .footer {
            margin-top: 20px;
            padding-top: 10px;
            border-top: 1px solid #eee;
            text-align: center;
            color: #666;
            font-size: 9px;
        }
        @media print {
            .no-print { display: none !important; }
            body {
                margin: 0;
                padding: 0;
                font-size: 10px;
                background: white;
            }
            .invoice-container {
                box-shadow: none;
                max-width: 210mm;
                width: 210mm;
                min-height: 297mm;
                margin: 0;
                padding: 15mm 20mm;
                box-sizing: border-box;
            }
            .print-button {
                display: none !important;
            }
            @page {
                size: A4;
                margin: 10mm 10mm 10mm 10mm;
            }
        }
        .print-button {
            position: fixed;
            top: 10px;
            right: 10px;
            background: #007bff;
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 3px;
            cursor: pointer;
            font-size: 12px;
            font-weight: bold;
            box-shadow: 0 1px 3px rgba(0,0,0,0.2);
            z-index: 1000;
        }
        .print-button:hover {
            background: #0056b3;
        }
        /* Compact variant display */
        .variant-info {
            font-size: 9px;
            color: #666;
            line-height: 1.2;
        }
        /* Reduce line spacing */
        p, div {
            margin: 2px 0;
        }
        /* Compact total section */
        .amount-value {
            font-weight: bold;
        }
        /* Make table more compact */
        .items-table .product-name {
            font-weight: bold;
            margin-bottom: 2px;
        }
        .items-table .product-details {
            color: #666;
            font-size: 9px;
        }

        /* Responsive design for screens */
        @media screen and (max-width: 900px) {
            .invoice-container {
                width: 100%;
                max-width: 210mm;
                margin: 20px auto;
                padding: 20px;
                min-height: auto;
            }
        }

        @media screen and (max-width: 600px) {
            .company-info,
            .invoice-info {
                float: none;
                width: 100%;
                text-align: left;
                margin-bottom: 15px;
            }
            .invoice-info {
                text-align: left;
            }
            .billing-to,
            .billing-details {
                float: none;
                width: 100%;
                margin-bottom: 15px;
            }
            .total-section {
                float: none;
                width: 100%;
                max-width: 300px;
            }
            .items-table th,
            .items-table td {
                padding: 4px;
                font-size: 9px;
            }
        }
    </style>
</head>
<body>
    <button class="print-button no-print" onclick="window.print()">
        🖨️ Print Invoice
    </button>

    <div class="invoice-container">
        <!-- Header -->
        <div class="invoice-header clearfix">
            <div class="company-info">
                <div class="company-name">Your Company Name</div>
                <div class="company-details">
                    123 Business Street<br>
                    City, State 12345<br>
                    Phone: (555) 123-4567<br>
                    Email: sales@company.com
                </div>
            </div>
            <div class="invoice-info">
                <div class="invoice-title">INVOICE</div>
                <div class="invoice-number">#{{ $item->order_number }}</div>
                <div class="invoice-date">
                    <strong>Date:</strong> {{ $item->order_date->format('M d, Y') }}<br>
                    <strong>Status:</strong>
                    <span class="status-badge status-{{ $item->status }}">{{ ucfirst($item->status) }}</span>
                </div>
            </div>
        </div>

        <!-- Billing Information -->
        <div class="billing-section clearfix">
            <div class="billing-to">
                <div class="section-title">Bill To:</div>
                <div class="customer-info">
                    <div class="customer-name">{{ $item->customer ? $item->customer->name : 'Guest Customer' }}</div>
                    @if($item->customer->email)
                        <div>{{ $item->customer->email }}</div>
                    @endif
                    @if($item->customer->phone)
                        <div>{{ $item->customer->phone }}</div>
                    @endif
                    @if($item->customer->address)
                        <div style="margin-top: 10px;">
                            {{ $item->customer->address }}<br>
                            @if($item->customer->city || $item->customer->state || $item->customer->postal_code)
                                {{ $item->customer->city }}{{ $item->customer->city && ($item->customer->state || $item->customer->postal_code) ? ', ' : '' }}{{ $item->customer->state }} {{ $item->customer->postal_code }}<br>
                            @endif
                            @if($item->customer->country)
                                {{ $item->customer->country }}
                            @endif
                        </div>
                    @endif
                </div>
            </div>
            <div class="billing-details">
                <div class="section-title">Invoice Details:</div>
                <div class="customer-info">
                    <strong>Order Number:</strong> {{ $item->order_number }}<br>
                    <strong>Order Date:</strong> {{ $item->order_date->format('M d, Y') }}<br>
                    <strong>Due Date:</strong> {{ $item->order_date->addDays(30)->format('M d, Y') }}<br>
                    <strong>Total Items:</strong> {{ $item->items->count() }}<br>
                    <strong>Created:</strong> {{ $item->created_at->format('M d, Y H:i') }}
                </div>
            </div>
        </div>

        <!-- Items Table -->
        <table class="items-table">
            <thead>
                <tr>
                    <th style="width: 45%">Product</th>
                    <th style="width: 12%" class="text-center">Qty</th>
                    <th style="width: 18%" class="text-right">Unit Price</th>
                    <th style="width: 25%" class="text-right">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($item->items as $orderItem)
                <tr>
                    <td>
                        <div class="product-name">{{ $orderItem->getDisplayName() }}</div>
                        @if($orderItem->variant?->sku ?? $orderItem->product->sku)
                            <div class="product-details">SKU: {{ $orderItem->variant?->sku ?? $orderItem->product->sku }}</div>
                        @endif
                        @if($orderItem->product->unit)
                            <div class="product-details">Unit: {{ $orderItem->product->unit }}</div>
                        @endif
                        @if($orderItem->variant)
                            <div class="variant-info">{{ $orderItem->variant->variant_name }}</div>
                        @endif
                    </td>
                    <td class="text-center">{{ number_format($orderItem->quantity) }}</td>
                    <td class="text-right">${{ number_format($orderItem->unit_price, 2) }}</td>
                    <td class="text-right"><strong>${{ number_format($orderItem->total_price, 2) }}</strong></td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Total Section -->
        <div class="clearfix">
            <div class="total-section">
                <div class="total-row">
                    <span>Subtotal:</span>
                    <span class="amount-value">${{ number_format($item->subtotal, 2) }}</span>
                </div>
                @if($item->discount_amount > 0)
                <div class="total-row">
                    <span>Discount:</span>
                    <span class="amount-value">-${{ number_format($item->discount_amount, 2) }}</span>
                </div>
                @endif
                @if($item->tax_amount > 0)
                <div class="total-row">
                    <span>Tax:</span>
                    <span class="amount-value">${{ number_format($item->tax_amount, 2) }}</span>
                </div>
                @endif
                @if($item->paid_amount > 0)
                <div class="total-row">
                    <span>Paid:</span>
                    <span class="amount-value">${{ number_format($item->paid_amount, 2) }}</span>
                </div>
                @if($item->change_amount > 0)
                <div class="total-row">
                    <span>Change:</span>
                    <span class="amount-value">${{ number_format($item->change_amount, 2) }}</span>
                </div>
                @endif
                @endif
                <div class="total-row final">
                    <span>TOTAL:</span>
                    <span>${{ number_format($item->total_amount, 2) }}</span>
                </div>
            </div>
        </div>

        @if($item->notes)
        <!-- Notes -->
        <div class="notes-section">
            <div class="section-title">Notes:</div>
            <p>{{ $item->notes }}</p>
        </div>
        @endif

        <!-- Footer -->
        <div class="footer">
            <p>Thank you for your business!</p>
            <p>This invoice was generated on {{ now()->format('M d, Y \a\t H:i') }}</p>
        </div>
    </div>

    <script>
        // Auto-print when opened in new window
        if (window.location.search.includes('print=true')) {
            window.onload = function() {
                setTimeout(() => {
                    window.print();
                }, 500);
            };
        }
    </script>
</body>
</html>