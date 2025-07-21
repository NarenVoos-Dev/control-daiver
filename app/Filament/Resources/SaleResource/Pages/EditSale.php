<?php

namespace App\Filament\Resources\SaleResource\Pages;

use App\Filament\Resources\SaleResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Filament\Notifications\Notification;

class EditSale extends EditRecord
{
    protected static string $resource = SaleResource::class;
    protected function getHeaderActions(): array { return [ Actions\DeleteAction::make(), ]; }
    protected function mutateFormDataBeforeFill(array $data): array { $data['items'] = $this->record->items->toArray(); return $data; }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        return DB::transaction(function () use ($record, $data) {
            
            // 1. Revertir el stock de los items antiguos, usando la conversión guardada
            foreach ($record->items as $oldItem) {
                $quantityToRevert = (float)$oldItem->quantity * (float)$oldItem->unitOfMeasure->conversion_factor;
                \App\Models\Product::where('id', $oldItem->product_id)->update(['stock' => DB::raw("stock + " . $quantityToRevert)]);
            }

            // 2. Validar el stock de los NUEVOS items
            foreach ($data['items'] as $newItemData) {
                $product = \App\Models\Product::findOrFail($newItemData['product_id']);
                $sellingUnit = \App\Models\UnitOfMeasure::findOrFail($newItemData['unit_of_measure_id']);
                $quantityToDeduct = (float)$newItemData['quantity'] * (float)$sellingUnit->conversion_factor;

                if ($product->stock < $quantityToDeduct) {
                    Notification::make()->title('Error de Stock')->body("No hay suficiente stock para {$product->name}.")->danger()->send();
                    $this->halt();
                }
            }

            $record->items()->delete();
            \App\Models\StockMovement::where('source_type', get_class($record))->where('source_id', $record->id)->delete();
            
            $record->update(['client_id' => $data['client_id'], 'date' => $data['date'], 'total' => $data['total']]);

            // 3. Crear los nuevos items y descontar el stock
            if (isset($data['items']) && is_array($data['items'])) {
                foreach ($data['items'] as $newItemData) {
                    $product = \App\Models\Product::findOrFail($newItemData['product_id']);
                    $sellingUnit = \App\Models\UnitOfMeasure::findOrFail($newItemData['unit_of_measure_id']);
                    
                    $record->items()->create($newItemData);
                    
                    $quantityToDeduct = (float)$newItemData['quantity'] * (float)$sellingUnit->conversion_factor;
                    
                    \App\Models\Product::where('id', $product->id)->update(['stock' => DB::raw("stock - " . $quantityToDeduct)]);
                    \App\Models\StockMovement::create(['product_id' => $product->id, 'type' => 'salida', 'quantity' => $quantityToDeduct, 'source_type' => get_class($record), 'source_id' => $record->id]);
                }
            }
            return $record;
        });
    }
}
