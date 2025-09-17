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
                Inventory::with(['product', 'location'])
                    ->whereColumn('stock', '<=', 'stock_minimo')
                    ->where('stock_minimo', '>', 0)
                    ->whereHas('product') // Solo inventarios que tengan producto
                    ->whereHas('location') // Solo inventarios que tengan ubicación
            )
            ->heading('Productos con Bajo Stock por Bodega')
            ->columns([
                Tables\Columns\TextColumn::make('product.name')
                    ->label('Producto')
                    ->searchable()
                    ->sortable()
                    ->wrap(),

                Tables\Columns\TextColumn::make('location.name')
                    ->label('Bodega / Sucursal')
                    ->badge()
                    ->color('info')
                    ->searchable(),

                Tables\Columns\TextColumn::make('stock')
                    ->label('Stock Actual')
                    ->numeric()
                    ->color('danger')
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('stock_minimo')
                    ->label('Stock Mínimo')
                    ->numeric()
                    ->color('warning'),

                // Columna adicional para mostrar qué tan crítico es el stock
                Tables\Columns\TextColumn::make('criticality')
                    ->label('Criticidad')
                    ->getStateUsing(function (Inventory $record): string {
                        $percentage = ($record->stock / $record->stock_minimo) * 100;
                        if ($percentage == 0) return 'Sin stock';
                        if ($percentage <= 25) return 'Crítico';
                        if ($percentage <= 50) return 'Bajo';
                        return 'Normal';
                    })
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Sin stock' => 'danger',
                        'Crítico' => 'danger',
                        'Bajo' => 'warning',
                        default => 'success',
                    }),
            ])
            ->defaultSort('stock', 'asc') // Ordenar por stock más bajo primero
            ->paginated([10, 25, 50])
            ->poll('30s'); // Actualizar cada 30 segundos
    }
}