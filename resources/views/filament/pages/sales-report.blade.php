<x-filament-panels::page>
    <form wire:submit.prevent="submit">
        {{ $this->form }}

        <div class="flex mt-4 gap-x-3">
             <x-filament::button 
                type="submit"
                icon="heroicon-o-chart-bar">
                Generar Reporte
            </x-filament::button>
            
            {{-- NUEVOS BOTONES DE EXPORTACIÓN --}}
            <x-filament::button 
            wire:click="exportToExcel" 
            color="success"
            icon="heroicon-o-table-cells">
                Exportar a Excel
            </x-filament::button>
            
            <x-filament::button 
            wire:click="exportToPdf" 
            color="danger"
            icon="heroicon-o-document-arrow-down">
                Exportar a PDF
            </x-filament::button>
        </div>
    </form>
    
    <div class="mt-8">
        @php
            $sales = $this->getSalesData();
            $totalSubtotal = $sales->sum('subtotal');
            $totalTax = $sales->sum('tax');
            $totalGeneral = $sales->sum('total');
        @endphp

        <div class="overflow-x-auto border border-gray-200 rounded-lg dark:border-gray-700">
            <table class="w-full min-w-full text-sm bg-white divide-y-2 divide-gray-200 dark:divide-gray-700 dark:bg-gray-900">
                <thead class="text-left">
                    <tr>
                        <th class="px-4 py-2 font-medium text-gray-900 whitespace-nowrap dark:text-white">Fecha</th>
                        <th class="px-4 py-2 font-medium text-gray-900 whitespace-nowrap dark:text-white">Cliente</th>
                        <th class="px-4 py-2 font-medium text-gray-900 whitespace-nowrap dark:text-white">Subtotal</th>
                        <th class="px-4 py-2 font-medium text-gray-900 whitespace-nowrap dark:text-white">IVA</th>
                        <th class="px-4 py-2 font-medium text-gray-900 whitespace-nowrap dark:text-white">Total</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse ($sales as $sale)
                        <tr>
                            <td class="px-4 py-2 text-gray-700 whitespace-nowrap dark:text-gray-200">{{ \Carbon\Carbon::parse($sale->date)->format('d/m/Y') }}</td>
                            <td class="px-4 py-2 text-gray-700 whitespace-nowrap dark:text-gray-200">{{ $sale->client->name }}</td>
                            <td class="px-4 py-2 text-gray-700 whitespace-nowrap dark:text-gray-200">{{ number_format($sale->subtotal, 2) }}</td>
                            <td class="px-4 py-2 text-gray-700 whitespace-nowrap dark:text-gray-200">{{ number_format($sale->tax, 2) }}</td>
                            <td class="px-4 py-2 font-bold text-gray-900 whitespace-nowrap dark:text-white">{{ number_format($sale->total, 2) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-4 text-center">No se encontraron ventas para los filtros seleccionados.</td>
                        </tr>
                    @endforelse
                </tbody>
                <tfoot class="bg-gray-50 dark:bg-gray-800">
                    <tr>
                        <th colspan="2" class="px-4 py-2 font-medium text-right text-gray-900 dark:text-white">Totales:</th>
                        <th class="px-4 py-2 font-medium text-left text-gray-900 dark:text-white">{{ number_format($totalSubtotal, 2) }}</th>
                        <th class="px-4 py-2 font-medium text-left text-gray-900 dark:text-white">{{ number_format($totalTax, 2) }}</th>
                        <th class="px-4 py-2 font-bold text-left text-gray-900 dark:text-white">{{ number_format($totalGeneral, 2) }}</th>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</x-filament-panels::page>