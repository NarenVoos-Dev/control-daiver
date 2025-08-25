@forelse($pendingSales as $sale)
<tr class="border-b border-gray-100 hover:bg-gray-50 transition-colors">
    <td class="py-3 px-4 font-medium text-gray-900">#{{ $sale->id }}</td>
    <td class="py-3 px-4 text-gray-600">{{ $sale->created_at->format('d/m/Y H:m:s') }}</td>
    <td class="py-3 px-4 text-center font-medium">${{ number_format($sale->total, 0) }}</td>
    <td class="py-3 px-4 text-center font-semibold text-red-600">${{ number_format($sale->pending_amount, 0) }}</td>
    <td class="py-3 px-4 text-center font-medium">{{ round(\Carbon\Carbon::parse($sale->date)->diffInDays(now())) }}</td>
    <td class="py-3 px-4 text-center">
        <button onclick="openPaymentModal({{ $sale->client_id }}, {{ $sale->id }}, {{ $sale->pending_amount }})" class="bg-green-500 hover:bg-green-600 text-white px-3 py-1 rounded text-xs font-medium transition-colors">
            Abonar
        </button>
    </td>
</tr>
@empty
<tr><td colspan="6" class="py-10 text-center text-gray-500">No se encontraron facturas pendientes.</td></tr>
@endforelse



