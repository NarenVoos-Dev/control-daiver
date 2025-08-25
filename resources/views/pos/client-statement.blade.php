@extends('layouts.pos')

@section('title', 'Estado de Cuenta - ' . $client->name)
@section('page-title', 'Estado de Cuenta')

@section('content')
<div class="bg-white rounded-2xl shadow-xl overflow-hidden">
    <div class="bg-gradient-to-r from-indigo-600 to-purple-600 px-8 py-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold text-white">Facturas - {{ $client->name }}</h1>
                <p class="text-indigo-100 mt-2">Detalle de facturas pendientes</p>
            </div>
            <a href="{{ route('pos.accounts.receivable') }}" class="bg-white bg-opacity-20 hover:bg-opacity-30 text-white px-4 py-2 rounded-lg transition-colors flex items-center space-x-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                <span>Volver</span>
            </a>
        </div>
    </div>
    
    <div class="p-8">
        <div class="mb-6 flex flex-col md:flex-row justify-between items-center">
            <form action="{{ route('pos.accounts.client.statement', $client) }}" method="GET" class="flex items-center space-x-2 w-full md:w-auto mb-4 md:mb-0">
                <div class="relative flex-grow">
                    {{-- CAMBIO: Se usa el helper request() --}}
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Buscar por N° de factura..." class="w-full md:w-72 px-3 py-2 pl-9 text-sm border border-gray-300 rounded-lg">
                    <svg class="absolute left-3 top-1/2 transform -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                </div>
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-sm">Buscar</button>
            </form>
            <button onclick="openPaymentModal({{ $client->id }})" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors flex items-center space-x-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
                <span>Abono Masivo</span>
            </button>
        </div>
        <!-- Stats Cards -->
        <div class="mb-6 grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="bg-blue-50 p-6 rounded-lg">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-blue-600 font-medium">Total Facturas</p>
                        <p class="text-2xl font-bold text-blue-800">{{ $stats['total_invoices'] }}</p>
                    </div>
                    <div class="bg-blue-100 p-3 rounded-full">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                    </div>
                </div>
            </div>
            <div class="bg-red-50 p-6 rounded-lg">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-red-600 font-medium">Saldo Total</p>
                        <p class="text-2xl font-bold text-red-800">${{ number_format($stats['total_debt'], 0) }}</p>
                    </div>
                    <div class="bg-red-100 p-3 rounded-full">
                        <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path></svg>
                    </div>
                </div>
            </div>
            <div class="bg-green-50 p-6 rounded-lg">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-green-600 font-medium">Días Promedio</p>
                        <p class="text-2xl font-bold text-green-800">{{ number_format($stats['average_days'], 0) }}</p>
                    </div>
                    <div class="bg-green-100 p-3 rounded-full">
                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                    </div>
                </div>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b-2 border-gray-200">
                        <th class="text-left py-3 px-4 font-semibold text-gray-600">Factura</th>
                        <th class="text-left py-3 px-4 font-semibold text-gray-600">Fecha</th>
                        <th class="text-center py-3 px-4 font-semibold text-gray-600">Valor factura</th>
                        <th class="text-center py-3 px-4 font-semibold text-gray-600">Saldo pendiente</th>
                        <th class="text-center py-3 px-4 font-semibold text-gray-600">Días</th>
                        <th class="text-center py-3 px-4 font-semibold text-gray-600">Acciones</th>
                    </tr>
                </thead>
                <tbody id="sales-tbody">
                    @include('partials.sales-table-rows', ['pendingSales' => $pendingSales])
                    <!--@forelse($pendingSales as $sale)
                    <tr class="border-b border-gray-100 hover:bg-gray-50 transition-colors">
                        <td class="py-3 px-4 font-medium text-gray-900">#{{ $sale->id }}</td>
                        <td class="py-3 px-4 text-gray-600">{{ $sale->date->format('d/m/Y') }}</td>
                        <td class="py-3 px-4 text-right font-medium">${{ number_format($sale->total, 0) }}</td>
                        <td class="py-3 px-4 text-right font-semibold text-red-600">${{ number_format($sale->pending_amount, 0) }}</td>
                        <td class="py-3 px-4 text-center font-medium">{{ \Carbon\Carbon::parse($sale->date)->diffInDays(now()) }}</td>
                        <td class="py-3 px-4 text-center">
                            <button onclick="openPaymentModal({{ $client->id }}, {{ $sale->id }}, {{ $sale->pending_amount }})" class="bg-green-500 hover:bg-green-600 text-white px-3 py-1 rounded text-xs font-medium transition-colors">
                                Abonar
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="py-10 text-center text-gray-500">No se encontraron facturas pendientes.</td></tr>
                    @endforelse-->
                </tbody>
            </table>
        </div>
        <div class="mt-6">
            {{ $pendingSales->links() }}
        </div>
    </div>
</div>


