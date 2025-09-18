<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EgressResource\Pages;
use App\Filament\Resources\EgressResource\RelationManagers;
use App\Models\Egress;
use App\Models\Purchase; 
use App\Models\Supplier;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Filters\Filter;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Get;


class EgressResource extends Resource
{
    protected static ?string $model = Egress::class;

    protected static ?string $navigationIcon = 'heroicon-o-arrow-trending-down';
    protected static ?string $pluralModelLabel = 'Egresos';
    protected static ?string $modelLabel = 'Egresos';
    protected static ?string $navigationGroup = 'Finanzas';
    protected static ?int $navigationSort = 23;

    public static function form(Form $form): Form
    {
        $purchase = null;
        if (request()->has('purchase_id')) {
            $purchase = Purchase::find(request('purchase_id'));
        }

        return $form
        ->schema([
            Forms\Components\Hidden::make('business_id')->default(auth()->user()->business_id),
            Forms\Components\Hidden::make('user_id')->default(auth()->id()),
            Forms\Components\Hidden::make('purchase_id')->default($purchase?->id),

            Forms\Components\Section::make('Detalles del Egreso')
                ->schema([
                    Forms\Components\Select::make('type')
                        ->label('Tipo de Egreso')
                        ->options([
                            'compra' => 'Compra de Inventario',
                            'gasto' => 'Gasto Operativo',
                            'retiro' => 'Retiro de Efectivo',
                        ])
                        ->default($purchase ? 'compra' : null) // Si viene de una compra, el tipo es 'compra'
                        ->required()
                        ->disabled(fn (): bool => request()->has('purchase_id')), // Deshabilitado si viene de una compra

                    Forms\Components\Textarea::make('description')
                        ->label('Descripción')
                        ->required()
                        ->default($purchase ? 'Pago de Compra #' . $purchase->id : null)
                        ->columnSpanFull(),

                    Forms\Components\TextInput::make('amount')
                        ->label('Monto')
                        ->required()->numeric()->prefix('$')
                        ->default($purchase ? $purchase->total : 0)
                        ->readOnly(fn (): bool => request()->has('purchase_id')), // Solo lectura si viene de una compra

                    Forms\Components\Select::make('supplier_id')
                        ->label('Proveedor')
                        ->options(Supplier::query()->pluck('name', 'id'))
                        ->default($purchase ? $purchase->supplier_id : null)
                        ->searchable()
                        ->disabled(fn (): bool => request()->has('purchase_id')) // Deshabilitado si viene de una compra
                ])->columns(2),

            Forms\Components\Section::make('Información del Pago')
                ->schema([
                    Forms\Components\Select::make('payment_method')
                        ->label('Método de Pago')
                        ->options([
                            'efectivo' => 'Efectivo',
                            'transferencia' => 'Transferencia',
                            'crédito' => 'Crédito (Cuenta por Pagar)',
                        ])
                        ->required()
                        ->live(),
                    Forms\Components\DatePicker::make('date')
                        ->label('Fecha del Pago')
                        ->required()->default(now()),
                        
                    Toggle::make('pay_from_cash_session')
                        ->label('¿Pagar con dinero de la caja activa?')
                        ->onIcon('heroicon-s-banknotes')
                        ->offIcon('heroicon-o-banknotes')
                        // El interruptor solo aparece si el método de pago es 'efectivo'
                        ->visible(fn (Get $get): bool => $get('payment_method') === 'efectivo')
                   
                ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('date')
                    ->date('d/m/Y')
                    ->label('Fecha')
                    ->sortable()
                    ->searchable(),

                BadgeColumn::make('type')
                    ->label('Tipo')
                    ->colors([
                        'primary' => 'compra',
                        'warning' => 'gasto',
                        'danger' => 'retiro',
                    ])
                    ->searchable(),

                Tables\Columns\TextColumn::make('description')
                    ->label('Descripción')
                    ->limit(40) // Limita el texto para no ocupar mucho espacio
                    ->tooltip(fn ($record) => $record->description) // Muestra el texto completo al pasar el mouse
                    ->searchable(),

                Tables\Columns\TextColumn::make('amount')
                    ->label('Monto')
                    ->money('cop') // Formato para pesos colombianos
                    ->sortable(),

                BadgeColumn::make('payment_method')
                    ->label('Método de Pago')
                    ->colors([
                        'success' => 'efectivo',
                        'info' => 'transferencia',
                        'secondary' => 'crédito',
                    ]),

                Tables\Columns\TextColumn::make('user.name')
                    ->label('Registrado por')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true), // Oculto por defecto

                Tables\Columns\TextColumn::make('purchase_id')
                    ->label('Compra Relacionada')
                    ->formatStateUsing(fn ($state) => "Compra #{$state}") // Muestra "Compra #123"
                    ->url(fn ($record) => $record->purchase_id ? PurchaseResource::getUrl('edit', ['record' => $record->purchase_id]) : null)
                    ->openUrlInNewTab()
                    ->toggleable(),
            ])
            ->filters([
                Filter::make('date')
                    ->form([
                        DatePicker::make('created_from')->label('Desde'),
                        DatePicker::make('created_until')->label('Hasta'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['created_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('date', '>=', $date),
                            )
                            ->when(
                                $data['created_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('date', '<=', $date),
                            );
                    })
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
            'index' => Pages\ListEgresses::route('/'),
            'create' => Pages\CreateEgress::route('/create'),
            'edit' => Pages\EditEgress::route('/{record}/edit'),
        ];
    }
}
