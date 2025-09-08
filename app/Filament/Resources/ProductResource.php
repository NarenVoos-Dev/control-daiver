<?php
namespace App\Filament\Resources;

use App\Filament\Resources\ProductResource\Pages;
use App\Models\Product;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\ProductResource\RelationManagers;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static ?string $navigationIcon = 'heroicon-o-archive-box';

    protected static ?string $modelLabel = 'Productos';
    protected static ?string $pluralModelLabel = 'Productos';

    protected static ?string $navigationGroup = 'Inventario';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Hidden::make('business_id')
                    ->default(auth()->user()->business_id),
                
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255)
                    ->label('Nombre del producto')
                    ->columnSpan('full'),
                
                Forms\Components\Select::make('category_id')
                    ->relationship('category', 'name')
                    ->searchable()
                    ->preload()
                    ->createOptionForm([
                        Forms\Components\TextInput::make('name')
                            ->required(),
                        Forms\Components\Hidden::make('business_id')
                            ->default(auth()->user()->business_id)
                    ])
                    ->label('Categoría'),
                    
                Forms\Components\TextInput::make('sku')
                    ->label('SKU (Código)')
                    ->maxLength(255),
                
                // --- CAMBIO PRINCIPAL AQUÍ ---
                // Reemplazamos el campo de texto por un menú desplegable
                Forms\Components\Select::make('unit_of_measure_id')
                    ->relationship('unitOfMeasure', 'name')
                    ->searchable()
                    ->preload()
                    ->required()
                    ->label('Unidad de Medida Base')
                    ->helperText('La unidad en que se vende y se controla el stock.'),

                Forms\Components\TextInput::make('price')
                    ->label('Precio de Venta')
                    ->required()
                    ->numeric()
                    ->prefix('$'),
                
                Forms\Components\TextInput::make('cost')
                    ->label('Costo de Compra (por unidad base)')
                    ->numeric()
                    ->prefix('$'),
                
                /*Forms\Components\TextInput::make('stock')
                    ->label('Stock Actual (en unidad base)')
                    ->numeric()
                    ->default(0),*/
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->label('Nombre'),
                Tables\Columns\TextColumn::make('category.name')
                    ->label('Categoría')
                    ->badge(),
                Tables\Columns\TextColumn::make('price')
                    ->money('cop')
                    ->sortable()
                    ->label('Precio'),
                Tables\Columns\TextColumn::make('cost')
                    ->money('cop')
                    ->sortable()
                    ->label('Costo'),
                Tables\Columns\TextColumn::make('total_stock')
                    ->label('Stock Total')
                    ->numeric()
                    ->sortable(),
                // Mostramos el nombre de la unidad de medida desde la relación
                Tables\Columns\TextColumn::make('unitOfMeasure.name')
                    ->label('Unidad'),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\ViewAction::make(),
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
            'index' => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'view' => Pages\ViewProduct::route('/{record}'),
            'edit' => Pages\EditProduct::route('/{record}/edit'),
        ];
    }
    
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('business_id', auth()->user()->business_id);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\InventoryRelationManager::class,
        ];
    }
}