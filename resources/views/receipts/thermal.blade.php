//Vista para la tirilla de ventas
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Recibo de Venta #{{ $sale->id }}</title>
    <style>
        body {
            font-family: 'Courier New', monospace;
            font-size: 10pt;
            color: #000;
            width: 280px; /* Ancho típico de impresora térmica */
            margin: 0 auto;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .header { margin-bottom: 10px; }
        .header h1 { font-size: 14pt; margin: 0; }
        .header p { margin: 0; }
        table { width: 100%; border-collapse: collapse; }
        table th, table td { padding: 2px 0; }
        .items-table th { border-bottom: 1px dashed #000; }
        .totals-table td { padding: 1px 0; }
        .footer { margin-top: 15px; border-top: 1px dashed #000; padding-top: 5px;}
        @media print {
            @page { margin: 0; }
            body { margin: 0.5cm; }
        }
    </style>
</head>
<body onload="window.print(); setTimeout(window.close, 0);" >
    <div class="header text-center">
        <h1>{{ $sale->business->name }}</h1>
        <p>NIT: {{ $sale->business->nit }}</p>
        <p>Fecha: {{ \Carbon\Carbon::parse($sale->created_at)->format('d/m/Y H:i') }}</p>
        <p>Venta #: {{ $sale->id }}</p>
    </div>

    <div class="customer-info">
        <p><strong>Cliente:</strong> {{ $sale->client->name }}</p>
        <p><strong>Doc:</strong> {{ $sale->client->document }}</p>
    </div>

    <table class="items-table">
        <thead>
            <tr>
                <th>Cant.</th>
                <th>Producto</th>
                <th class="text-right">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($sale->items as $item)
            <tr>
                <td>{{ $item->quantity }}</td>
                <td>{{ $item->product->name }}</td>
                <td class="text-right">{{ number_format($item->quantity * $item->price, 0) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <table class="totals-table">
        <tr>
            <td><strong>Subtotal:</strong></td>
            <td class="text-right">{{ number_format($sale->subtotal, 0) }}</td>
        </tr>
        <tr>
            <td><strong>IVA:</strong></td>
            <td class="text-right">{{ number_format($sale->tax, 0) }}</td>
        </tr>
        <tr>
            <td><strong>TOTAL:</strong></td>
            <td class="text-right"><strong>{{ number_format($sale->total, 0) }}</strong></td>
        </tr>
    </table>

    <div class="footer text-center">
        <p>¡Gracias por su compra!</p>
    </div>
</body>
</html>
