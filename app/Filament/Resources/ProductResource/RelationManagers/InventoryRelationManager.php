<?php

namespace App\Filament\Resources\ProductResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Database\Eloquent\Model;
use App\Models\Location;

class InventoryRelationManager extends RelationManager
{
    protected static string $relationship = 'inventory';
    protected static ?string $recordTitleAttribute = 'id';
    protected static ?string $title = 'Inventario por Bodega';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('location_id')
                    ->label('Bodega / Sucursal')
                    ->options(Location::query()->pluck('name', 'id'))
                    ->required()
                    // Evita que se pueda añadir stock a una bodega que ya lo tiene para este producto
                    ->unique(ignoreRecord: true, modifyRuleUsing: function ($rule, RelationManager $livewire) {
                        return $rule->where('product_id', $livewire->ownerRecord->id);
                    }),

                Forms\Components\TextInput::make('stock')
                    ->required()
                    ->numeric()
                    ->label('Cantidad en Stock'),

                    Forms\Components\TextInput::make('stock_minimo')
                ->required()
                ->numeric()
                ->label('Stock Mínimo Permitido')
                ->default(0)
                ->helperText('Cuando el stock sea igual o menor a este valor, se generará una alerta.'),
        ]);

    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('location_id')
            ->columns([
                Tables\Columns\TextColumn::make('location.name')
                    ->label('Bodega / Sucursal'),
                    
                Tables\Columns\TextColumn::make('stock')
                    ->label('Cantidad en Stock')
                    ->numeric()
                    ->color(fn ($record): string => $record->stock <= $record->stock_minimo ? 'danger' : 'success'),
                Tables\Columns\TextColumn::make('stock_minimo')
                    ->label('Stock Mínimo')
                    ->numeric(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
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
}
