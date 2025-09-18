<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UnitOfMeasureResource\Pages;
use App\Models\UnitOfMeasure;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class UnitOfMeasureResource extends Resource
{
    protected static ?string $model = UnitOfMeasure::class;

    protected static ?string $navigationIcon = 'heroicon-o-cube';

    protected static ?string $navigationGroup = 'Configuracion';
    protected static ?int $navigationSort = 54;
    protected static ?string $modelLabel = 'Unidad de Medida';
    protected static ?string $pluralModelLabel = 'Unidades de Medida';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Hidden::make('business_id')
                    ->default(auth()->user()->business_id),

                Forms\Components\TextInput::make('name')
                    ->label('Nombre de la Unidad')
                    ->required()
                    ->maxLength(255)
                    ->columnSpan('full'),

                Forms\Components\TextInput::make('abbreviation')
                    ->label('Abreviatura')
                    ->required()
                    ->maxLength(255),

                Forms\Components\TextInput::make('conversion_factor')
                    ->label('Factor de Conversión')
                    ->numeric()
                    ->required()
                    ->default(1)
                    ->helperText('¿Cuántas unidades base contiene? Ej: Una "Caja de 12" tiene un factor de 12. La "Unidad" base siempre tiene un factor de 1.'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nombre')
                    ->searchable(),
                Tables\Columns\TextColumn::make('abbreviation')
                    ->label('Abreviatura'),
                Tables\Columns\TextColumn::make('conversion_factor')
                    ->label('Factor')
                    ->numeric(),
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

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUnitOfMeasures::route('/'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('business_id', auth()->user()->business_id);
    }
}
