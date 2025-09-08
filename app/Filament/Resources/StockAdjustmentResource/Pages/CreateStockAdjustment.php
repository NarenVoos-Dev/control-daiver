<?php

namespace App\Filament\Resources\StockAdjustmentResource\Pages;

use App\Filament\Resources\StockAdjustmentResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use App\Models\Product;
use App\Models\StockMovement;
use App\Models\Inventory;
use Filament\Notifications\Notification;

class CreateStockAdjustment extends CreateRecord
{
    protected static string $resource = StockAdjustmentResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        return DB::transaction(function () use ($data) {
            
            // <<< PUNTO CLAVE: Aseguramos que las variables se definan aquí >>>
            $productId = $data['product_id'];
            $locationId = $data['location_id'];
            $quantity = (float)$data['quantity'];
            
            // Buscamos el registro de inventario específico
            $inventory = Inventory::where('product_id', $productId)
                ->where('location_id', $locationId)
                ->first();

            // 1. Validar si es una salida y hay suficiente stock EN ESA BODEGA
            if ($data['type'] === 'salida' && (!$inventory || $inventory->stock < $quantity)) {
                $stockActual = $inventory ? $inventory->stock : 0;
                throw new Exception("No se puede realizar la salida. Stock actual en esta bodega: {$stockActual}.");
            }

            // 2. Crear el registro del ajuste
            $adjustment = static::getModel()::create($data);

            // 3. Actualizar el stock en la tabla 'inventory'
            if ($data['type'] === 'entrada') {
                Inventory::updateOrCreate(
                    ['product_id' => $productId, 'location_id' => $locationId],
                    ['stock' => DB::raw("stock + {$quantity}")]
                );
            } else { // Salida
                $inventory->decrement('stock', $quantity);
            }
            
            // 4. Registrar el movimiento para auditoría
            StockMovement::create([
                'product_id' => $productId,
                'type' => $data['type'],
                'quantity' => $quantity,
                'source_type' => get_class($adjustment),
                'source_id' => $adjustment->id,
            ]);

            return $adjustment;
        });
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return '¡Ajuste de inventario registrado con éxito!';
    }
}