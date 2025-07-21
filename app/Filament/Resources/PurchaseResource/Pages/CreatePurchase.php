<?php

namespace App\Filament\Resources\PurchaseResource\Pages;

use App\Filament\Resources\PurchaseResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use App\Models\Product;
use App\Models\StockMovement;
use App\Models\UnitOfMeasure;

class CreatePurchase extends CreateRecord
{
    protected static string $resource = PurchaseResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        
        return DB::transaction(function () use ($data) {
            
            $purchase = static::getModel()::create([
                'business_id' => $data['business_id'],
                'supplier_id' => $data['supplier_id'],
                'date' => $data['date'],
                'total' => $data['total'],
            ]);

            if (isset($data['items']) && is_array($data['items'])) {
                foreach ($data['items'] as $itemData) {
                    
                    // --- CAMBIO CLAVE: Usamos findOrFail para obtener un error claro ---
                    // Si el producto o la unidad no se encuentran, la transacción fallará
                    // y se revertirá, en lugar de fallar silenciosamente.
                    $product = Product::findOrFail($itemData['product_id']);
                    $unit = UnitOfMeasure::findOrFail($itemData['unit_of_measure_id']);
                    
                    // El if($product && $unit) ya no es necesario gracias a findOrFail.

                    // Primero, crear el item de la compra
                    $purchase->items()->create($itemData);
                    
                    // Segundo, calcular la cantidad a añadir
                    $quantityInBaseUnits = (float)$itemData['quantity'] * (float)$unit->conversion_factor;
                    
                    // Tercero, calcular el nuevo costo por unidad base
                    $costPerBaseUnit = ((float)$unit->conversion_factor > 0) 
                        ? (float)$itemData['price'] / (float)$unit->conversion_factor 
                        : 0;

                    // Actualización Atómica y Directa
                    Product::where('id', $product->id)->update([
                        'stock' => DB::raw("stock + {$quantityInBaseUnits}"),
                        'cost' => $costPerBaseUnit,
                    ]);

                    // Cuarto, registrar el movimiento de stock para auditoría
                    StockMovement::create([
                        'product_id' => $product->id,
                        'type' => 'entrada',
                        'quantity' => $quantityInBaseUnits,
                        'source_type' => get_class($purchase),
                        'source_id' => $purchase->id,
                    ]);
                }
            }
            
            return $purchase;
        });
    }

}
