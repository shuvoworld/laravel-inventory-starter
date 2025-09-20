<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice - Order #{{ $item->order_number }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            color: #333;
            line-height: 1.6;
        }
        .invoice-container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
        }
        .invoice-header {
            border-bottom: 3px solid #007bff;
            padding-bottom: 20px;
            margin-bottom: 30px;
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
            font-size: 28px;
            font-weight: bold;
            color: #007bff;
            margin-bottom: 5px;
        }
        .company-details {
            color: #666;
            font-size: 14px;
        }
        .invoice-title {
            font-size: 36px;
            font-weight: bold;
            color: #333;
            margin-bottom: 10px;
        }
        .invoice-number {
            font-size: 18px;
            color: #007bff;
            font-weight: bold;
        }
        .invoice-date {
            color: #666;
            margin-top: 5px;
        }
        .billing-section {
            margin: 30px 0;
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
            font-size: 16px;
            font-weight: bold;
            color: #007bff;
            margin-bottom: 10px;
            border-bottom: 1px solid #eee;
            padding-bottom: 5px;
        }
        .customer-info {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
        }
        .customer-name {
            font-weight: bold;
            font-size: 16px;
            margin-bottom: 5px;
        }
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin: 30px 0;
            border: 1px solid #ddd;
        }
        .items-table th {
            background: #007bff;
            color: white;
            padding: 12px;
            text-align: left;
            font-weight: bold;
        }
        .items-table td {
            padding: 12px;
            border-bottom: 1px solid #eee;
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
            width: 300px;
            margin-top: 20px;
        }
        .total-row {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px solid #eee;
        }
        .total-row.final {
            border-top: 2px solid #007bff;
            border-bottom: 2px solid #007bff;
            font-weight: bold;
            font-size: 18px;
            color: #007bff;
            margin-top: 10px;
            padding: 12px 0;
        }
        .status-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
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
            margin-top: 30px;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 5px;
            border-left: 4px solid #007bff;
        }
        .footer {
            margin-top: 50px;
            padding-top: 20px;
            border-top: 1px solid #eee;
            text-align: center;
            color: #666;
            font-size: 12px;
        }
        @media print {
            .no-print { display: none !important; }
            body { margin: 0; }
            .invoice-container { box-shadow: none; }
        }
        .print-button {
            position: fixed;
            top: 20px;
            right: 20px;
            background: #007bff;
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            font-weight: bold;
            box-shadow: 0 2px 4px rgba(0,0,0,0.2);
            z-index: 1000;
        }
        .print-button:hover {
            background: #0056b3;
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
                    <div class="customer-name">{{ $item->customer->name }}</div>
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
                    <th style="width: 50%">Product</th>
                    <th style="width: 15%" class="text-center">Qty</th>
                    <th style="width: 15%" class="text-right">Unit Price</th>
                    <th style="width: 20%" class="text-right">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($item->items as $orderItem)
                <tr>
                    <td>
                        <strong>{{ $orderItem->product->name }}</strong><br>
                        @if($orderItem->product->sku)
                            <small style="color: #666;">SKU: {{ $orderItem->product->sku }}</small><br>
                        @endif
                        @if($orderItem->product->unit)
                            <small style="color: #666;">Unit: {{ $orderItem->product->unit }}</small>
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
                    <span>${{ number_format($item->subtotal, 2) }}</span>
                </div>
                @if($item->discount_amount > 0)
                <div class="total-row">
                    <span>Discount:</span>
                    <span>-${{ number_format($item->discount_amount, 2) }}</span>
                </div>
                @endif
                @if($item->tax_amount > 0)
                <div class="total-row">
                    <span>Tax:</span>
                    <span>${{ number_format($item->tax_amount, 2) }}</span>
                </div>
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