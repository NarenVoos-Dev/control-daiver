<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TestItemResource\Pages;
use App\Filament\Resources\TestItemResource\RelationManagers;
use App\Models\TestItem;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class TestItemResource extends Resource
{
    protected static ?string $model = TestItem::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        
        return $form
        ->schema([
            Forms\Components\TextInput::make('name')
                ->default('Prueba'),
            Forms\Components\Toggle::make('requires_confirmation')
                ->label('¿Requerir confirmación para guardar?')
                ->live(), // Hacemos que sea reactivo
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
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
            'index' => Pages\ListTestItems::route('/'),
            'create' => Pages\CreateTestItem::route('/create'),
            'edit' => Pages\EditTestItem::route('/{record}/edit'),
        ];
    }
}
