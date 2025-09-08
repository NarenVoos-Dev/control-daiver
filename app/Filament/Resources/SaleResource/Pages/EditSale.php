<?php

namespace App\Filament\Resources\SaleResource\Pages;

use App\Filament\Resources\SaleResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Filament\Notifications\Notification;
use App\Models\Inventory;

class EditSale extends EditRecord
{
    protected static string $resource = SaleResource::class;
    protected function getHeaderActions(): array { return [ Actions\DeleteAction::make(), ]; }
    protected function mutateFormDataBeforeFill(array $data): array { $data['items'] = $this->record->items->toArray(); return $data; }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        return DB::transaction(function () use ($record, $data) {
            $originalLocationId = $record->location_id;
            $newLocationId = $data['location_id'];
            
            // 1. Revertir el stock en la BODEGA ORIGINAL
            foreach ($record->items as $oldItem) {
                $quantityToRevert = (float)$oldItem->quantity * (float)$oldItem->unitOfMeasure->conversion_factor;
                Inventory::where('product_id', $oldItem->product_id)
                        ->where('location_id', $originalLocationId)
                        ->increment('stock', $quantityToRevert);
            }

            // 2. Validar el stock de los NUEVOS items en la NUEVA BODEGA
            foreach ($data['items'] as $newItemData) {
                $product = \App\Models\Product::findOrFail($newItemData['product_id']);
                $sellingUnit = \App\Models\UnitOfMeasure::findOrFail($newItemData['unit_of_measure_id']);
                $quantityToDeduct = (float)$newItemData['quantity'] * (float)$sellingUnit->conversion_factor;
                
                $inventory = Inventory::where('product_id', $product->id)->where('location_id', $newLocationId)->first();

                if (!$inventory || $inventory->stock < $quantityToDeduct) {
                    throw new \Exception("No hay stock para {$product->name} en la nueva bodega seleccionada.");
                }
            }

            $record->items()->delete();
            \App\Models\StockMovement::where('source_type', get_class($record))->where('source_id', $record->id)->delete();
            
            $record->update(['client_id' => $data['client_id'], 'date' => $data['date'], 'total' => $data['total']]);

            // 3. Crear los nuevos items y descontar el stock
            if (isset($data['items']) && is_array($data['items'])) {
                foreach ($data['items'] as $newItemData) {
                    $sellingUnit = \App\Models\UnitOfMeasure::findOrFail($newItemData['unit_of_measure_id']);
                    $quantityToDeduct = (float)$newItemData['quantity'] * (float)$sellingUnit->conversion_factor;
                    
                    $record->items()->create($newItemData);
                    
                    Inventory::where('product_id', $newItemData['product_id'])
                            ->where('location_id', $newLocationId)
                            ->decrement('stock', $quantityToDeduct);
                    
                    \App\Models\StockMovement::create([ /* ... tu lógica de StockMovement no cambia ... */]);
                }
            }
            return $record;
        });
    }
}
