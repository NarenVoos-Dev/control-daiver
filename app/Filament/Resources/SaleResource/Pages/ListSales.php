<?php

namespace App\Filament\Resources\SaleResource\Pages;

use App\Filament\Resources\SaleResource;
use App\Filament\Resources\SaleResource\Widgets\SalesStatsOverview;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListSales extends ListRecords
{
    protected static string $resource = SaleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
            ->icon('heroicon-o-plus-circle'), // Puedes usar cualquier ícono de Heroicons,
        ];
    }
    protected function getHeaderWidgets(): array
    {
        return [
            SalesStatsOverview::class,
        ];
    }
}
