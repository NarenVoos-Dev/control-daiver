<?php

namespace App\Filament\Resources\EgressResource\Pages;

use App\Filament\Resources\EgressResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditEgress extends EditRecord
{
    protected static string $resource = EgressResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
