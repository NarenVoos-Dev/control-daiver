<?php

namespace App\Filament\Resources\TestItemResource\Pages;

use App\Filament\Resources\TestItemResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Filament\Forms\Get; // <-- Importante

class CreateTestItem extends CreateRecord
{
    protected static string $resource = TestItemResource::class;

    // Aquí está la prueba más simple posible
    protected function getCreateFormAction(): Actions\Action
    {
        return parent::getCreateFormAction()
            ->requiresConfirmation(function (Get $get) {
                // La confirmación depende ÚNICAMENTE del estado del interruptor
                return (bool) $get('requires_confirmation');
            })
            ->modalHeading('Confirmación de Prueba')
            ->modalDescription('Este modal solo debe aparecer si el interruptor está encendido.');
    }
}