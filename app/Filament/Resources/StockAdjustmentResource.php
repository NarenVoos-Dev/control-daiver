<?php

namespace App\Filament\Resources;

use App\Filament\Resources\StockAdjustmentResource\Pages;
use App\Models\StockAdjustment;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class StockAdjustmentResource extends Resource
{
    protected static ?string $model = \App\Models\StockAdjustment::class;

    protected static ?string $navigationIcon = 'heroicon-o-arrows-right-left';

    // Lo agrupamos en la sección de Inventario
    protected static ?string $navigationGroup = 'Inventario';
    
    protected static ?string $modelLabel = 'Ajuste de Inventario';
    protected static ?string $pluralModelLabel = 'Ajustes de Inventario';


    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Hidden::make('business_id')->default(auth()->user()->business_id),
                Forms\Components\Select::make('location_id')
                ->label('Bodega / Sucursal')
                ->relationship('location', 'name')
                ->required(),
                Forms\Components\Select::make('product_id')
                    ->label('Producto')
                    ->options(\App\Models\Product::query()->pluck('name', 'id'))
                    ->required()
                    ->searchable()
                    ->preload(),
                
                Forms\Components\Select::make('type')
                    ->label('Tipo de Ajuste')
                    ->options([
                        'entrada' => 'Entrada (Sumar al stock)',
                        'salida' => 'Salida (Restar del stock)',
                    ])
                    ->required(),

                Forms\Components\TextInput::make('quantity')
                    ->label('Cantidad')
                    ->helperText('La cantidad se ajustará en la unidad base del producto.')
                    ->required()
                    ->numeric(),
                
                Forms\Components\Textarea::make('reason')
                    ->label('Motivo del Ajuste')
                    ->required()
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('product.name')->label('Producto')->searchable(),
                Tables\Columns\TextColumn::make('type')->label('Tipo')->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'entrada' => 'success',
                        'salida' => 'danger',
                    }),
                Tables\Columns\TextColumn::make('quantity')->label('Cantidad')->numeric(),
                Tables\Columns\TextColumn::make('reason')->label('Motivo')->limit(50),
                Tables\Columns\TextColumn::make('created_at')->label('Fecha')->dateTime('d/m/Y H:i'),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListStockAdjustments::route('/'),
            'create' => Pages\CreateStockAdjustment::route('/create'),
            // Los ajustes no se deberían editar para mantener la integridad de los movimientos.
            // 'edit' => Pages\EditStockAdjustment::route('/{record}/edit'),
        ];
    }
    
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('business_id', auth()->user()->business_id);
    }
}