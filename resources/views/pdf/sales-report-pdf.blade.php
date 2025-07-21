<!DOCTYPE html>
<html>
<head>
    <title>Reporte de Ventas</title>
    <style>
        body { font-family: 'Helvetica', sans-serif; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        h1 { text-align: center; }
        .footer { position: fixed; bottom: 0; width: 100%; text-align: center; font-size: 10px; }
    </style>
</head>
<body>
    <h1>Reporte de Ventas</h1>
    <p><strong>Rango de Fechas:</strong> {{ \Carbon\Carbon::parse($sales->first()->date ?? '')->format('d/m/Y') }} - {{ \Carbon\Carbon::parse($sales->last()->date ?? '')->format('d/m/Y') }}</p>
    
    <table>
        <thead>
            <tr>
                <th>Fecha</th>
                <th>Cliente</th>
                <th>Subtotal</th>
                <th>IVA</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
        @foreach($sales as $sale)
            <tr>
                <td>{{ \Carbon\Carbon::parse($sale->date)->format('d/m/Y') }}</td>
                <td>{{ $sale->client->name }}</td>
                <td>{{ number_format($sale->subtotal, 2) }}</td>
                <td>{{ number_format($sale->tax, 2) }}</td>
                <td>{{ number_format($sale->total, 2) }}</td>
            </tr>
        @endforeach
        </tbody>
        <tfoot>
            <tr>
                <th colspan="2" style="text-align:right;">Totales:</th>
                <th>{{ number_format($sales->sum('subtotal'), 2) }}</th>
                <th>{{ number_format($sales->sum('tax'), 2) }}</th>
                <th>{{ number_format($sales->sum('total'), 2) }}</th>
            </tr>
        </tfoot>
    </table>
    <div class="footer">
        Generado el {{ \Carbon\Carbon::now()->format('d/m/Y H:i') }}
    </div>
</body>
</html>