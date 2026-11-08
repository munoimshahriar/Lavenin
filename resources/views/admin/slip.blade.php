<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="robots" content="noindex">
    <title>Packing slip {{ $order->number }}</title>
    <style nonce="{{ Vite::cspNonce() }}">
        body { font-family: 'Hind Siliguri', Arial, sans-serif; color: #30363F; max-width: 720px; margin: 24px auto; padding: 0 16px; }
        h1 { font-size: 22px; margin: 0; }
        table { width: 100%; border-collapse: collapse; margin-top: 16px; }
        th, td { border-bottom: 1px solid #ddd; padding: 8px 4px; text-align: left; font-size: 14px; }
        td.r, th.r { text-align: right; }
        .grid { display: flex; justify-content: space-between; gap: 24px; margin-top: 16px; font-size: 14px; }
        .box { border: 2px dashed #76A9AD; border-radius: 12px; padding: 12px 16px; }
        .muted { color: #777; }
        .total { font-size: 18px; font-weight: bold; }
        @media print { .noprint { display: none; } body { margin: 0; } }
    </style>
</head>
<body>
    <button class="noprint" onclick="window.print()">Print</button>
    <div class="grid">
        <div>
            <h1>Lavenin</h1>
            <div class="muted">lavenin.com</div>
        </div>
        <div style="text-align:right">
            <div><strong>{{ $order->number }}</strong></div>
            <div class="muted">{{ $order->created_at->format('d M Y, h:i A') }}</div>
        </div>
    </div>

    <div class="box" style="margin-top:16px">
        <div><strong>{{ $order->name }}</strong> · {{ $order->phone }}</div>
        <div>{{ $order->address }}</div>
        <div>{{ $order->city }}, {{ $order->district }}</div>
        @if ($order->note)<div class="muted">Note: {{ $order->note }}</div>@endif
    </div>

    <table>
        <thead><tr><th>Item</th><th class="r">Qty</th><th class="r">Price</th><th class="r">Total</th></tr></thead>
        <tbody>
        @foreach ($order->items as $item)
            <tr><td>{{ $item->name }}</td><td class="r">{{ $item->quantity }}</td><td class="r">৳{{ number_format($item->price) }}</td><td class="r">৳{{ number_format($item->lineTotal()) }}</td></tr>
        @endforeach
        </tbody>
    </table>

    <table>
        <tr><td>Subtotal</td><td class="r">৳{{ number_format($order->subtotal) }}</td></tr>
        @if ($order->discount)<tr><td>Discount</td><td class="r">− ৳{{ number_format($order->discount) }}</td></tr>@endif
        <tr><td>Delivery</td><td class="r">৳{{ number_format($order->shipping) }}</td></tr>
        <tr><td>Total</td><td class="r">৳{{ number_format($order->total) }}</td></tr>
        <tr><td class="total">Collect on delivery</td>
            <td class="r total">৳{{ number_format($order->payment_status->value === 'paid' ? 0 : $order->total) }}</td></tr>
        <tr><td class="muted">Payment</td><td class="r muted">{{ $order->payment_method->getLabel() }} · {{ $order->payment_status->getLabel() }}</td></tr>
    </table>
</body>
</html>
