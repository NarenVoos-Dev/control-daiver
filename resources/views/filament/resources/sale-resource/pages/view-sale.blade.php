<x-filament-panels::page>
    <div class="space-y-6">
        {{-- Sección de Información del Cliente y la Venta --}}
        <div class="p-6 bg-white rounded-xl shadow-sm dark:bg-gray-800">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Detalles de la Venta #{{ $record->id }}</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Cliente</p>
                    <p class="text-base font-medium text-gray-900 dark:text-white">{{ $record->client->name }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Fecha de la Venta</p>
                    <p class="text-base font-medium text-gray-900 dark:text-white">{{ \Carbon\Carbon::parse($record->date)->format('d/m/Y') }}</p>
                </div>
            </div>
        </div>

        {{-- Sección de Items de la Venta --}}
        <div class="overflow-x-auto rounded-lg border border-gray-200 dark:border-gray-700">
            <table class="w-full min-w-full divide-y-2 divide-gray-200 bg-white text-sm dark:divide-gray-700 dark:bg-gray-900">
                <thead class="text-left">
                    <tr>
                        <th class="px-4 py-2 font-medium text-gray-900 dark:text-white">Producto</th>
                        <th class="px-4 py-2 font-medium text-gray-900 dark:text-white">Cantidad</th>
                        <th class="px-4 py-2 font-medium text-gray-900 dark:text-white">Unidad</th>
                        <th class="px-4 py-2 font-medium text-gray-900 dark:text-white">Precio Unitario</th>
                        <th class="px-4 py-2 font-medium text-gray-900 dark:text-white">Total Item</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    @foreach($record->items as $item)
                        <tr>
                            <td class="px-4 py-2 text-gray-700 dark:text-gray-200">{{ $item->product->name }}</td>
                            <td class="px-4 py-2 text-gray-700 dark:text-gray-200">{{ $item->quantity }}</td>
                            <td class="px-4 py-2 text-gray-700 dark:text-gray-200">{{ $item->unitOfMeasure->name }}</td>
                            <td class="px-4 py-2 text-gray-700 dark:text-gray-200">${{ number_format($item->price, 2) }}</td>
                            <td class="px-4 py-2 text-gray-700 dark:text-gray-200">${{ number_format($item->price * $item->quantity, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Sección de Totales --}}
        <div class="p-6 bg-white rounded-xl shadow-sm dark:bg-gray-800 flex justify-end">
            <div class="w-full md:w-1/3 space-y-2">
                <div class="flex justify-between">
                    <span class="text-gray-600 dark:text-gray-300">Subtotal:</span>
                    <span class="font-medium text-gray-900 dark:text-white">${{ number_format($record->subtotal, 2) }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600 dark:text-gray-300">IVA:</span>
                    <span class="font-medium text-gray-900 dark:text-white">${{ number_format($record->tax, 2) }}</span>
                </div>
                <div class="flex justify-between border-t border-gray-200 dark:border-gray-700 pt-2 mt-2">
                    <span class="text-lg font-bold text-gray-900 dark:text-white">Total General:</span>
                    <span class="text-lg font-bold text-gray-900 dark:text-white">${{ number_format($record->total, 2) }}</span>
                </div>
            </div>
        </div>
    </div>
</x-filament-panels::page>