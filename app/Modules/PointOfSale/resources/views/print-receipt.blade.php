<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Receipt - {{ $salesOrder->order_number }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Courier New', monospace;
            font-size: 12pt;
            line-height: 1.4;
            padding: 20px;
            max-width: 80mm;
            margin: 0 auto;
        }

        .receipt {
            width: 100%;
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px dashed #000;
            padding-bottom: 10px;
        }

        .company-name {
            font-size: 18pt;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .company-info {
            font-size: 10pt;
            margin-bottom: 3px;
        }

        .receipt-title {
            font-size: 14pt;
            font-weight: bold;
            margin: 15px 0 10px;
            text-align: center;
        }

        .info-section {
            margin-bottom: 15px;
            font-size: 10pt;
        }

        .info-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 3px;
        }

        .items-table {
            width: 100%;
            margin: 15px 0;
            border-top: 1px dashed #000;
            border-bottom: 1px dashed #000;
            padding: 10px 0;
        }

        .item-row {
            margin-bottom: 8px;
        }

        .item-name {
            font-weight: bold;
            margin-bottom: 2px;
        }

        .item-details {
            display: flex;
            justify-content: space-between;
            font-size: 10pt;
        }

        .totals-section {
            margin: 15px 0;
        }

        .total-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 5px;
            font-size: 11pt;
        }

        .total-row.grand-total {
            font-size: 14pt;
            font-weight: bold;
            border-top: 2px solid #000;
            padding-top: 8px;
            margin-top: 8px;
        }

        .payment-info {
            margin: 15px 0;
            padding: 10px 0;
            border-top: 1px dashed #000;
            border-bottom: 1px dashed #000;
        }

        .footer {
            text-align: center;
            margin-top: 20px;
            font-size: 10pt;
        }

        .thank-you {
            font-weight: bold;
            margin: 10px 0;
        }

        .barcode {
            text-align: center;
            margin: 15px 0;
            font-size: 10pt;
            letter-spacing: 2px;
        }

        @media print {
            body {
                padding: 0;
            }

            @page {
                margin: 0;
                size: 80mm auto;
            }
        }
    </style>
</head>
<body>
    <div class="receipt">
        <!-- Header -->
        <div class="header">
            <div class="company-name">{{ config('app.name') }}</div>
            <div class="company-info">Point of Sale Receipt</div>
            <div class="company-info">Tel: +1 (555) 123-4567</div>
            <div class="company-info">Email: info@company.com</div>
        </div>

        <!-- Receipt Title -->
        <div class="receipt-title">SALES RECEIPT</div>

        <!-- Order Information -->
        <div class="info-section">
            <div class="info-row">
                <span>Order No:</span>
                <strong>{{ $salesOrder->order_number }}</strong>
            </div>
            <div class="info-row">
                <span>Date:</span>
                <span>{{ $salesOrder->order_date->format('M d, Y H:i') }}</span>
            </div>
            @if($salesOrder->customer)
            <div class="info-row">
                <span>Customer:</span>
                <span>{{ $salesOrder->customer->name }}</span>
            </div>
            @endif
            <div class="info-row">
                <span>Payment:</span>
                <span>{{ strtoupper(str_replace('_', ' ', $salesOrder->payment_method)) }}</span>
            </div>
            <div class="info-row">
                <span>Cashier:</span>
                <span>{{ auth()->user()->name }}</span>
            </div>
        </div>

        <!-- Items -->
        <div class="items-table">
            @foreach($salesOrder->items as $item)
            <div class="item-row">
                <div class="item-name">{{ $item->product->name }}</div>
                <div class="item-details">
                    <span>{{ $item->quantity }} x ${{ number_format($item->unit_price, 2) }}</span>
                    <strong>${{ number_format($item->total_price, 2) }}</strong>
                </div>
            </div>
            @endforeach
        </div>

        <!-- Totals -->
        <div class="totals-section">
            <div class="total-row">
                <span>Subtotal:</span>
                <span>${{ number_format($salesOrder->subtotal, 2) }}</span>
            </div>

            @if($salesOrder->tax_amount > 0)
            <div class="total-row">
                <span>Tax:</span>
                <span>${{ number_format($salesOrder->tax_amount, 2) }}</span>
            </div>
            @endif

            @if($salesOrder->discount_amount > 0)
            <div class="total-row">
                <span>Discount:</span>
                <span>-${{ number_format($salesOrder->discount_amount, 2) }}</span>
            </div>
            @endif

            @if($salesOrder->notes && str_contains($salesOrder->notes, 'Adjustment'))
            <div class="total-row">
                <span>Adjustment:</span>
                <span>{{ preg_match('/\(-\$([\d.]+)\)/', $salesOrder->notes, $matches) ? '-$' . $matches[1] : '' }}</span>
            </div>
            @endif

            <div class="total-row grand-total">
                <span>TOTAL:</span>
                <span>${{ number_format($salesOrder->total_amount, 2) }}</span>
            </div>
        </div>

        <!-- Payment Details -->
        <div class="payment-info">
            <div class="total-row">
                <span>Amount Paid:</span>
                <span>${{ number_format($salesOrder->paid_amount, 2) }}</span>
            </div>
            @if($salesOrder->change_amount > 0)
            <div class="total-row">
                <span>Change:</span>
                <span>${{ number_format($salesOrder->change_amount, 2) }}</span>
            </div>
            @endif
        </div>

        <!-- Barcode/Order Number -->
        <div class="barcode">
            {{ $salesOrder->order_number }}
        </div>

        <!-- Footer -->
        <div class="footer">
            <div class="thank-you">THANK YOU FOR YOUR PURCHASE!</div>
            <div>Please come again</div>
            <div style="margin-top: 10px; font-size: 9pt;">
                Items sold are not returnable
            </div>
            <div style="margin-top: 15px; font-size: 9pt;">
                Powered by {{ config('app.name') }}
            </div>
        </div>
    </div>

    <script>
        // Auto-focus window for printing
        window.focus();
    </script>
</body>
</html>
