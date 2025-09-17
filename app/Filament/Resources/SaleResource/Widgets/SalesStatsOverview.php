<?php

namespace App\Filament\Resources\SaleResource\Widgets;

use App\Filament\Resources\SaleResource\Pages\ListSales;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use App\Models\Sale;
use Filament\Widgets\Concerns\InteractsWithPageTable; // <<< 1. AÑADE ESTA LÍNEA

class SalesStatsOverview extends BaseWidget
{
    use InteractsWithPageTable; // <<< 2. AÑADE ESTA LÍNEA

    protected static bool $isLazy = false;

    // Esta función nos permite acceder a la consulta de la tabla de la página
    protected function getTablePage(): string
    {
        return ListSales::class;
    }

    protected function getStats(): array
    {
        // <<< 3. CAMBIO CLAVE AQUÍ >>>
        // Usamos el método que nos da el Trait para obtener la consulta con los filtros
        $filteredQuery = $this->getPageTableQuery();

        // El resto de la lógica no cambia...
        $cashSalesQuery = (clone $filteredQuery)->where('is_cash', true);
        $creditSalesQuery = (clone $filteredQuery)->where('is_cash', false);
        $paidSalesQuery = (clone $filteredQuery)->where('status', 'Pagada');
        $pendingSalesQuery = (clone $filteredQuery)->where('status', 'Pendiente');

        return [
            Stat::make('Ventas de Contado', '$' . number_format($cashSalesQuery->sum('total'), 0))
                ->description($cashSalesQuery->count() . ' ventas')
                ->color('success'),
            Stat::make('Ventas a Crédito', '$' . number_format($creditSalesQuery->sum('total'), 0))
                ->description($creditSalesQuery->count() . ' ventas')
                ->color('warning'),
            Stat::make('Total Pagado', '$' . number_format($paidSalesQuery->sum('total'), 0))
                ->description($paidSalesQuery->count() . ' ventas pagadas'),
            Stat::make('Total Pendiente', '$' . number_format($pendingSalesQuery->sum('pending_amount'), 0))
                ->description($pendingSalesQuery->count() . ' ventas pendientes'),
        ];
    }
}