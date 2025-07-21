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
            <td>{{ \Carbon\Carbon::parse($sale->date)->format('Y-m-d') }}</td>
            <td>{{ $sale->client->name }}</td>
            <td>{{ $sale->subtotal }}</td>
            <td>{{ $sale->tax }}</td>
            <td>{{ $sale->total }}</td>
        </tr>
    @endforeach
        <tr>
            <td colspan="2"><strong>Totales:</strong></td>
            <td><strong>{{ $sales->sum('subtotal') }}</strong></td>
            <td><strong>{{ $sales->sum('tax') }}</strong></td>
            <td><strong>{{ $sales->sum('total') }}</strong></td>
        </tr>
    </tbody>
</table>