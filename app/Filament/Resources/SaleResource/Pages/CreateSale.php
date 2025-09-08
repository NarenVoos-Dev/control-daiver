<?php

namespace App\Filament\Resources\SaleResource\Pages;

use App\Filament\Resources\SaleResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Filament\Actions\Action;
use App\Models\Client;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Filament\Notifications\Notification;

use App\Models\Product;
use App\Models\StockMovement;
use App\Models\UnitOfMeasure;
use App\Models\Inventory;

class CreateSale extends CreateRecord
{
    protected static string $resource = SaleResource::class;

    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('view', ['record' => $this->getRecord()]);
    }

    protected function getFormActions(): array
    {
        return [
            $this->getSaveFormAction(),
            $this->getCancelFormAction(),
        ];
    }

    protected function getSaveFormAction(): Action
    {
        // Atención: NO usamos ->submit('save') para que Filament ejecute requiresConfirmation correctamente
        return Action::make('save')
            ->label('Guardar Venta')
            ->keyBindings(['mod+s'])
            ->before(function () {
                // Antes de abrir confirmación/acción sólo hacemos logging y cálculos ligeros
                Log::info('=== ACCIÓN SAVE INICIADA (before) ===');
                $data = $this->form->getState();
                Log::info('Datos del formulario (before):', $data);

                // Si quieres, aquí puedes recalcular total automáticamente y rellenarlo en el form:
                if ((!isset($data['total']) || !is_numeric($data['total'])) && isset($data['items'])) {
                    $calculated = $this->calculateSaleTotal($data);
                    Log::info("Se recalculó total en before(): {$calculated}");
                    // Llenar el formulario con el total recalculado (opcional)
                    $this->form->fill(['total' => $calculated]);
                }
            })
            ->requiresConfirmation(function () {
                // Esta closure se ejecuta en el servidor para decidir si el modal de confirmación debe mostrarse.
                Log::info('=== EVALUANDO REQUIRES_CONFIRMATION ===');

                $data = $this->form->getState();
                Log::info('Datos en requiresConfirmation:', $data);

                // Si es venta al contado -> NO requiere confirmación
                $isCash = $data['is_cash'] ?? false;
                if ($isCash) {
                    Log::info('Venta al contado: no se requiere confirmación.');
                    return false;
                }

                // Si no hay cliente -> no requiere confirmación (o puedes cambiar la lógica)
                $clientId = $data['client_id'] ?? null;
                if (!$clientId) {
                    Log::info('No hay cliente asignado: no se requiere confirmación.');
                    return false;
                }

                $client = Client::find($clientId);
                if (!$client) {
                    Log::info("Cliente {$clientId} no encontrado: no se requiere confirmación.");
                    return false;
                }

                // Si cliente no tiene límite sensible -> no requiere confirmación
                if (!isset($client->credit_limit) || $client->credit_limit <= 0) {
                    Log::info("Cliente {$clientId} sin límite de crédito activo: no se requiere confirmación.");
                    return false;
                }

                // Calcular totales
                $newSaleTotal = $this->calculateSaleTotal($data);
                $currentDebt = method_exists($client, 'getCurrentDebt') ? $client->getCurrentDebt() : 0;
                $totalAfterSale = $currentDebt + $newSaleTotal;

                Log::info("Cliente {$clientId}: deuda actual={$currentDebt}, venta={$newSaleTotal}, totalDespues={$totalAfterSale}, limite={$client->credit_limit}");

                // Requiere confirmación sólo si sobrepasa el límite
                $exceedsLimit = $totalAfterSale > $client->credit_limit;
                Log::info("¿Excede límite? " . ($exceedsLimit ? 'SÍ' : 'NO'));

                return $exceedsLimit;
            })
            ->modalHeading('Límite de Crédito Excedido')
            ->modalDescription(function () {
                // Descripción del modal que verá el usuario
                $data = $this->form->getState();
                $client = Client::find($data['client_id'] ?? null);

                if (!$client) {
                    return 'No se pudo verificar el límite de crédito del cliente.';
                }

                $newSaleTotal = $this->calculateSaleTotal($data);
                $currentDebt = method_exists($client, 'getCurrentDebt') ? $client->getCurrentDebt() : 0;
                $totalAfterSale = $currentDebt + $newSaleTotal;

                return sprintf(
                    'El cliente tiene una deuda actual de $%s. Con esta venta de $%s, su deuda total sería de $%s, lo que excede su límite de crédito de $%s. ¿Desea continuar con la venta?',
                    number_format($currentDebt, 2),
                    number_format($newSaleTotal, 2),
                    number_format($totalAfterSale, 2),
                    number_format($client->credit_limit, 2)
                );
            })
            ->modalSubmitActionLabel('Sí, continuar con la venta')
            ->modalCancelActionLabel('Cancelar')
            ->action(function () {
                // Esta acción **solo** se ejecuta si:
                // - No se requería confirmación, o
                // - Se requirió y el usuario confirmó en el modal
                Log::info('=== EJECUTANDO ACCIÓN FINAL (action) ===');

                $data = $this->form->getState();
                Log::info('Datos del formulario (action):', $data);

                try {
                    $record = $this->handleRecordCreation($data);
                    $this->record = $record;

                    Log::info('Record creado exitosamente (action):', ['id' => $record->id]);

                    Notification::make()
                        ->title('Venta creada exitosamente')
                        ->success()
                        ->send();

                    $this->redirect($this->getRedirectUrl());
                } catch (\Exception $e) {
                    Log::error('Error al crear la venta (action):', [
                        'mensaje' => $e->getMessage(),
                        'archivo' => $e->getFile(),
                        'linea' => $e->getLine()
                    ]);

                    Notification::make()
                        ->title('Error al crear la venta')
                        ->body($e->getMessage())
                        ->danger()
                        ->send();
                }
            });
    }

    protected function calculateSaleTotal(array $data): float
    {
        $total = 0;

        Log::info('=== CALCULANDO TOTAL DE VENTA ===');

        if (isset($data['total']) && is_numeric($data['total'])) {
            $total = (float)$data['total'];
            Log::info("Usando total pre-calculado: {$total}");
            return $total;
        }

        Log::info('Calculando desde items...');

        if (isset($data['items']) && is_array($data['items'])) {
            foreach ($data['items'] as $index => $item) {
                $quantity = (float)($item['quantity'] ?? 0);
                $price = (float)($item['price'] ?? 0);
                $taxRate = (float)($item['tax_rate'] ?? 0);

                $subtotal = $quantity * $price;
                $tax = $subtotal * ($taxRate / 100);
                $itemTotal = $subtotal + $tax;

                Log::info("Item {$index}:", [
                    'quantity' => $quantity,
                    'price' => $price,
                    'tax_rate' => $taxRate,
                    'subtotal' => $subtotal,
                    'tax' => $tax,
                    'total' => $itemTotal
                ]);

                $total += $itemTotal;
            }
        }

        Log::info("Total calculado final: {$total}");
        return $total;
    }

    protected function handleRecordCreation(array $data): Model
    {
        return DB::transaction(function () use ($data) {
            Log::info('=== INICIANDO CREACIÓN DE RECORD ===');
            $locationId = $data['location_id'];

             // 1. Validar stock ANTES de crear
            foreach ($data['items'] as $itemData) {
                $product = Product::findOrFail($itemData['product_id']);
                $sellingUnit = UnitOfMeasure::findOrFail($itemData['unit_of_measure_id']);
                $quantityToDeduct = (float)$itemData['quantity'] * (float)$sellingUnit->conversion_factor;
                
                // Buscamos el stock en la bodega correcta
                $inventory = Inventory::where('product_id', $product->id)->where('location_id', $locationId)->first();

                if (!$inventory || $inventory->stock < $quantityToDeduct) {
                    throw new \Exception("No hay stock para {$product->name} en la bodega seleccionada.");
                }
            }

            // Estado según condición de pago
            $isCash = $data['is_cash'];
            $total = $data['total'];
            $data['status'] = $isCash ? 'Pagada' : 'Pendiente';
            $data['pending_amount'] = $isCash ? 0 : $total;
            Log::info("Estado asignado: {$data['status']} (is_cash: " . ($data['is_cash'] ? 'true' : 'false') . ")");

            // Crear la venta
            $sale = static::getModel()::create($data);
            Log::info('Venta creada (handleRecordCreation):', ['id' => $sale->id, 'status' => $sale->status, 'is_cash' => $sale->is_cash]);

            // Procesar items
            if (isset($data['items']) && is_array($data['items'])) {
                foreach ($data['items'] as $itemData) {
                    $sellingUnit = UnitOfMeasure::findOrFail($itemData['unit_of_measure_id']);
                    $quantityToDeduct = (float)$itemData['quantity'] * (float)$sellingUnit->conversion_factor;

                    $sale->items()->create($itemData);

                    Inventory::where('product_id', $itemData['product_id'])
                                ->where('location_id', $locationId)
                                ->decrement('stock', $quantityToDeduct);

                    StockMovement::create([
                        'product_id' => $product->id,
                        'type' => 'salida',
                        'quantity' => $quantityToDeduct,
                        'source_type' => get_class($sale),
                        'source_id' => $sale->id,
                    ]);
                }
            }

            return $sale;
        });
    }
}
