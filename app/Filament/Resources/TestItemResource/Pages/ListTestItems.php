<?php

namespace App\Filament\Resources\TestItemResource\Pages;

use App\Filament\Resources\TestItemResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListTestItems extends ListRecords
{
    protected static string $resource = TestItemResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
