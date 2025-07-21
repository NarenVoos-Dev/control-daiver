<x-filament-panels::page>
    <form wire:submit.prevent="submit">
        {{ $this->form }}

        <div class="mt-4">
            <x-filament::button 
            type="submit"
            icon="heroicon-o-chart-bar">
                Generar Reporte
            </x-filament::button>
        </div>
    </form>
    
    <div class="mt-8">
        @php
            $movements = $this->getStockMovements();
            // Para calcular el saldo, necesitamos saber cuál era el stock ANTES del primer movimiento del rango
            $initialStock = 0;
            if ($this->productId && $movements->isNotEmpty()) {
                $firstMovement = $movements->first();
                $sumOfPreviousMovements = \App\Models\StockMovement::where('product_id', $this->productId)
                    ->where('created_at', '<', $firstMovement->created_at)
                    ->get()
                    ->sum(function($mov) {
                        return $mov->type === 'entrada' ? $mov->quantity : -$mov->quantity;
                    });
                $initialStock = $sumOfPreviousMovements;
            }
            $balance = $initialStock;
        @endphp

        <div class="overflow-x-auto border border-gray-200 rounded-lg dark:border-gray-700">
            <table class="w-full min-w-full text-sm bg-white divide-y-2 divide-gray-200 dark:divide-gray-700 dark:bg-gray-900">
                <thead class="text-left">
                    <tr>
                        <th class="px-4 py-2 font-medium text-gray-900 dark:text-white">Fecha</th>
                        <th class="px-4 py-2 font-medium text-gray-900 dark:text-white">Tipo</th>
                        <th class="px-4 py-2 font-medium text-gray-900 dark:text-white">Origen</th>
                        <th class="px-4 py-2 font-medium text-gray-900 dark:text-white">Entrada</th>
                        <th class="px-4 py-2 font-medium text-gray-900 dark:text-white">Salida</th>
                        <th class="px-4 py-2 font-medium text-gray-900 dark:text-white">Saldo</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    {{-- Fila para el Saldo Inicial --}}
                    @if ($this->productId)
                    <tr>
                        <td colspan="5" class="px-4 py-2 font-bold text-gray-900 dark:text-white">Saldo Inicial</td>
                        <td class="px-4 py-2 font-bold text-gray-900 dark:text-white">{{ number_format($initialStock, 2) }}</td>
                    </tr>
                    @endif

                    @forelse ($movements as $movement)
                        @php
                            $isEntrance = $movement->type === 'entrada';
                            $balance += $isEntrance ? $movement->quantity : -$movement->quantity;

                            $sourceDescription = 'N/A';
                            if ($movement->source) {
                                $sourceModel = class_basename($movement->source_type);
                                $sourceDescription = "{$sourceModel} #{$movement->source_id}";
                            }
                        @endphp
                        <tr>
                            <td class="px-4 py-2 text-gray-700 dark:text-gray-200">{{ \Carbon\Carbon::parse($movement->created_at)->format('d/m/Y H:i') }}</td>
                            <td class="px-4 py-2">
                                <span class="inline-flex items-center justify-center rounded-full px-2.5 py-0.5 {{ $isEntrance ? 'bg-green-100 text-green-800 dark:bg-green-800/50 dark:text-green-300' : 'bg-red-100 text-red-800 dark:bg-red-800/50 dark:text-red-300' }}">
                                    <p class="text-sm whitespace-nowrap">{{ $movement->type }}</p>
                                </span>
                            </td>
                            <td class="px-4 py-2 text-gray-700 dark:text-gray-200">{{ $sourceDescription }}</td>
                            <td class="px-4 py-2 text-green-700 dark:text-green-400">{{ $isEntrance ? number_format($movement->quantity, 2) : '-' }}</td>
                            <td class="px-4 py-2 text-red-700 dark:text-red-400">{{ !$isEntrance ? number_format($movement->quantity, 2) : '-' }}</td>
                            <td class="px-4 py-2 font-bold text-gray-900 dark:text-white">{{ number_format($balance, 2) }}</td>
                        </tr>
                    @empty
                        @if ($this->productId)
                        <tr>
                            <td colspan="6" class="py-4 text-center">No se encontraron movimientos para este producto en el rango de fechas.</td>
                        </tr>
                        @else
                         <tr>
                            <td colspan="6" class="py-4 text-center">Por favor, seleccione un producto para generar el reporte.</td>
                        </tr>
                        @endif
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-filament-panels::page>