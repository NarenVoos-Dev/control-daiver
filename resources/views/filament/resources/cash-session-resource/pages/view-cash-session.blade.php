<x-filament-panels::page>
    <div class="space-y-6">
        {{-- Resumen de la Caja --}}
        <div class="p-6 bg-white shadow-sm rounded-xl dark:bg-gray-800">
            <h2 class="mb-4 text-lg font-semibold text-gray-900 dark:text-white">Resumen de Caja #{{ $record->id }}</h2>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-center">
                <div><p class="text-sm text-gray-500">Estado</p><span class="text-base font-medium px-2 py-0.5 rounded-full {{ $record->status === 'Cerrada' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">{{ $record->status }}</span></div>
                <div><p class="text-sm text-gray-500">Abierta por</p><p class="font-medium">{{ $record->userOpened->name }}</p></div>
                <div><p class="text-sm text-gray-500">Fecha Apertura</p><p class="font-medium">{{ $record->opened_at->format('d/m/Y H:i') }}</p></div>
                <div><p class="text-sm text-gray-500">Fecha Cierre</p><p class="font-medium">{{ $record->closed_at?->format('d/m/Y H:i') ?? 'N/A' }}</p></div>
            </div>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-center mt-6 pt-4 border-t">
                <div><p class="text-sm text-gray-500">Monto Apertura</p><p class="font-medium text-lg">${{ number_format($record->opening_balance, 0) }}</p></div>
                <div><p class="text-sm text-gray-500">Monto Calculado</p><p class="font-medium text-lg">${{ number_format($record->calculated_balance, 0) }}</p></div>
                <div><p class="text-sm text-gray-500">Monto Contado</p><p class="font-medium text-lg">${{ number_format($record->closing_balance, 0) }}</p></div>
                <div><p class="text-sm text-gray-500">Diferencia</p><p class="font-medium text-lg @if($record->difference > 0) text-green-600 @elseif($record->difference < 0) text-red-600 @endif">${{ number_format($record->difference, 0) }}</p></div>
            </div>
        </div>

        {{-- Listado de Transacciones --}}
        <div class="p-6 bg-white shadow-sm rounded-xl dark:bg-gray-800">
            <h3 class="mb-4 text-lg font-semibold text-gray-900 dark:text-white">Transacciones de la Sesión</h3>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="text-left bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th class="px-4 py-2 font-medium">Tipo</th>
                            <th class="px-4 py-2 font-medium">Descripción</th>
                            <th class="px-4 py-2 font-medium text-right">Monto</th>
                            <th class="px-4 py-2 font-medium">Fecha</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($record->transactions as $tx)
                        <tr class="border-b dark:border-gray-700">
                            <td class="px-4 py-2">
                                <span class="{{ $tx->type === 'entrada' ? 'text-green-600' : 'text-red-600' }} font-semibold">
                                    {{ ucfirst($tx->type) }}
                                </span>
                            </td>
                            <td class="px-4 py-2">{{ $tx->description }}</td>
                            <td class="px-4 py-2 text-right font-mono">${{ number_format($tx->amount, 0) }}</td>
                            <td class="px-4 py-2 text-gray-500">{{ $tx->created_at->format('H:i:s') }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-filament-panels::page>