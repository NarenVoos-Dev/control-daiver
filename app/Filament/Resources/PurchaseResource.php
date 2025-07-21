<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PurchaseResource\Pages;
use App\Models\Purchase;
use App\Models\Product;
use App\Models\UnitOfMeasure;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Illuminate\Support\Number;

class PurchaseResource extends Resource
{
    protected static ?string $model = Purchase::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-cart';

    protected static ?string $navigationGroup = 'Inventario';
    
    
    protected static ?string $modelLabel = 'Compra';
    protected static ?string $pluralModelLabel = 'Compras';

   /* public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Hidden::make('business_id')
                    ->default(auth()->user()->business_id),

                Forms\Components\Wizard::make([
                    Forms\Components\Wizard\Step::make('Información General')
                        ->schema([
                            Forms\Components\Select::make('supplier_id')
                                ->relationship('supplier', 'name')
                                ->searchable()
                                ->preload()
                                ->required()
                                ->label('Proveedor'),
                            Forms\Components\DatePicker::make('date')
                                ->label('Fecha de la Compra')
                                ->required()
                                ->default(now()),
                        ]),
                    Forms\Components\Wizard\Step::make('Items de la Compra')
                        ->schema([
                            Forms\Components\Repeater::make('items')
                               //->relationship() 
                                ->schema([
                                    Forms\Components\Select::make('product_id')
                                        ->label('Producto')
                                        ->options(Product::query()->pluck('name', 'id'))
                                        ->required()
                                        ->searchable()
                                        ->live()
                                        ->afterStateUpdated(function (Get $get, Set $set) {
                                            $product = Product::find($get('product_id'));
                                            if ($product) {
                                                $set('price', $product->cost ?? 0);
                                            }
                                        }),
                                    Forms\Components\TextInput::make('quantity')
                                        ->label('Cantidad Comprada')
                                        ->required()
                                        ->numeric()
                                        ->live(onBlur: true),
                                    Forms\Components\TextInput::make('price')
                                        ->label('Costo por Unidad')
                                        ->required()
                                        ->numeric()
                                        ->live(onBlur: true),
                                    
                                    // --- CÓDIGO CORREGIDO AQUÍ ---
                                    Forms\Components\Select::make('unit_of_measure_id')
                                        ->options(UnitOfMeasure::query()->pluck('name', 'id'))
                                        ->label('Medida de Compra')
                                        ->searchable()
                                        ->preload()
                                        ->required(),
                                ])
                                ->columns(4)
                                ->live()
                                ->afterStateUpdated(function (Get $get, Set $set) {
                                    self::updateTotals($get, $set);
                                }),
                        ]),
                    Forms\Components\Wizard\Step::make('Totales')
                        ->schema([
                            Forms\Components\TextInput::make('total')
                                ->label('Total de la Compra')
                                ->readOnly()
                                ->numeric()
                                ->prefix('$'),
                        ])
                ])->columnSpanFull()
            ]);
    }*/
    public static function form(Form $form): Form
{
    return $form
        ->schema([
            // Usamos Sections en lugar de Wizard para una mejor carga de datos en la edición.
            Forms\Components\Section::make('Información de la Compra')
                ->schema([
                    Forms\Components\Hidden::make('business_id')
                        ->default(auth()->user()->business_id),
                    Forms\Components\Select::make('supplier_id')
                        ->relationship('supplier', 'name')
                        ->searchable()
                        ->preload()
                        ->required()
                        ->label('Proveedor'),
                    Forms\Components\DatePicker::make('date')
                        ->label('Fecha de la Compra')
                        ->required()
                        ->default(now()),
                ])->columns(2),
            
            Forms\Components\Section::make('Items de la Compra')
                ->schema([
                    Forms\Components\Repeater::make('items')
                        ->schema([
                            Forms\Components\Select::make('product_id')
                                ->label('Producto')
                                ->options(Product::query()->pluck('name', 'id'))
                                ->required()
                                ->searchable()
                                ->preload()
                                ->live()
                                ->afterStateUpdated(function (Get $get, Set $set) {
                                    $product = Product::find($get('product_id'));
                                    if ($product) {
                                        $set('price', $product->cost ?? 0);
                                    }
                                }),
                            Forms\Components\TextInput::make('quantity')
                                ->label('Cantidad Comprada')
                                ->required()->numeric()->live(onBlur: true),
                            Forms\Components\TextInput::make('price')
                                ->label('Costo (antes de IVA)')
                                ->required()->numeric()->live(onBlur: true),
                            /*Forms\Components\TextInput::make('tax_rate')
                                ->label('IVA (%)')
                                ->numeric()->required()->default(19)
                                ->live(onBlur: true),*/
                            
                            Forms\Components\Select::make('unit_of_measure_id')
                                ->label('Unidad de Compra')
                                ->options(UnitOfMeasure::query()->pluck('name', 'id'))
                                ->searchable()
                                ->preload()
                                ->required(),
                        ])
                        ->columns(4)
                        ->live()
                        ->afterStateUpdated(fn (Get $get, Set $set) => self::updateTotals($get, $set)),
                ]),

            Forms\Components\Section::make('Resumen de Totales')
                ->schema([
                    /*Forms\Components\TextInput::make('subtotal')
                        ->label('Subtotal')->readOnly()->numeric()->prefix('$'),
                    Forms\Components\TextInput::make('tax_total')
                        ->label('Total IVA')->readOnly()->numeric()->prefix('$'),*/
                    Forms\Components\TextInput::make('total')
                        ->label('Total General')->readOnly()->numeric()->prefix('$'),
                ])->columns(3),
        ]);
}

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('supplier.name')
                    ->label('Proveedor')
                    ->sortable(),
                Tables\Columns\TextColumn::make('total')
                    ->money('cop')
                    ->sortable(),
                Tables\Columns\TextColumn::make('date')
                    ->date('d/m/Y')
                    ->label('Fecha')
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
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
            'index' => Pages\ListPurchases::route('/'),
            'create' => Pages\CreatePurchase::route('/create'),
            'edit' => Pages\EditPurchase::route('/{record}/edit'),
            
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('business_id', auth()->user()->business_id);
    }
    
    // Función para recalcular el total
    public static function updateTotals(Get $get, Set $set): void
    {
        $total = 0;
        $items = $get('items') ?? [];

        foreach ($items as $item) {
            $total += (float)($item['quantity'] ?? 0) * (float)($item['price'] ?? 0);
        }
        
        $set('total', $total);
    }
}
