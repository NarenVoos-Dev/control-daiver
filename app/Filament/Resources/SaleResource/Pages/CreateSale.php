<?php

namespace App\Filament\Resources\SaleResource\Pages;

use App\Filament\Resources\SaleResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Filament\Notifications\Notification;

class CreateSale extends CreateRecord
{
    protected static string $resource = SaleResource::class;

    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('view', ['record' => $this->getRecord()]);
    }

    protected function handleRecordCreation(array $data): Model
    {
        
        return DB::transaction(function () use ($data) {
            
            // 1. Validar el stock ANTES de crear nada, usando la conversión de unidades
            foreach ($data['items'] as $itemData) {
                $product = \App\Models\Product::findOrFail($itemData['product_id']);
                $sellingUnit = \App\Models\UnitOfMeasure::findOrFail($itemData['unit_of_measure_id']);
                $quantityToDeduct = (float)$itemData['quantity'] * (float)$sellingUnit->conversion_factor;
                if ($product->stock < $quantityToDeduct) {
                    Notification::make()
                        ->title('Error de Stock')
                        ->body("No hay suficiente stock para {$product->name}. Necesitas: {$quantityToDeduct} {$product->unitOfMeasure->abbreviation}, tienes: {$product->stock} {$product->unitOfMeasure->abbreviation}.")
                        ->danger()->send();
                    $this->halt();
                }
            }


            $sale = static::getModel()::create([
                'business_id' => $data['business_id'], 
                'client_id' => $data['client_id'], 
                'date' => $data['date'],
                'subtotal' => $data['subtotal'],
                'tax' => $data['tax'],
                'total' => $data['total']
            ]);


            // 2. Procesar cada item, guardando el item y descontando el stock convertido
            if (isset($data['items']) && is_array($data['items'])) {
                foreach ($data['items'] as $itemData) {
                    $product = \App\Models\Product::findOrFail($itemData['product_id']);
                    $sellingUnit = \App\Models\UnitOfMeasure::findOrFail($itemData['unit_of_measure_id']);

                    $sale->items()->create($itemData); // Guarda el item tal como se vendió
                    
                    $quantityToDeduct = (float)$itemData['quantity'] * (float)$sellingUnit->conversion_factor;

                    \App\Models\Product::where('id', $product->id)->update([
                        'stock' => DB::raw("stock - " . $quantityToDeduct),
                    ]);

                    \App\Models\StockMovement::create(['product_id' => $product->id, 'type' => 'salida', 'quantity' => $quantityToDeduct, 'source_type' => get_class($sale), 'source_id' => $sale->id]);
                }
            }
            return $sale;
        });
    }
}

