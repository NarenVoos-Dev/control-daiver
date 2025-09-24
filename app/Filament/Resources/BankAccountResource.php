<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BankAccountResource\Pages;
use App\Filament\Resources\BankAccountResource\RelationManagers;
use App\Models\BankAccount;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class BankAccountResource extends Resource
{
    protected static ?string $model = BankAccount::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-office';
    protected static ?string $navigationGroup = 'Configuracion';
    protected static ?string $modelLabel = 'Cuenta Bancaria';
    protected static ?string $pluralModelLabel = 'Cuentas Bancarias';
    protected static ?int $navigationSort = 57;


    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Hidden::make('business_id')
                    ->default(auth()->user()->business_id),
                
                Forms\Components\TextInput::make('name')
                    ->label('Nombre de la Cuenta')
                    ->required()
                    ->maxLength(255)
                    ->helperText('Ej: Bancolombia Ahorros, Nequi, Daviplata.'),

                Forms\Components\TextInput::make('account_number')
                    ->label('Número de Cuenta / Teléfono')
                    ->maxLength(255),
                
                Forms\Components\TextInput::make('details')
                    ->label('Detalles Adicionales')
                    ->helperText('Ej: Nombre del titular, Cédula, etc.')
                    ->maxLength(255),

                Forms\Components\Toggle::make('is_active')
                    ->label('Activa para recibir pagos')
                    ->default(true),
            ]);
    }


    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nombre de la Cuenta')
                    ->searchable(),
                Tables\Columns\TextColumn::make('account_number')
                    ->label('Número de Cuenta')
                    ->searchable(),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Activa')
                    ->boolean(),
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
            'index' => Pages\ListBankAccounts::route('/'),
            'create' => Pages\CreateBankAccount::route('/create'),
            'edit' => Pages\EditBankAccount::route('/{record}/edit'),
        ];
    }
}
