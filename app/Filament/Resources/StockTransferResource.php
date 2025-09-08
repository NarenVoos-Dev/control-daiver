<?php

namespace App\Filament\Resources;

use App\Filament\Resources\StockTransferResource\Pages;
use App\Filament\Resources\StockTransferResource\RelationManagers;
use App\Models\StockTransfer;
use App\Models\Location;
use App\Models\Product;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Get;
use Filament\Forms\Set;

class StockTransferResource extends Resource
{
    protected static ?string $model = StockTransfer::class;

    protected static ?string $navigationIcon = 'heroicon-o-arrows-right-left';
    protected static ?string $navigationGroup = 'Inventario';
    protected static ?string $modelLabel = 'Traslado de Stock';
    protected static ?string $pluralModelLabel = 'Traslados de Stock';

    public static function form(Form $form): Form
    {
            return $form
            ->schema([
                Forms\Components\Wizard::make([
                    Forms\Components\Wizard\Step::make('Información del Traslado')
                        ->schema([
                            Forms\Components\Hidden::make('business_id')->default(auth()->user()->business_id),
                            Forms\Components\Hidden::make('user_id')->default(auth()->id()),
                            
                            Forms\Components\Select::make('origin_location_id')
                                ->label('Bodega de Origen')
                                ->options(Location::query()->pluck('name', 'id'))
                                ->required()
                                ->live()
                                ->afterStateUpdated(fn (Set $set) => $set('destination_location_id', null)),

                            Forms\Components\Select::make('destination_location_id')
                                ->label('Bodega de Destino')
                                ->options(function (Get $get) {
                                    return Location::query()->where('id', '!=', $get('origin_location_id'))->pluck('name', 'id');
                                })
                                ->required(),

                            Forms\Components\DatePicker::make('date')
                                ->label('Fecha del Traslado')
                                ->default(now())
                                ->required(),
                            
                            Forms\Components\Textarea::make('notes')
                                ->label('Notas Adicionales')
                                ->columnSpanFull(),
                        ])->columns(2),
                    
                    Forms\Components\Wizard\Step::make('Productos a Trasladar')
                        ->schema([
                            Forms\Components\Repeater::make('items')
                                ->relationship()
                                ->schema([
                                    Forms\Components\Select::make('product_id')
                                        ->label('Producto')
                                        ->options(Product::query()->pluck('name', 'id'))
                                        ->searchable()
                                        ->required(),

                                    Forms\Components\TextInput::make('quantity')
                                        ->label('Cantidad a Trasladar')
                                        ->required()
                                        ->numeric()
                                        ->minValue(1),
                                ])
                                ->columns(2)
                                ->addActionLabel('Añadir Producto'),
                        ]),
                ])->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')->label('#')->sortable(),
                Tables\Columns\TextColumn::make('originLocation.name')->label('Origen')->searchable(),
                Tables\Columns\TextColumn::make('destinationLocation.name')->label('Destino')->searchable(),
                Tables\Columns\TextColumn::make('user.name')->label('Realizado por')->searchable(),
                Tables\Columns\TextColumn::make('date')->date('d/m/Y')->label('Fecha')->sortable(),
            ])
            ->filters([
                //
            ])
            ->defaultSort('id', 'desc')
            ->actions([
                Tables\Actions\ViewAction::make(),
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
            'index' => Pages\ListStockTransfers::route('/'),
            'create' => Pages\CreateStockTransfer::route('/create'),
            //'view' => Pages\ViewStockTransfer::route('/{record}'),
            'edit' => Pages\EditStockTransfer::route('/{record}/edit'),
        ];
    }
}
