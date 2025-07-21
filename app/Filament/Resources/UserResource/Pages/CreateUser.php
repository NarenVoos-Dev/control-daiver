<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    //Asignar el id de busineess automaticamente por cada usuario que se cree
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['business_id'] = auth()->user()->business_id;
    
        return $data;
    }
}
