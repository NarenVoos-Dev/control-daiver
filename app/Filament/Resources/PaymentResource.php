<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PaymentResource\Pages;
use App\Filament\Resources\PaymentResource\RelationManagers;
use App\Models\Payment;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Get;
use Filament\Notifications\Notification;

use App\Models\Client;
use App\Models\Sale;

class PaymentResource extends Resource
{
    protected static ?string $model = Payment::class;

    protected static ?string $navigationIcon = 'heroicon-o-banknotes';
    protected static ?string $navigationGroup = 'Finanzas';
    protected static ?int $navigationSort = 21;
    protected static ?string $modelLabel = 'Pago / Abono';
    protected static ?string $pluralModelLabel = 'Cuentas por cobrar';

    public static function form(Form $form): Form
    {
        return $form
             ->schema([
                Forms\Components\Hidden::make('business_id')->default(auth()->user()->business_id),
           
                Forms\Components\Select::make('client_id')
                    ->label('Cliente con Deuda')
                    ->relationship(
                        name: 'client',
                        titleAttribute: 'name', // Fallback
                        modifyQueryUsing: fn (Builder $query) => $query
                            ->where('business_id', auth()->user()->business_id)
                            ->whereHas('sales', fn(Builder $query) => $query->where('status', 'Pendiente'))
                    )
                    // Construye una etiqueta personalizada para cada opción
                    ->getOptionLabelFromRecordUsing(fn (Client $record) => "{$record->name} - Doc: {$record->document} - Deuda: $" . number_format($record->getCurrentDebt(), 2))
                    // Permite buscar por nombre y por documento
                    ->searchable(['name', 'document'])
                    ->preload()
                    ->live()
                    ->required(),
                
                Forms\Components\Select::make('sale_id')
                    ->label('Aplicar a una Factura Específica (Opcional)')
                    ->options(function (Get $get) {
                        $clientId = $get('client_id');
                        if (!$clientId) return [];
                        return Sale::query()
                            ->where('client_id', $clientId)
                            ->where('status', 'Pendiente')
                            ->get()
                            ->mapWithKeys(fn ($sale) => [$sale->id => "Venta #{$sale->id} - Pendiente: $" . number_format($sale->pending_amount, 2)])
                            ->toArray();
                    })
                    ->searchable()
                    ->placeholder('Aplicar a las facturas más antiguas')
                    ->searchable(),

                Forms\Components\TextInput::make('amount')
                    ->label('Monto del Abono')
                    ->numeric()->prefix('$')->required(),
                Forms\Components\DatePicker::make('payment_date')
                    ->label('Fecha del Pago')->default(now())->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('client.name')->label('Cliente')->searchable(),
                Tables\Columns\TextColumn::make('amount')->label('Monto')->money('cop')->sortable(),
                Tables\Columns\TextColumn::make('payment_date')->label('Fecha de Pago')->date('d/m/Y')->sortable(),
            ])
            ->defaultSort('payment_date', 'desc')
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
            'index' => Pages\ListPayments::route('/'),
            'create' => Pages\CreatePayment::route('/create'),
            'edit' => Pages\EditPayment::route('/{record}/edit'),
        ];
    }
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('business_id', auth()->user()->business_id);
    }
}
