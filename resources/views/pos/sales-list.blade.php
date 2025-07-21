@extends('layouts.pos')

@section('title', 'Listado de Ventas')
@section('page-title', 'Listado de Ventas')

@section('content')
<div class="p-4 bg-white rounded-lg shadow">
    <div class="overflow-x-auto">
        <table class="min-w-full text-sm bg-white divide-y-2 divide-gray-200">
            <thead class="text-left">
                <tr>
                    <th class="px-4 py-2 font-medium text-gray-900">N° Venta</th>
                    <th class="px-4 py-2 font-medium text-gray-900">Cliente</th>
                    <th class="px-4 py-2 font-medium text-gray-900">Total</th>
                    <th class="px-4 py-2 font-medium text-gray-900">Fecha</th>
                    <th class="px-4 py-2 font-medium text-gray-900">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($sales as $sale)
                <tr>
                    <td class="px-4 py-2 font-medium text-gray-900">{{ $sale->id }}</td>
                    <td class="px-4 py-2 text-gray-700">{{ $sale->client->name }}</td>
                    <td class="px-4 py-2 text-gray-700">${{ number_format($sale->total, 0) }}</td>
                    <td class="px-4 py-2 text-gray-700">{{ $sale->created_at->format('d/m/Y h:i A') }}</td>
                    <td class="px-4 py-2">
                        <a href="{{ route('sales.receipt.print', $sale) }}" target="_blank" class="text-blue-600 hover:underline">Imprimir</a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="py-4 text-center text-gray-500">No se han realizado ventas.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">
        {{ $sales->links() }}
    </div>
</div>
@endsection