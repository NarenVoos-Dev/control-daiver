<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CategoryResource\Pages;
use App\Models\Category;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class CategoryResource extends Resource
{
    protected static ?string $model = Category::class;

    protected static ?string $navigationIcon = 'heroicon-o-tag';
    
    // Lo agrupamos junto a Productos
    protected static ?string $navigationGroup = 'Inventario'; 
    
    // Cambiamos el nombre para que sea más legible
    protected static ?string $modelLabel = 'Categoría';
    protected static ?string $pluralModelLabel = 'Categorías';


    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                // Campo oculto para asegurar la multiempresa
                Forms\Components\Hidden::make('business_id')
                    ->default(auth()->user()->business_id),
                
                Forms\Components\TextInput::make('name')
                    ->label('Nombre de la Categoría')
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nombre')
                    ->searchable(),
                // ¡Extra! Contamos cuántos productos tiene cada categoría.
                Tables\Columns\TextColumn::make('products_count')
                    ->counts('products')
                    ->label('Nº de Productos'),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->requiresConfirmation(), // Pedir confirmación antes de borrar
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
            'index' => Pages\ListCategories::route('/'),
            // Usamos modales para crear y editar
            // 'create' => Pages\CreateCategory::route('/create'),
            // 'edit' => Pages\EditCategory::route('/{record}/edit'),
        ];
    }

    /**
     * Filtro multiempresa
     */
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('business_id', auth()->user()->business_id);
    }
}
