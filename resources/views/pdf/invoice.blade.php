//Vista para el documento pdf
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Factura de Venta #{{ $sale->id }}</title>
    <style>
        body { font-family: 'Helvetica', sans-serif; font-size: 12px; }
        .container { width: 100%; margin: 0 auto; }
        .header, .footer { width: 100%; position: fixed; }
        .header { top: 0px; }
        .footer { bottom: 0px; font-size: 10px; text-align: center; }
        .content { margin-top: 150px; }
        .company-details, .invoice-details { width: 50%; float: left; }
        .company-details h1 { margin: 0; }
        .invoice-details { text-align: right; }
        .client-details { margin-top: 20px; padding: 10px; border: 1px solid #eee; }
        .items-table { width: 100%; margin-top: 20px; border-collapse: collapse; }
        .items-table th, .items-table td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        .items-table th { background-color: #f2f2f2; }
        .totals { float: right; width: 40%; margin-top: 20px; }
        .totals table { width: 100%; }
        .clearfix::after { content: ""; clear: both; display: table; }
    </style>
</head>
<body>
    <div class="header">
        <div class="container clearfix">
            <div class="company-details">
                <h1>{{ $sale->business->name }}</h1>
                <p>NIT: {{ $sale->business->nit }}</p>
                {{-- <p>Dirección, Teléfono, etc. --}}
            </div>
            <div class="invoice-details">
                <h2>RECIBO DE VENTA</h2>
                <p><strong>Factura #:</strong> {{ $sale->id }}</p>
                <p><strong>Fecha:</strong> {{ \Carbon\Carbon::parse($sale->date)->format('d/m/Y') }}</p>
            </div>
        </div>
    </div>
    
    <div class="content">
        <div class="client-details">
            <h3>Facturar a:</h3>
            <p><strong>Cliente:</strong> {{ $sale->client->name }}</p>
            <p><strong>Documento:</strong> {{ $sale->client->document }}</p>
            <p><strong>Email:</strong> {{ $sale->client->email }}</p>
        </div>

        <table class="items-table">
            <thead>
                <tr>
                    <th>Producto</th>
                    <th>Cantidad</th>
                    <th>Unidad</th>
                    <th>Precio Unit.</th>
                    <th>IVA (%)</th>
                    <th>Total Item</th>
                </tr>
            </thead>
            <tbody>
                @foreach($sale->items as $item)
                <tr>
                    <td>{{ $item->product->name }}</td>
                    <td>{{ $item->quantity }}</td>
                    <td>{{ $item->unitOfMeasure->abbreviation }}</td>
                    <td>{{ number_format($item->price, 2) }}</td>
                    <td>{{ $item->tax_rate }}%</td>
                    <td>{{ number_format($item->quantity * $item->price * (1 + $item->tax_rate / 100), 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <div class="totals clearfix">
            <table>
                <tr>
                    <td>Subtotal:</td>
                    <td style="text-align:right;">{{ number_format($sale->subtotal, 2) }}</td>
                </tr>
                <tr>
                    <td>Total IVA:</td>
                    <td style="text-align:right;">{{ number_format($sale->tax, 2) }}</td>
                </tr>
                <tr>
                    <td><strong>Total General:</strong></td>
                    <td style="text-align:right;"><strong>{{ number_format($sale->total, 2) }}</strong></td>
                </tr>
            </table>
        </div>
    </div>
</body>
</html>