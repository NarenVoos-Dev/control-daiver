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
use App\Filament\Resources\EgressResource;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Actions\Action;
use App\Models\Supplier;
use App\Models\Location;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Forms\Components\DatePicker;


class PurchaseResource extends Resource
{
    protected static ?string $model = Purchase::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-cart';

    protected static ?string $navigationGroup = 'Inventario';
    
    
    protected static ?string $modelLabel = 'Compra';
    protected static ?string $pluralModelLabel = 'Compras';
    public static function form(Form $form): Form
{
    return $form
        ->schema([
            // Usamos Sections en lugar de Wizard para una mejor carga de datos en la edición.
            Forms\Components\Section::make('Información de la Compra')
                ->schema([
                    Forms\Components\Hidden::make('business_id')
                        ->default(auth()->user()->business_id),

                    Forms\Components\Select::make('location_id')
                            ->label('Bodega/Sucursal de Destino')
                            ->options(Location::query()->where('business_id', auth()->user()->business_id)->pluck('name', 'id'))
                            ->required()
                            ->searchable()
                            ->helperText('Selecciona la bodega a la que ingresará esta mercancía.'),
                            
                    Forms\Components\Select::make('supplier_id')
                        ->relationship('supplier', 'name')
                        ->searchable()
                        ->preload()
                        ->required()
                        ->label('Proveedor')
                        ->createOptionForm([
                            Forms\Components\Hidden::make('business_id')
                                ->default(auth()->user()->business_id),
                                
                            Forms\Components\TextInput::make('name')
                                ->label('Nombre del Proveedor')
                                ->required()
                                ->maxLength(255),
                                
                            Forms\Components\TextInput::make('document')
                                ->label('Documento (NIT/Cédula)'),
                                
                            Forms\Components\TextInput::make('phone')
                                ->label('Teléfono'),
                                
                            Forms\Components\TextInput::make('email')
                                ->label('Correo Electrónico')
                                ->email(),
                        ])
                        ->createOptionUsing(function (array $data): int {
                            // Esta función crea el proveedor en la base de datos...
                            $supplier = Supplier::create($data);
                            // ...y devuelve el ID del nuevo registro para seleccionarlo automáticamente.
                            return $supplier->id;
                        }),
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
                BadgeColumn::make('status')
                    ->label('Estado de Pago')
                    ->colors([
                        'warning' => 'Pendiente',
                        'success' => 'Pagada',
                    ]),

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
                SelectFilter::make('supplier_id')
                ->label('Proveedor')
                ->relationship('supplier', 'name')
                ->searchable()
                ->preload(),

                SelectFilter::make('status')
                    ->label('Estado de Pago')
                    ->options([
                        'Pendiente' => 'Pendiente',
                        'Pagada' => 'Pagada',
                    ]),

                Filter::make('date')
                    ->form([
                        DatePicker::make('start_date')->label('Fecha Inicio'),
                        DatePicker::make('end_date')->label('Fecha Fin'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['start_date'] ?? null,
                                fn (Builder $query, $date): Builder => $query->whereDate('date', '>=', $date),
                            )
                            ->when(
                                $data['end_date'] ?? null,
                                fn (Builder $query, $date): Builder => $query->whereDate('date', '<=', $date),
                            );
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                Action::make('registrarPago')
                    ->label('Registrar Pago')
                    ->icon('heroicon-o-currency-dollar')
                    ->color('success')
                    // La acción solo es visible si la compra está pendiente
                    ->visible(fn (Purchase $record): bool => $record->status === 'Pendiente')
                    // Redirigimos al formulario de creación de EgressResource, pasando el ID de la compra
                    ->url(fn (Purchase $record): string => EgressResource::getUrl('create', ['purchase_id' => $record->id])),
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
