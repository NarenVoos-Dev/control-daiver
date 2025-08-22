<!--Vista para la tirilla de ventas
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
    -->
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Recibo de Venta #{{ $sale->id }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Courier New', moanospace; font-size: 11px; line-height: 1.3; width: 80mm; margin: 0 auto; padding: 5mm; background: white; color: #000; }
        .receipt { width: 100%; max-width: 70mm; }
        .header { text-align: center; border-bottom: 2px solid #000; padding-bottom: 8px; margin-bottom: 10px; }
        .company-name { font-size: 16px; font-weight: bold; text-transform: uppercase; margin-bottom: 3px; letter-spacing: 1px; }
        .company-info { font-size: 10px; margin-bottom: 2px; }
        .receipt-title { font-size: 14px; font-weight: bold; margin: 8px 0; text-align: center; background: #000; color: white; padding: 4px; }
        .invoice-info { margin-bottom: 10px; font-size: 10px; }
        .invoice-info div { display: flex; justify-content: space-between; margin-bottom: 2px; }
        .client-section { border-top: 1px dashed #000; border-bottom: 1px dashed #000; padding: 8px 0; margin: 10px 0; }
        .client-title { font-weight: bold; margin-bottom: 4px; text-transform: uppercase; }
        .client-info { font-size: 10px; margin-bottom: 2px; }
        .items-section { margin: 10px 0; }
        .items-header { border-bottom: 1px solid #000; padding-bottom: 3px; margin-bottom: 5px; font-weight: bold; font-size: 10px; }
        .item-row { margin-bottom: 6px; padding-bottom: 4px; border-bottom: 1px dotted #ccc; }
        .item-name { font-weight: bold; margin-bottom: 2px; }
        .item-details { display: flex; justify-content: space-between; font-size: 10px; margin-bottom: 1px; }
        .item-total { display: flex; justify-content: space-between; font-weight: bold; }
        .totals-section { border-top: 2px solid #000; padding-top: 8px; margin-top: 10px; }
        .total-row { display: flex; justify-content: space-between; margin-bottom: 3px; font-size: 11px; }
        .final-total { border-top: 1px solid #000; padding-top: 5px; margin-top: 5px; font-weight: bold; font-size: 13px; }
        .footer { text-align: center; margin-top: 15px; padding-top: 10px; border-top: 1px dashed #000; font-size: 9px; }
        .thank-you { font-weight: bold; margin-bottom: 5px; }
        .notes-section { margin-top: 15px; padding-top: 10px; border-top: 1px dashed #000; }
    </style>
</head>
<body onload="window.print(); setTimeout(window.close, 0);">
    <div class="receipt">
        <!-- Header -->
        <div class="header">
            <div class="company-name">{{ $sale->business->name }}</div>
            <div class="company-info">NIT: {{ $sale->business->nit }}</div>
            {{-- Puedes añadir más detalles del negocio aquí si los tienes en la base de datos --}}
        </div>
        
        <!-- Receipt Title -->
        <div class="receipt-title">RECIBO DE VENTA</div>
        
        <!-- Invoice Info -->
        <div class="invoice-info">
            <div><span><strong>Factura #:</strong></span><span>{{ $sale->id }}</span></div>
            <div><span><strong>Fecha:</strong></span><span>{{ $sale->created_at->format('d/m/Y') }}</span></div>
            <div><span><strong>Hora:</strong></span><span>{{ $sale->created_at->format('H:i:s') }}</span></div>
            <div><span><strong>Cajero:</strong></span><span>{{ $sale->user->name ?? 'N/A' }}</span></div>
        </div>
        
        <!-- Client Info -->
        <div class="client-section">
            <div class="client-title">Cliente:</div>
            <div class="client-info"><strong>Nombre:</strong> {{ $sale->client->name }}</div>
            <div class="client-info"><strong>Documento:</strong> {{ $sale->client->document ?? 'N/A' }}</div>
            <div class="client-info"><strong>Email:</strong> {{ $sale->client->email ?? 'N/A' }}</div>
        </div>
        
        <!-- Items -->
        <div class="items-section">
            <div class="items-header">PRODUCTOS</div>
            @foreach($sale->items as $item)
                <div class="item-row">
                    <div class="item-name">{{ $item->product->name }}</div>
                    <div class="item-details">
                        <span>Cant: {{ $item->quantity }} x ${{ number_format($item->price, 0) }}</span>
                        <span>IVA: {{ $item->tax_rate }}%</span>
                    </div>
                    <div class="item-total">
                        <span>Total:</span>
                        <span>${{ number_format($item->quantity * $item->price * (1 + $item->tax_rate / 100), 0) }}</span>
                    </div>
                </div>
            @endforeach
        </div>
        
        <!-- Totals -->
        <div class="totals-section">
            <div class="total-row">
                <span>Subtotal:</span>
                <span>${{ number_format($sale->subtotal, 0) }}</span>
            </div>
            <div class="total-row">
                <span>Total IVA:</span>
                <span>${{ number_format($sale->tax, 0) }}</span>
            </div>
            <div class="total-row final-total">
                <span>TOTAL A PAGAR:</span>
                <span>${{ number_format($sale->total, 0) }}</span>
            </div>
        </div>

        <!-- Notes Section -->
        @if($sale->notes)
            <div class="notes-section">
                <div class="client-title">Notas:</div>
                <div class="client-info">{{ $sale->notes }}</div>
            </div>
        @endif
        
        <!-- Footer -->
        <div class="footer">
            <div class="thank-you">¡GRACIAS POR SU COMPRA!</div>
            <div>Software POS v1.0</div>
        </div>
    </div>
</body>
</html>
