<?php

namespace App\Filament\SuperAdmin\Resources\BusinessResource\Pages;

use App\Filament\SuperAdmin\Resources\BusinessResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Filament\Forms\Components\TextInput;
use Illuminate\Support\Facades\Hash;
use Filament\Notifications\Notification;

class EditBusiness extends EditRecord
{
    protected static string $resource = BusinessResource::class;

    protected function getHeaderActions(): array { return [Actions\DeleteAction::make()]; }

    protected function getSaveFormAction(): Actions\Action
    {
        return Actions\Action::make('save')
            ->label(__('filament-panels::resources/pages/edit-record.form.actions.save.label'))
            ->submit('save')->keyBindings(['mod+s'])
            ->requiresConfirmation()
            ->modalHeading('Confirmar Acción')
            ->modalDescription('Por favor, ingresa tu contraseña para confirmar los cambios en la licencia.')
            ->modalIcon('heroicon-o-key')
            ->form([TextInput::make('password')->label('Contraseña de Super Admin')->password()->required()])
            ->action(function (array $data) {
                if (! Hash::check($data['password'], auth()->user()->password)) {
                    Notification::make()->title('Contraseña Incorrecta')->danger()->send();
                    return;
                }
                $this->save();
            });
    }
}
