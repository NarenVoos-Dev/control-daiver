<?php

namespace App\Filament\Resources\PurchaseResource\Pages;

use App\Filament\Resources\PurchaseResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use App\Models\Product;
use App\Models\StockMovement;
use App\Models\UnitOfMeasure;

class EditPurchase extends EditRecord
{
    protected static string $resource = PurchaseResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    /**
     * Este método se ejecuta antes de que el formulario se llene con datos.
     * Aquí cargamos manualmente los items de la compra.
     */
     protected function mutateFormDataBeforeFill(array $data): array
    {
        // Carga los items relacionados a la compra y los convierte a un array.
        $data['items'] = $this->record->items->toArray();
        return $data;
    }

    /**
     * Este método se ejecuta cuando se guardan los cambios.
     * Aquí implementamos la lógica de actualización de stock.
     */
    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        return DB::transaction(function () use ($record, $data) {
            
            // 1. Revertir el stock de los items antiguos
            foreach ($record->items as $oldItem) {
                $product = Product::find($oldItem->product_id);
                if ($product) {
                    $quantityInBaseUnits = (float)$oldItem->quantity * (float)$oldItem->unitOfMeasure->conversion_factor;
                    Product::where('id', $product->id)->update([
                        'stock' => DB::raw("stock - {$quantityInBaseUnits}")
                    ]);
                }
            }

            // 2. Borrar los items antiguos y sus movimientos de stock
            $record->items()->delete();
            StockMovement::where('source_type', get_class($record))
                         ->where('source_id', $record->id)
                         ->delete();

            // 3. Actualizar la compra principal
            $record->update([
                'supplier_id' => $data['supplier_id'],
                'date' => $data['date'],
                'total' => $data['total'],
            ]);

            // 4. Crear los nuevos items y actualizar el stock (misma lógica que en Create)
            if (isset($data['items']) && is_array($data['items'])) {
                foreach ($data['items'] as $newItemData) {
                    $product = Product::findOrFail($newItemData['product_id']);
                    $unit = UnitOfMeasure::findOrFail($newItemData['unit_of_measure_id']);
                    
                    $record->items()->create($newItemData);

                    $quantityInBaseUnits = (float)$newItemData['quantity'] * (float)$unit->conversion_factor;
                    $costPerBaseUnit = ((float)$unit->conversion_factor > 0) 
                        ? (float)$newItemData['price'] / (float)$unit->conversion_factor 
                        : 0;
                    
                    Product::where('id', $product->id)->update([
                        'stock' => DB::raw("stock + {$quantityInBaseUnits}"),
                        'cost' => $costPerBaseUnit,
                    ]);

                    StockMovement::create([
                        'product_id' => $product->id,
                        'type' => 'entrada',
                        'quantity' => $quantityInBaseUnits,
                        'source_type' => get_class($record),
                        'source_id' => $record->id,
                    ]);
                }
            }
            
            return $record;
        });
    }
}

