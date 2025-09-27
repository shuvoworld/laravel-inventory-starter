<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>POS Receipt - Order #{{ $item->order_number }}</title>
    <style>
        @media print {
            @page {
                size: 80mm auto;
                margin: 0;
            }
            body {
                margin: 0;
                padding: 0;
            }
            .no-print { display: none !important; }
        }

        body {
            font-family: 'Courier New', monospace;
            font-size: 12px;
            line-height: 1.2;
            margin: 0;
            padding: 10px;
            width: 80mm;
            background: white;
            color: black;
        }

        .receipt {
            width: 100%;
            max-width: 80mm;
        }

        .header {
            text-align: center;
            border-bottom: 1px dashed #000;
            padding-bottom: 10px;
            margin-bottom: 10px;
        }

        .store-name {
            font-size: 16px;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .store-info {
            font-size: 10px;
            line-height: 1.1;
        }

        .receipt-title {
            font-size: 14px;
            font-weight: bold;
            margin: 10px 0;
        }

        .order-info {
            margin-bottom: 10px;
            font-size: 11px;
        }

        .customer-info {
            border-top: 1px dashed #000;
            border-bottom: 1px dashed #000;
            padding: 8px 0;
            margin: 10px 0;
            font-size: 11px;
        }

        .items-section {
            margin: 10px 0;
        }

        .item-row {
            margin-bottom: 5px;
            font-size: 11px;
        }

        .item-name {
            font-weight: bold;
            margin-bottom: 2px;
        }

        .item-details {
            display: flex;
            justify-content: space-between;
            margin-bottom: 3px;
        }

        .item-line {
            display: flex;
            justify-content: space-between;
        }

        .separator {
            border-top: 1px dashed #000;
            margin: 10px 0;
        }

        .totals {
            font-size: 11px;
        }

        .total-line {
            display: flex;
            justify-content: space-between;
            margin-bottom: 2px;
        }

        .final-total {
            font-weight: bold;
            font-size: 13px;
            border-top: 1px solid #000;
            border-bottom: 1px solid #000;
            padding: 5px 0;
            margin: 5px 0;
        }

        .footer {
            text-align: center;
            margin-top: 15px;
            font-size: 10px;
            border-top: 1px dashed #000;
            padding-top: 10px;
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
            z-index: 1000;
        }

        .print-button:hover {
            background: #0056b3;
        }

        /* Make text monospace for better alignment */
        .mono {
            font-family: 'Courier New', monospace;
        }

        /* Status styling */
        .status {
            text-align: center;
            margin: 5px 0;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <button class="print-button no-print" onclick="window.print()">🖨️ Print</button>

    <div class="receipt">
        <!-- Header -->
        <div class="header">
            <div class="store-name">YOUR COMPANY NAME</div>
            <div class="store-info">
                123 Business Street<br>
                City, State 12345<br>
                Phone: (555) 123-4567<br>
                Email: sales@company.com
            </div>
        </div>

        <!-- Receipt Title -->
        <div class="receipt-title" style="text-align: center;">SALES RECEIPT</div>

        <!-- Order Information -->
        <div class="order-info">
            <div>Order #: {{ $item->order_number }}</div>
            <div>Date: {{ $item->order_date->format('M d, Y H:i') }}</div>
            <div>Cashier: {{ auth()->user()->name }}</div>
        </div>

        <!-- Customer Information -->
        <div class="customer-info">
            <div><strong>Customer:</strong></div>
            <div>{{ $item->customer->name }}</div>
            @if($item->customer->phone)
                <div>Phone: {{ $item->customer->phone }}</div>
            @endif
        </div>

        <!-- Status -->
        <div class="status">STATUS: {{ strtoupper($item->status) }}</div>

        <div class="separator"></div>

        <!-- Items -->
        <div class="items-section">
            @foreach($item->items as $orderItem)
                <div class="item-row">
                    <div class="item-name">{{ $orderItem->product->name }}</div>
                    @if($orderItem->product->sku)
                        <div style="font-size: 10px; color: #666;">SKU: {{ $orderItem->product->sku }}</div>
                    @endif
                    <div class="item-line">
                        <span>{{ number_format($orderItem->quantity) }} x ${{ number_format($orderItem->unit_price, 2) }}</span>
                        <span>${{ number_format($orderItem->total_price, 2) }}</span>
                    </div>
                </div>
                @if(!$loop->last)
                    <div style="margin: 5px 0; border-top: 1px dotted #ccc;"></div>
                @endif
            @endforeach
        </div>

        <div class="separator"></div>

        <!-- Totals -->
        <div class="totals">
            <div class="total-line">
                <span>Subtotal:</span>
                <span>${{ number_format($item->subtotal, 2) }}</span>
            </div>

            @if($item->discount_amount > 0)
                <div class="total-line">
                    <span>Discount:</span>
                    <span>-${{ number_format($item->discount_amount, 2) }}</span>
                </div>
            @endif

            @if($item->tax_amount > 0)
                <div class="total-line">
                    <span>Tax:</span>
                    <span>${{ number_format($item->tax_amount, 2) }}</span>
                </div>
            @endif

            <div class="final-total">
                <div class="total-line">
                    <span>TOTAL:</span>
                    <span>${{ number_format($item->total_amount, 2) }}</span>
                </div>
            </div>

            <div style="text-align: center; margin-top: 10px; font-size: 10px;">
                Items: {{ $item->items->count() }} | Qty: {{ $item->items->sum('quantity') }}
            </div>
        </div>

        @if($item->notes)
            <div class="separator"></div>
            <div style="font-size: 10px;">
                <strong>Notes:</strong><br>
                {{ $item->notes }}
            </div>
        @endif

        <!-- Footer -->
        <div class="footer">
            <div>Thank you for your business!</div>
            <div style="margin-top: 5px;">{{ now()->format('M d, Y H:i') }}</div>
            <div style="margin-top: 10px; font-size: 9px;">
                ================================================<br>
                This receipt was generated automatically<br>
                Please keep for your records
            </div>
        </div>
    </div>

    <script>
        // Auto-print when opened with print parameter
        if (window.location.search.includes('print=true')) {
            window.onload = function() {
                setTimeout(() => {
                    window.print();
                    // Close window after printing (if opened in popup)
                    setTimeout(() => {
                        if (window.opener) {
                            window.close();
                        }
                    }, 1000);
                }, 500);
            };
        }

        // Print button functionality
        function printReceipt() {
            window.print();
        }

        // Optional: Add keyboard shortcut for printing
        document.addEventListener('keydown', function(e) {
            if (e.ctrlKey && e.key === 'p') {
                e.preventDefault();
                printReceipt();
            }
        });
    </script>
</body>
</html>