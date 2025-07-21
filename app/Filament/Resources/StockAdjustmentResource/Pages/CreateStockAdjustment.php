<?php

namespace App\Filament\Resources\StockAdjustmentResource\Pages;

use App\Filament\Resources\StockAdjustmentResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use App\Models\Product;
use App\Models\StockMovement;
use Filament\Notifications\Notification;

class CreateStockAdjustment extends CreateRecord
{
    protected static string $resource = StockAdjustmentResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        return DB::transaction(function () use ($data) {
            
            $product = Product::findOrFail($data['product_id']);
            $quantity = (float)$data['quantity'];
            
            // 1. Validar si es una salida y hay suficiente stock
            if ($data['type'] === 'salida' && $product->stock < $quantity) {
                Notification::make()
                    ->title('Error de Stock')
                    ->body("No se puede realizar la salida. Stock actual de {$product->name}: {$product->stock}.")
                    ->danger()
                    ->send();
                $this->halt();
            }

            // 2. Crear el registro del ajuste
            $adjustment = static::getModel()::create($data);

            // 3. Actualizar el stock del producto
            $updateExpression = ($data['type'] === 'entrada')
                ? "stock + {$quantity}"
                : "stock - {$quantity}";

            Product::where('id', $product->id)->update([
                'stock' => DB::raw($updateExpression)
            ]);
            
            // 4. Registrar el movimiento para auditoría
            StockMovement::create([
                'product_id' => $product->id,
                'type' => $data['type'],
                'quantity' => $quantity,
                'source_type' => get_class($adjustment),
                'source_id' => $adjustment->id,
            ]);

            return $adjustment;
        });
    }
}