<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CashSessionResource\Pages;
use App\Filament\Resources\CashSessionResource\RelationManagers;
use App\Models\CashSession;
use App\Models\Location;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class CashSessionResource extends Resource
{
    protected static ?string $model = CashSession::class;
    protected static ?string $navigationIcon = 'heroicon-o-archive-box';
    protected static ?string $navigationGroup = 'Operaciones';
    protected static ?int $navigationSort = 12;
    protected static ?string $modelLabel = 'Caja';
    protected static ?string $pluralModelLabel = 'Cajas';

    //protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form->schema([]); // No permitimos crear/editar desde aquí
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')->label('Sesión #')->sortable(),
                Tables\Columns\TextColumn::make('userOpened.name')->label('Abierta por')->searchable(),
                Tables\Columns\TextColumn::make('status')->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Abierta' => 'warning',
                        'Cerrada' => 'success',
                    }),
                Tables\Columns\TextColumn::make('location.name')->label('Sucursal / Bodega')->badge()->searchable(),

                Tables\Columns\TextColumn::make('opening_balance')->label('Monto Apertura')->money('cop'),
                Tables\Columns\TextColumn::make('closing_balance')->label('Monto Cierre')->money('cop'),
                Tables\Columns\TextColumn::make('difference')->label('Diferencia')->money('cop')
                    ->color(fn ($state) => $state > 0 ? 'success' : ($state < 0 ? 'danger' : 'gray')),
                Tables\Columns\TextColumn::make('opened_at')->label('Fecha Apertura')->dateTime('d/m/Y H:i'),
            ])
            ->defaultSort('opened_at', 'desc')
            ->actions([
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([]);
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
            'index' => Pages\ListCashSessions::route('/'),
           // 'create' => Pages\CreateCashSession::route('/create'),
            'view' => Pages\ViewCashSession::route('/{record}'),
            //'edit' => Pages\EditCashSession::route('/{record}/edit'),
        ];
    }
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('business_id', auth()->user()->business_id);
    }
}