{{-- Modal para Asignar Abono --}}
<div id="abonoModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
    <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full mx-4">
        <div class="bg-gradient-to-r from-green-600 to-emerald-600 px-6 py-4 rounded-t-2xl">
            <h3 class="text-xl font-bold text-white">Asignar Abono</h3>
        </div>
        <div class="p-6">
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Factura:</label>
                <p class="text-lg font-semibold text-gray-900" id="facturaAbono">-</p>
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Saldo Actual:</label>
                <p class="text-lg font-semibold text-red-600" id="saldoActual">$0</p>
            </div>
            <div class="mb-6">
                <label for="montoAbono" class="block text-sm font-medium text-gray-700 mb-2">Monto del Abono:</label>
                <input type="number" id="montoAbono" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent" placeholder="Ingrese el monto">
            </div>
            <div class="flex space-x-3">
                <button onclick="cerrarModalAbono()" class="flex-1 bg-gray-300 hover:bg-gray-400 text-gray-700 py-3 px-4 rounded-lg font-medium transition-colors">Cancelar</button>
                <button onclick="procesarAbono()" class="flex-1 bg-green-600 hover:bg-green-700 text-white py-3 px-4 rounded-lg font-medium transition-colors">Aplicar Abono</button>
            </div>
        </div>
    </div>
</div>

{{-- CAMBIO: Se añade el overlay de carga --}}
<div id="loading-overlay" class="fixed inset-0 z-50 flex-col items-center justify-center hidden bg-gray-900 bg-opacity-75">
    <div class="w-16 h-16 border-4 border-t-transparent border-white rounded-full animate-spin"></div>
    <p class="mt-4 text-lg font-semibold text-white">Procesando abono...</p>
</div>

{{-- Contenedor para Alertas --}}
<div id="alert-container" class="fixed top-5 right-5 z-50 space-y-4 w-full max-w-sm"></div>
@endsection

@push('scripts')
<script>
    let currentClientId, currentSaleId, currentSaleDebt;
    const apiToken = $('meta[name="api-token"]').attr('content');
    const csrfToken = $('meta[name="csrf-token"]').attr('content');

    $(document).ready(function() {
        const debounce = (func, delay) => {
            let timeout;
            return function(...args) {
                clearTimeout(timeout);
                timeout = setTimeout(() => func.apply(this, args), delay);
            };
        };

        const performSearch = () => {
            const searchTerm = $('#search-input').val();
            const url = `/api/pos/clients/{{ $client->id }}/search-sales?search=${encodeURIComponent(searchTerm)}`;

            $.ajax({
                url: url,
                method: 'GET',
                headers: { 'Authorization': `Bearer {{ $apiToken }}`, 'Accept': 'application/json' },
                success: function(response) {
                    $('#sales-tbody').html(response.table_html);
                    $('#sales-pagination').html(response.pagination_html);
                },
                error: function(xhr) {
                    console.error('Error en la búsqueda:', xhr);
                }
            });
        };

        $('#search-input').on('keyup', debounce(performSearch, 300));
    });

    function openPaymentModal(clientId, saleId = null, saleDebt = null) {
        currentClientId = clientId;
        currentSaleId = saleId;
        currentSaleDebt = saleDebt;

        if (saleId) {
            $('#facturaAbono').text(`Factura #${saleId}`);
            $('#saldoActual').text(`$${saleDebt.toLocaleString()}`);
        } else {
            $('#facturaAbono').text('Abono a Deuda Total');
            $('#saldoActual').text("${{ number_format($stats['total_debt'], 0, ',', '.') }}");
        }
        $('#montoAbono').val('');
        $('#abonoModal').removeClass('hidden').addClass('flex');
    }

    function cerrarModalAbono() {
        $('#abonoModal').addClass('hidden').removeClass('flex');
    }

    function procesarAbono() {
        const amount = parseFloat($('#montoAbono').val());
        if (!amount || amount <= 0) {
            showAlert('Monto Inválido', 'Por favor ingrese un monto válido.', 'error');
            return;
        }

        if (currentSaleId && amount > currentSaleDebt) {
            const surplus = amount - currentSaleDebt;
            if (confirm(`El monto ingresado es mayor a la deuda de esta factura. ¿Desea aplicar el sobrante de $${surplus.toLocaleString()} a la deuda total del cliente?`)) {
                // Si confirma, se envía el pago completo como un abono masivo
                sendPaymentRequest({ client_id: currentClientId, amount: amount });
            } else {
                // Si no, se envía solo el monto exacto de la deuda
                sendPaymentRequest({ client_id: currentClientId, sale_id: currentSaleId, amount: currentSaleDebt });
                showAlert('Vuelto a Entregar', `Se aplicó el pago exacto. Debe devolver un vuelto de $${surplus.toLocaleString()} al cliente.`, 'info');
            }
        } else {
            // Si es un abono masivo o el monto es menor/igual a la deuda específica
            sendPaymentRequest({
                client_id: currentClientId,
                sale_id: currentSaleId, // Será null si es masivo
                amount: amount
            });
        }
    }

    function sendPaymentRequest(data) {
        $('#loading-overlay').removeClass('hidden').addClass('flex');
        
        $.ajax({
            url: '/api/pos/store-payment',
            method: 'POST',
            headers: { 'Authorization': `Bearer ${apiToken}`, 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
            data: data,
            success: function(response) {
                if (response.success) {
                    showAlert('Éxito', response.message, 'success');
                    cerrarModalAbono();
                    setTimeout(() => {
                        window.location.href = "{{ route('pos.accounts.receivable') }}";
                    }, 2000);
                }
            },
            error: function(xhr) {
                const error = xhr.responseJSON;
                showAlert('Error', error?.message || 'Ocurrió un problema.', 'error');
            }
        });
    }

    function showAlert(title, message, type = 'success') { /* ... (tu función de alerta aquí) ... */ }
</script>
@endpush