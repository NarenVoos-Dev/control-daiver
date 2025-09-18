<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ClientResource\Pages;
use App\Models\Client;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use App\Models\Zone;

class ClientResource extends Resource
{
    protected static ?string $model = Client::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';

    // Lo agrupamos junto a Proveedores
    protected static ?string $navigationGroup = 'Catalogos';
    protected static ?int $navigationSort = 32;
    protected static ?string $modelLabel = 'Cliente';
    protected static ?string $pluralModelLabel = 'Clientes';


    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Hidden::make('business_id')
                    ->default(auth()->user()->business_id),
                    
                Forms\Components\TextInput::make('name')
                    ->label('Nombre del Cliente')
                    ->required()
                    ->maxLength(255),

                Forms\Components\TextInput::make('document')
                    ->label('Cédula o Documento')
                    ->required()
                    ->maxLength(255)
                    ->nullable()
                    ->unique(
                        ignoreRecord: true,
                    )
                    ->validationMessages([
                        'unique' => 'Este número de documento ya está registrado.',
                    ]),

                Forms\Components\TextInput::make('phone')
                    ->label('Teléfono')
                    ->tel()
                    ->maxLength(255),

                Forms\Components\TextInput::make('email')
                    ->label('Correo Electrónico')
                    ->email()
                    ->maxLength(255),

                Forms\Components\Select::make('zone_id')
                    ->label('Zona')
                    ->options(Zone::query()->where('business_id', auth()->user()->business_id)->pluck('name', 'id'))
                    ->searchable()
                    ->placeholder('Sin zona asignada'),
                
                Forms\Components\TextInput::make('credit_limit')
                    ->label('Límite de Crédito')
                    ->numeric()
                    ->prefix('$')
                    ->default(0)
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->label('Nombre')->searchable(),
                Tables\Columns\TextColumn::make('zone.name')->label('Zona')->badge(),
                Tables\Columns\TextColumn::make('credit_limit')->label('Límite Crédito')->money('cop'),
                Tables\Columns\TextColumn::make('document')->label('Documento')->searchable(),
                Tables\Columns\TextColumn::make('phone')->label('Teléfono'),
                Tables\Columns\TextColumn::make('email')->label('Email')->searchable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
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
            'index' => Pages\ListClients::route('/'),
            // Usamos modales por defecto
            // 'create' => Pages\CreateClient::route('/create'),
            // 'edit' => Pages\EditClient::route('/{record}/edit'),
        ];
    }    

    /**
     * Filtro multiempresa
     */
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('business_id', auth()->user()->business_id);
    }
}
