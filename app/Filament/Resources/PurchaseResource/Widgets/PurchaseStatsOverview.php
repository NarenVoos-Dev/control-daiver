<?php

namespace App\Filament\Resources\PurchaseResource\Widgets;

use App\Filament\Resources\PurchaseResource\Pages\ListPurchases;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Filament\Widgets\Concerns\InteractsWithPageTable;

class PurchaseStatsOverview extends BaseWidget
{
    use InteractsWithPageTable;

    protected static bool $isLazy = false;

    protected function getTablePage(): string
    {
        return ListPurchases::class;
    }

    protected function getStats(): array
    {
        // Obtenemos la consulta de la tabla con los filtros ya aplicados
        $filteredQuery = $this->getPageTableQuery();

        // Clonamos la consulta para cada cálculo
        $totalQuery = (clone $filteredQuery);
        $pendingQuery = (clone $filteredQuery)->where('status', 'Pendiente');
        $paidQuery = (clone $filteredQuery)->where('status', 'Pagada');

        return [
            Stat::make('Total en Compras', '$' . number_format($totalQuery->sum('total'), 0))
                ->description($totalQuery->count() . ' compras realizadas'),
            Stat::make('Total Pendiente por Pagar', '$' . number_format($pendingQuery->sum('total'), 0))
                ->description($pendingQuery->count() . ' compras pendientes')
                ->color('warning'),
            Stat::make('Total Pagado a Proveedores', '$' . number_format($paidQuery->sum('total'), 0))
                ->description($paidQuery->count() . ' compras pagadas')
                ->color('success'),
        ];
    }
}