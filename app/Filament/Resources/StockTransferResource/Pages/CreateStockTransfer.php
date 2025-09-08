<?php

namespace App\Filament\Resources\StockTransferResource\Pages;

use App\Filament\Resources\StockTransferResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use App\Models\Inventory;
use Illuminate\Support\Facades\DB;
use Exception;


class CreateStockTransfer extends CreateRecord
{
    protected static string $resource = StockTransferResource::class;
    protected function afterCreate(): void
    {
        $transfer = $this->getRecord();
        $items = $transfer->items;

        DB::transaction(function () use ($transfer, $items) {
            foreach ($items as $item) {
                // 1. Validar si hay stock suficiente en el origen
                $originInventory = Inventory::where('product_id', $item->product_id)
                    ->where('location_id', $transfer->origin_location_id)
                    ->first();
                
                if (!$originInventory || $originInventory->stock < $item->quantity) {
                    // Si no hay stock, se cancela toda la transacción
                    throw new Exception("No hay stock suficiente para el producto '{$item->product->name}' en la bodega de origen.");
                }

                // 2. Restar stock de la bodega de origen
                $originInventory->decrement('stock', $item->quantity);

                // 3. Sumar stock a la bodega de destino (crea el registro si no existe)
                Inventory::updateOrCreate(
                    [
                        'product_id' => $item->product_id,
                        'location_id' => $transfer->destination_location_id
                    ],
                    [
                        'stock' => DB::raw("stock + {$item->quantity}")
                    ]
                );
            }
        });
    }
}
