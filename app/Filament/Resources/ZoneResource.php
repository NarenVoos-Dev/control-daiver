<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ZoneResource\Pages;
use App\Filament\Resources\ZoneResource\RelationManagers;
use App\Models\Zone;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ZoneResource extends Resource
{
    protected static ?string $model = Zone::class;

    protected static ?string $navigationIcon = 'heroicon-o-map-pin';
    
    // Lo agrupamos en la sección de Administración
    protected static ?string $navigationGroup = 'Administración';
    
    protected static ?string $modelLabel = 'Zona';
    protected static ?string $pluralModelLabel = 'Zonas';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([                
                Forms\Components\Hidden::make('business_id')
                    ->default(auth()->user()->business_id),
                
                Forms\Components\TextInput::make('name')
                    ->label('Nombre de la Zona')
                    ->required()
                    ->maxLength(255),            
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nombre')
                    ->searchable(),
                Tables\Columns\TextColumn::make('clients_count')
                    ->counts('clients')
                    ->label('Nº de Clientes'),
            ])
            
            ->filters([
               //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                    Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListZones::route('/'),
            //'create' => Pages\CreateZone::route('/create'),
            //'edit' => Pages\EditZone::route('/{record}/edit'),
        ];
    }
    /**
     * Filtro multiempresa para asegurar que cada negocio solo vea sus propias zonas.
     */
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('business_id', auth()->user()->business_id);
    }
}
