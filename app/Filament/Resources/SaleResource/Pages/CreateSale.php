<?php

namespace App\Filament\Resources\SaleResource\Pages;

use App\Filament\Resources\SaleResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use App\Models\Client;
use Illuminate\Support\Facades\DB;
use App\Models\Product;
use App\Models\StockMovement;
use App\Models\UnitOfMeasure;
use App\Models\Inventory;
use Filament\Notifications\Notification;
use Filament\Actions\Action;
use Illuminate\Support\Facades\Log;

class CreateSale extends CreateRecord
{
    protected static string $resource = SaleResource::class;
    public bool $isForcingCreation = false;
    public array $pendingData = []; // Para guardar los datos cuando se detiene la creación
    
    protected function beforeCreate(): void
    {
        Log::info('=== beforeCreate() INICIADO ===');
        
        if ($this->isForcingCreation) {
            Log::info('FORZANDO CREACIÓN - Saltando validación');
            return;
        }
        
        $data = $this->form->getState();
        Log::info('Datos del formulario en beforeCreate:', $data);

        // Guardar los datos por si necesitamos usarlos después
        $this->pendingData = $data;

        // Si es venta de contado, no se valida el crédito y se permite la creación.
        $isCash = filter_var($data['is_cash'] ?? false, FILTER_VALIDATE_BOOLEAN);
        Log::info('Tipo de pago detectado:', [
            'is_cash_raw' => $data['is_cash'] ?? 'NO_EXISTE',
            'is_cash_processed' => $isCash
        ]);

        if ($isCash) {
            Log::info('ES CONTADO - Permitiendo creación');
            return;
        }
        
        $client = Client::find($data['client_id'] ?? null);
        Log::info('Cliente encontrado:', [
            'client_id' => $data['client_id'] ?? 'NO_ID',
            'cliente_existe' => $client ? 'SI' : 'NO',
            'cliente_nombre' => $client->name ?? 'N/A'
        ]);

        // Solo se valida si hay un cliente con un límite de crédito activo.
        if ($client && isset($client->credit_limit) && $client->credit_limit > 0) {
            $newSaleTotal = $this->calculateSaleTotal($data);
            $currentDebt = method_exists($client, 'getCurrentDebt') ? $client->getCurrentDebt() : 0;
            $totalAfterSale = $currentDebt + $newSaleTotal;

            Log::info('Cálculos de crédito:', [
                'limite_credito' => $client->credit_limit,
                'deuda_actual' => $currentDebt,
                'total_venta' => $newSaleTotal,
                'total_despues' => $totalAfterSale,
                'excede_limite' => $totalAfterSale > $client->credit_limit
            ]);

            // Si se excede el límite, se detiene la creación y se muestra una notificación con una acción.
            if ($totalAfterSale > $client->credit_limit) {
                Log::info('EXCEDE LÍMITE - Mostrando notificación');
                
                $message = sprintf(
                    'El cliente "%s" excede su límite de crédito de $%s. Deuda actual: $%s. Con esta venta: $%s. Total sería: $%s.',
                    $client->name,
                    number_format($client->credit_limit, 2),
                    number_format($currentDebt, 2),
                    number_format($newSaleTotal, 2),
                    number_format($totalAfterSale, 2)
                );

                Notification::make()
                    ->title('⚠️ Límite de Crédito Excedido')
                    ->body($message)
                    ->danger()
                    ->persistent()
                    ->actions([
                        \Filament\Notifications\Actions\Action::make('forceCreate')
                            ->label('✅ Forzar Venta a Crédito')
                            ->color('danger')
                            ->button()
                            ->dispatch('forceCreateSale'),
                        \Filament\Notifications\Actions\Action::make('cancel')
                            ->label('❌ Cancelar')
                            ->color('gray')
                            ->button()
                            ->action(function () {
                                Log::info('=== BOTÓN CANCELAR CLICKEADO ===');
                                $this->cancelPendingSale();
                            })
                    ])
                    ->send();

                Log::info('Notificación enviada - Deteniendo proceso');
                // Detiene el proceso de creación normal.
                $this->halt();
            }
        }

        Log::info('beforeCreate completado - Continuando con creación normal');
    }

    /**
     * Este método se ejecuta cuando el usuario hace clic en "Forzar Venta"
     */
    #[On('forceCreateSale')]
    public function forceCreate(): void
    {
        Log::info('=== forceCreate() INICIADO ===');
        Log::info('Datos pendientes disponibles:', $this->pendingData);
        
        try {
            // Validar que tenemos datos pendientes
            if (empty($this->pendingData)) {
                Log::error('ERROR: No hay datos pendientes para crear la venta');
                Notification::make()
                    ->title('Error')
                    ->body('No se pueden recuperar los datos de la venta. Por favor, intenta de nuevo.')
                    ->danger()
                    ->send();
                return;
            }

            // Marcar que estamos forzando la creación
            $this->isForcingCreation = true;
            
            // Forzar que sea venta a crédito
            $this->pendingData['is_cash'] = false;
            
            Log::info('Creando venta forzada con datos:', $this->pendingData);
            
            // Crear la venta directamente
            $sale = $this->handleRecordCreation($this->pendingData);
            
            Log::info('Venta creada exitosamente:', ['sale_id' => $sale->id]);
            
            // Limpiar datos pendientes
            $this->pendingData = [];
            $this->isForcingCreation = false;
            
            // Notificación de éxito
            Notification::make()
                ->title('✅ Venta Creada')
                ->body('La venta a crédito ha sido creada exitosamente a pesar de exceder el límite.')
                ->success()
                ->send();
            
            // Redirigir al registro creado
            $this->redirect(static::getResource()::getUrl('view', ['record' => $sale]));
            
        } catch (\Exception $e) {
            Log::error('ERROR al forzar creación:', [
                'mensaje' => $e->getMessage(),
                'archivo' => $e->getFile(),
                'linea' => $e->getLine()
            ]);
            
            Notification::make()
                ->title('Error al Crear Venta')
                ->body('Ocurrió un error: ' . $e->getMessage())
                ->danger()
                ->send();
        }
    }

