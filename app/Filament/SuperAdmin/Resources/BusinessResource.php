<?php

namespace App\Filament\SuperAdmin\Resources;

use App\Filament\SuperAdmin\Resources\BusinessResource\Pages;
use App\Models\Business;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class BusinessResource extends Resource
{
    protected static ?string $model = Business::class;
    protected static ?string $navigationIcon = 'heroicon-o-building-storefront';
    protected static ?string $modelLabel = 'Negocio';
    protected static ?string $pluralModelLabel = 'Negocios';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('name')->required(),
            Forms\Components\TextInput::make('nit')->required()->unique(ignoreRecord: true),
            Forms\Components\Toggle::make('is_active')->label('Licencia Activa'),
            Forms\Components\Toggle::make('has_pos_access')->label('Acceso al POS'),
            Forms\Components\DatePicker::make('license_expires_at')->label('Licencia Vence el'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('name')->searchable(),
            Tables\Columns\IconColumn::make('is_active')->label('Activo')->boolean(), 
            Tables\Columns\IconColumn::make('has_pos_access')->label('POS')->boolean(), // <-- AÑADIR{cm}
            Tables\Columns\TextColumn::make('license_expires_at')->label('Vencimiento')->date(),
        ])->actions([Tables\Actions\EditAction::make()]);
    }

    public static function getPages(): array
    {
        return ['index' => Pages\ListBusinesses::route('/'), 'edit' => Pages\EditBusiness::route('/{record}/edit')];
    }
}
