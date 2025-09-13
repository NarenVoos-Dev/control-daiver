<?php

namespace App\Filament\Resources\TestItemResource\Pages;

use App\Filament\Resources\TestItemResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditTestItem extends EditRecord
{
    protected static string $resource = TestItemResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
