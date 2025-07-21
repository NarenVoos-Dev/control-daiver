<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use App\Models\Sale;
use Carbon\Carbon;

class SalesChart extends ChartWidget
{
    protected static ?string $heading = 'Ventas de los Últimos 7 Días';
    protected static ?int $sort = 2;

    protected function getData(): array
    {
        $data = $this->getSalesPerDay();
        return ['datasets' => [['label' => 'Ventas', 'data' => array_values($data)]], 'labels' => array_keys($data)];
    }

    protected function getType(): string { return 'line'; }

    private function getSalesPerDay(): array
    {
        $salesData = [];
        $startDate = Carbon::now()->subDays(6);
        $endDate = Carbon::now();

        // CAMBIO: Se usan groupByRaw y orderByRaw para compatibilidad con el modo estricto de MySQL.
        $sales = Sale::where('business_id', auth()->user()->business_id)
                     ->whereBetween('created_at', [$startDate, $endDate])
                     ->selectRaw('DATE(created_at) as date, SUM(total) as total')
                     ->groupByRaw('DATE(created_at)')
                     ->orderByRaw('DATE(created_at) asc')
                     ->get()
                     ->keyBy(function ($item) {
                         return Carbon::parse($item->date)->format('Y-m-d');
                     });
        
        for ($date = $startDate->copy(); $date->lte($endDate); $date->addDay()) {
            $dateString = $date->format('Y-m-d');
            $salesData[$date->format('d M')] = $sales->has($dateString) ? $sales[$dateString]->total : 0;
        }
        return $salesData;
    }
}