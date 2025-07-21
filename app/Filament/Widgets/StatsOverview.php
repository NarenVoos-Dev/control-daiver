<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use App\Models\Sale;
use App\Models\Product;
use App\Models\Client;
use Carbon\Carbon;

class StatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        // Obtenemos las ventas de hoy para el negocio actual
        $salesToday = Sale::where('business_id', auth()->user()->business_id)
                           ->whereDate('created_at', Carbon::today())
                           ->sum('total');

        // Contamos el total de productos del negocio
        $totalProducts = Product::where('business_id', auth()->user()->business_id)->count();
        
        // Contamos el total de clientes del negocio
        $totalClients = Client::where('business_id', auth()->user()->business_id)->count();

        return [
            Stat::make('Ventas de Hoy', number_format($salesToday, 2) . ' COP')
                ->description('Total facturado hoy')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('success'),
            Stat::make('Total de Productos', $totalProducts)
                ->description('Productos registrados en inventario')
                ->descriptionIcon('heroicon-m-archive-box')
                ->color('info'),
            Stat::make('Total de Clientes', $totalClients)
                ->description('Clientes registrados en el sistema')
                ->descriptionIcon('heroicon-m-user-group')
                ->color('warning'),
        ];
    }
}