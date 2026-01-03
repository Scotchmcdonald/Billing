<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Invoice {{ $invoice->invoice_number }}</title>
    <style>
        body {
            font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif;
            font-size: 14px;
            line-height: 1.6;
            color: #333;
        }
        .invoice-box {
            max-width: 800px;
            margin: auto;
            padding: 30px;
            border: 1px solid #eee;
            box-shadow: 0 0 10px rgba(0, 0, 0, .15);
        }
        .header {
            margin-bottom: 40px;
        }
        .header td {
            vertical-align: top;
        }
        .header .title {
            font-size: 45px;
            line-height: 45px;
            color: #333;
        }
        .info-table {
            width: 100%;
            margin-bottom: 20px;
        }
        .info-table td {
            padding: 5px;
            vertical-align: top;
        }
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .items-table th {
            background: #f5f5f5;
            border-bottom: 1px solid #ddd;
            font-weight: bold;
            padding: 10px;
            text-align: left;
        }
        .items-table td {
            border-bottom: 1px solid #eee;
            padding: 10px;
        }
        .items-table .total-row td {
            border-top: 2px solid #eee;
            font-weight: bold;
        }
        .text-right {
            text-align: right;
        }
        .status-paid {
            color: green;
            font-weight: bold;
            border: 2px solid green;
            padding: 5px 10px;
            transform: rotate(-5deg);
            display: inline-block;
        }
        .status-unpaid {
            color: red;
            font-weight: bold;
            border: 2px solid red;
            padding: 5px 10px;
            transform: rotate(-5deg);
            display: inline-block;
        }
    </style>
</head>
<body>
    <div class="invoice-box">
        <table class="info-table header">
            <tr>
                <td class="title">
                    INVOICE
                </td>
                <td class="text-right">
                    Invoice #: {{ $invoice->invoice_number }}<br>
                    Created: {{ $invoice->issue_date->format('M d, Y') }}<br>
                    Due: {{ $invoice->due_date->format('M d, Y') }}
                </td>
            </tr>
        </table>

        <table class="info-table">
            <tr>
                <td>
                    <strong>Bill To:</strong><br>
                    {{ $invoice->company->name }}<br>
                    {{ $invoice->company->address }}<br>
                    {{ $invoice->company->city }}, {{ $invoice->company->state }} {{ $invoice->company->zip }}
                </td>
                <td class="text-right">
                    <strong>Status:</strong><br>
                    @if($invoice->status == 'paid')
                        <span class="status-paid">PAID</span>
                    @else
                        <span class="status-unpaid">{{ strtoupper($invoice->status) }}</span>
                    @endif
                </td>
            </tr>
        </table>

        <table class="items-table">
            <thead>
                <tr>
                    <th>Item</th>
                    <th class="text-right">Price</th>
                    <th class="text-right">Qty</th>
                    <th class="text-right">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($invoice->lineItems as $item)
                <tr>
                    <td>{{ $item->description }}</td>
                    <td class="text-right">${{ number_format($item->unit_price, 2) }}</td>
                    <td class="text-right">{{ $item->quantity }}</td>
                    <td class="text-right">${{ number_format($item->subtotal, 2) }}</td>
                </tr>
                @endforeach
                
                <tr class="total-row">
                    <td colspan="3" class="text-right">Subtotal:</td>
                    <td class="text-right">${{ number_format($invoice->subtotal, 2) }}</td>
                </tr>
                @if($invoice->tax_total > 0)
                <tr class="total-row">
                    <td colspan="3" class="text-right">Tax:</td>
                    <td class="text-right">${{ number_format($invoice->tax_total, 2) }}</td>
                </tr>
                @endif
                <tr class="total-row">
                    <td colspan="3" class="text-right">Total:</td>
                    <td class="text-right">${{ number_format($invoice->total, 2) }}</td>
                </tr>
            </tbody>
        </table>

        @php
            $subsidyTotal = 0;
            foreach($invoice->lineItems as $item) {
                $standard = $item->standard_unit_price ?? $item->unit_price;
                if ($standard > $item->unit_price) {
                    $subsidyTotal += ($standard - $item->unit_price) * $item->quantity;
                }
            }
        @endphp

        @if($subsidyTotal > 0)
        <div style="margin-top: 20px; background-color: #f0fdf4; border: 1px solid #bbf7d0; padding: 15px; border-radius: 5px; color: #166534;">
            <strong>Community Partnership Grant:</strong><br>
            As a proud partner of {{ $invoice->company->name }}, we have subsidized this service by <strong>${{ number_format($subsidyTotal, 2) }}</strong> this month.
        </div>
        @endif
        
        @if($invoice->notes)
        <div style="margin-top: 30px; border-top: 1px solid #eee; padding-top: 10px;">
            <strong>Notes:</strong><br>
            {{ $invoice->notes }}
        </div>
        @endif
    </div>
</body>
</html>