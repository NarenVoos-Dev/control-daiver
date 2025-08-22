@extends('layouts.pos')

@section('title', 'Cuentas por Cobrar')
@section('page-title', 'Cuentas por Cobrar')

@section('content')
<div class="bg-white rounded-xl shadow-lg overflow-hidden">
    <div class="bg-gradient-to-r from-blue-800 to-indigo-900 px-6 py-4">
        <h1 class="text-2xl font-bold text-white">Cuentas por Cobrar</h1>
        <p class="text-blue-200 text-sm mt-1">Gestión de clientes y facturas pendientes</p>
    </div>
    
    <div class="p-6">
        <div class="mb-4 flex flex-col md:flex-row justify-between items-center">
            <div class="flex items-center space-x-3 mb-4 md:mb-0">
                <div class="bg-blue-100 p-2 rounded-lg">
                    <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                </div>
                <h2 class="text-lg font-semibold text-gray-700">Clientes con Saldo Pendiente</h2>
            </div>
            <form action="{{ route('pos.accounts.receivable') }}" method="GET" class="flex items-center space-x-2">
                <div class="relative">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Buscar cliente por nombre o NIT..." class="w-full md:w-72 px-3 py-2 pl-9 text-sm border border-gray-300 rounded-lg">
                    <svg class="absolute left-3 top-1/2 transform -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                </div>
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-sm">Buscar</button>
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b-2 border-gray-200">
                        <th class="text-left py-3 px-4 font-semibold text-gray-600">Cliente</th>
                        <th class="text-left py-3 px-4 font-semibold text-gray-600">Valor Pendiente</th>
                        <th class="text-center py-3 px-4 font-semibold text-gray-600">Estado</th>
                        <th class="text-center py-3 px-4 font-semibold text-gray-600">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($clientsWithDebt as $client)
                    <tr class="border-b border-gray-100 hover:bg-gray-50 transition-colors">
                        <td class="py-3 px-4">
                            <div class="flex items-center space-x-3">
                                <div class="w-9 h-9 bg-blue-100 rounded-full flex items-center justify-center text-blue-600 font-semibold text-xs">
                                    {{ strtoupper(substr($client->name, 0, 2)) }}
                                </div>
                                <div>
                                    <div class="font-medium text-gray-800">{{ $client->name }}</div>
                                    <div class="text-xs text-gray-500">NIT: {{ $client->document }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="py-3 px-4"><span class="text-base font-semibold text-red-600">${{ number_format($client->sales_sum_pending_amount, 0) }}</span></td>
                        <td class="py-3 px-4 text-center">
                            <span class="inline-flex px-2.5 py-1 text-xs font-medium rounded-full bg-red-100 text-red-800">Pendiente</span>
                        </td>
                        <td class="py-3 px-4 text-center">
                            <a href="{{ route('pos.accounts.client.statement', $client) }}" class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-1.5 rounded-md text-xs font-medium transition-colors">
                                Ver Facturas
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="4" class="py-10 text-center text-gray-500">No se encontraron clientes con deudas.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-6 bg-gray-50 rounded-lg p-4">
            <div class="flex justify-between items-center">
                <div class="text-gray-600 text-sm font-medium">Total por cobrar:</div>
                <div class="text-xl font-bold text-red-600">${{ number_format($totalReceivable, 0) }}</div>
            </div>
        </div>
        
        <div class="mt-4">
            {{ $clientsWithDebt->links() }}
        </div>
    </div>
</div>
@endsection