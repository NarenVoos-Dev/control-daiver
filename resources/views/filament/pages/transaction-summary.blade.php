<x-filament-panels::page>

   {{-- Renderiza el formulario de filtros --}}
    <form wire:submit="filter" class="mt-6">
        {{ $this->form }}

        <x-filament::button type="submit" class="mt-10">
            Aplicar Filtros
        </x-filament::button>
    </form>

     {{-- Esta línea mágica renderiza los widgets registrados en getHeaderWidgets() --}}
    <x-filament-widgets::widgets :widgets="$this->getHeaderWidgets()" />

</x-filament-panels::page>