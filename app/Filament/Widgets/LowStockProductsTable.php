<?php

namespace App\Filament\Widgets;

use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use App\Models\Product;
use App\Models\Inventory;

use App\Filament\Resources\ProductResource;

class LowStockProductsTable extends BaseWidget
{
    protected static ?int $sort = 3; // Orden en el dashboard
    protected int | string | array $columnSpan = '1';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Product::query()
                    ->where('business_id', auth()->user()->business_id)
                    // Puedes cambiar el '5' por un valor de stock mínimo configurable
                    ->where('stock', '<=', 10) 
            )
            ->heading('Productos con Bajo Stock')
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Producto'),
                Tables\Columns\TextColumn::make('stock')
                    ->label('Stock Actual')
                    ->numeric()
                    ->badge()
                    ->color('danger'),
                Tables\Columns\TextColumn::make('unitOfMeasure.name')
                    ->label('Unidad Base'),
            ]);
            
    }
}