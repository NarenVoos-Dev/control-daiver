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

use App\Models\Client;
use App\Models\Sale;

class PaymentResource extends Resource
{
    protected static ?string $model = Payment::class;

    protected static ?string $navigationIcon = 'heroicon-o-banknotes';
    protected static ?string $navigationGroup = 'Ventas';
    protected static ?string $modelLabel = 'Pago / Abono';
    protected static ?string $pluralModelLabel = 'Pagos y Abonos';

    public static function form(Form $form): Form
    {
        return $form
             ->schema([
                Forms\Components\Hidden::make('business_id')->default(auth()->user()->business_id),
                Forms\Components\Select::make('client_id')
                    ->label('Cliente con Deuda')
                    ->options(
                        // Muestra solo clientes que tienen ventas pendientes
                        Client::whereHas('sales', fn(Builder $query) => $query->where('status', 'Pendiente'))
                              ->where('business_id', auth()->user()->business_id)
                              ->pluck('name', 'id')
                    )
                    ->searchable()->required(),
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
            //'create' => Pages\CreatePayment::route('/create'),
            //'edit' => Pages\EditPayment::route('/{record}/edit'),
        ];
    }
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('business_id', auth()->user()->business_id);
    }
}