    /**
     * Método para cancelar la venta pendiente
     */
    public function cancelPendingSale(): void
    {
        Log::info('=== cancelPendingSale() INICIADO ===');
        
        // Limpiar datos pendientes
        $this->pendingData = [];
        $this->isForcingCreation = false;
        
        Notification::make()
            ->title('Venta Cancelada')
            ->body('La venta ha sido cancelada.')
            ->warning()
            ->send();
            
        Log::info('Venta cancelada - Datos limpiados');
    }

    protected function handleRecordCreation(array $data): Model
    {
        Log::info('=== handleRecordCreation() INICIADO ===');
        Log::info('¿Es creación forzada?', ['is_forcing' => $this->isForcingCreation]);
        
        return DB::transaction(function () use ($data) {
            $locationId = $data['location_id'];

            Log::info('Validando stock para ' . count($data['items']) . ' items');
            
            // Validación de stock
            foreach ($data['items'] as $index => $itemData) {
                $product = Product::findOrFail($itemData['product_id']);
                $sellingUnit = UnitOfMeasure::findOrFail($itemData['unit_of_measure_id']);
                $quantityToDeduct = (float)$itemData['quantity'] * (float)$sellingUnit->conversion_factor;
                $inventory = Inventory::where('product_id', $product->id)->where('location_id', $locationId)->first();
                
                Log::info("Stock item {$index} - {$product->name}:", [
                    'requerido' => $quantityToDeduct,
                    'disponible' => $inventory ? $inventory->stock : 0,
                    'suficiente' => $inventory && $inventory->stock >= $quantityToDeduct
                ]);
                
                if (!$inventory || $inventory->stock < $quantityToDeduct) {
                    throw new \Exception("No hay stock para {$product->name} en la bodega seleccionada.");
                }
            }

            // Lógica de creación de la venta
            $isCash = filter_var($data['is_cash'] ?? false, FILTER_VALIDATE_BOOLEAN);
            $total = $this->calculateSaleTotal($data);
            $subtotal = $this->calculateSaleSubtotal($data);
            
            $data['total'] = $total;
            $data['subtotal'] = $subtotal;
            $data['tax'] = $total - $subtotal;
            $data['status'] = $isCash ? 'Pagada' : 'Pendiente';
            $data['pending_amount'] = $isCash ? 0 : $total;
            
            Log::info('Datos finales de venta:', [
                'is_cash' => $data['is_cash'],
                'total' => $data['total'],
                'status' => $data['status'],
                'pending_amount' => $data['pending_amount']
            ]);
            
            $sale = static::getModel()::create($data);
            Log::info('Registro de venta creado:', ['id' => $sale->id]);

            // Procesar items y actualizar inventario
            foreach ($data['items'] as $index => $itemData) {
                $sellingUnit = UnitOfMeasure::findOrFail($itemData['unit_of_measure_id']);
                $quantityToDeduct = (float)$itemData['quantity'] * (float)$sellingUnit->conversion_factor;
                
                Log::info("Procesando item {$index}");
                
                $sale->items()->create($itemData);
                
                Inventory::where('product_id', $itemData['product_id'])
                         ->where('location_id', $locationId)
                         ->decrement('stock', $quantityToDeduct);
                         
                StockMovement::create([
                    'product_id' => $itemData['product_id'],
                    'type' => 'salida',
                    'quantity' => $quantityToDeduct,
                    'source_type' => get_class($sale),
                    'source_id' => $sale->id,
                ]);
            }
            
            Log::info('Venta completada exitosamente');
            return $sale;
        });
    }
    
    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('view', ['record' => $this->getRecord()]);
    }

    protected function calculateSaleTotal(array $data): float
    {
        $total = 0.0;
        if (isset($data['items']) && is_array($data['items'])) {
            foreach ($data['items'] as $item) {
                $subtotal = (float)($item['quantity'] ?? 0) * (float)($item['price'] ?? 0);
                $tax = $subtotal * ((float)($item['tax_rate'] ?? 0) / 100);
                $total += $subtotal + $tax;
            }
        }
        return $total;
    }

    protected function calculateSaleSubtotal(array $data): float
    {
        $subtotal = 0.0;
        if (isset($data['items']) && is_array($data['items'])) {
            foreach ($data['items'] as $item) {
                $subtotal += (float)($item['quantity'] ?? 0) * (float)($item['price'] ?? 0);
            }
        }
        return $subtotal;
    }
}