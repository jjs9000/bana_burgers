<!DOCTYPE html>
<html lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Invoice #{{ $order->display_id }}</title>
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
            color: #333;
            line-height: 1.5;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 1px solid #ddd;
            padding-bottom: 20px;
        }
        .invoice-title {
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        .store-name {
            font-size: 28px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        .info-section {
            margin-bottom: 20px;
        }
        .info-row {
            display: flex;
            margin-bottom: 10px;
        }
        .info-label {
            font-weight: bold;
            width: 150px;
        }
        .table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .table th {
            background-color: #f3f3f3;
            text-align: left;
            padding: 10px;
            font-weight: bold;
            border-bottom: 1px solid #ddd;
        }
        .table td {
            padding: 10px;
            border-bottom: 1px solid #ddd;
        }
        .table tfoot td {
            font-weight: bold;
        }
        .notes {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .footer {
            margin-top: 40px;
            text-align: center;
            font-size: 12px;
            color: #777;
        }
        .half-width {
            width: 48%;
            float: left;
        }
        .right {
            float: right;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="store-name">BANA</div>
            <div>Delicious Burgers & More</div>
        </div>
        
        <div class="invoice-title">INVOICE #{{ $order->display_id }}</div>
        
        <div style="overflow: hidden; margin-bottom: 30px;">
            <div class="half-width">
                <div class="info-section">
                    <div><strong>Date:</strong> {{ $order->created_at->format('M d, Y') }}</div>
                    <div><strong>Time:</strong> {{ $order->created_at->format('h:i A') }}</div>
                    <div><strong>Status:</strong> {{ ucfirst($order->status) }}</div>
                    @if($order->notes)
                        <div><strong>Notes:</strong> {{ $order->notes }}</div>
                    @endif
                </div>
            </div>
            
            <div class="half-width right">
                <div class="info-section">
                    <div><strong>Customer:</strong> {{ $order->customer_name ?? $order->user->name ?? 'Guest' }}</div>
                    @if($order->customer_phone)
                        <div><strong>Phone:</strong> {{ $order->customer_phone }}</div>
                    @endif
                    @if($order->delivery_address)
                        <div><strong>Delivery Address:</strong> {{ $order->delivery_address }}</div>
                    @endif
                    @if($order->delivery_time)
                        <div><strong>Delivery Time:</strong> {{ $order->delivery_time->format('M d, Y h:i A') }}</div>
                    @endif
                </div>
            </div>
        </div>
        
        <table class="table">
            <thead>
                <tr>
                    <th>Item</th>
                    <th>Quantity</th>
                    <th>Unit Price</th>
                    <th class="text-right">Amount</th>
                </tr>
            </thead>
            <tbody>
                @foreach($order->items as $item)
                    <tr>
                        <td>
                            <div>{{ $item->product->name }}</div>
                            @if($item->variation)
                                <div style="font-size: 12px; color: #666;">{{ $item->variation->name }}</div>
                            @endif
                            
                            @if($item->options && count($item->options) > 0)
                                <div style="font-size: 12px; color: #666;">
                                    @foreach($item->options as $option)
                                        {{ ucfirst($option['type']) }}: {{ $option['name'] }}<br>
                                    @endforeach
                                </div>
                            @endif
                            
                            @if($item->notes)
                                <div style="font-size: 12px; font-style: italic; color: #666;">
                                    Note: {{ $item->notes }}
                                </div>
                            @endif
                        </td>
                        <td>{{ $item->quantity }}</td>
                        <td>RM{{ number_format($item->unit_price, 2) }}</td>
                        <td class="text-right">RM{{ number_format($item->subtotal, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="3" class="text-right">Total:</td>
                    <td class="text-right">RM{{ number_format($order->total, 2) }}</td>
                </tr>
            </tfoot>
        </table>
        
        <div class="notes">
            <p>Thank you for your order!</p>
        </div>
        
        <div class="footer">
            <p>BANA Burger Store</p>
            <p>123 Burger Street, Food District, City</p>
            <p>Phone: (123) 456-7890 | Email: info@banaburger.com</p>
        </div>
    </div>
</body>
</html> 