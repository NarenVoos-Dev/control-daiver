<?php

namespace App\Filament\Resources\SaleResource\Pages;

use App\Filament\Resources\SaleResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use App\Models\Client;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Product;
use App\Models\StockMovement;
use App\Models\UnitOfMeasure;
use App\Models\Inventory;
use Filament\Notifications\Notification;

class CreateSale extends CreateRecord
{
    protected static string $resource = SaleResource::class;

    protected function beforeCreate(): void
    {
        Log::info('=== beforeCreate() LLAMADO ===');
        
        $data = $this->form->getState();
        Log::info('=== DEBUG: TODOS LOS DATOS DEL FORMULARIO ===');
        Log::info('Datos completos:', $data);
        
        // DEBUG EXTENDIDO PARA ENCONTRAR EL CAMPO CORRECTO
        Log::info('=== BÚSQUEDA DEL CAMPO is_cash ===');
        foreach ($data as $key => $value) {
            if (stripos($key, 'cash') !== false || stripos($key, 'contado') !== false || stripos($key, 'payment') !== false) {
                Log::info("POSIBLE CAMPO ENCONTRADO: {$key}", [
                    'valor' => $value,
                    'tipo' => gettype($value)
                ]);
            }
        }
        
        // MÚLTIPLES INTENTOS DE CAPTURAR EL CAMPO
        $possibleFields = [
            'is_cash',
            'payment_type',
            'tipo_pago',
            'is_contado',
            'cash',
            'contado',
            'payment_method'
        ];
        
        $isCash = null;
        $fieldFound = null;
        
        foreach ($possibleFields as $field) {
            if (isset($data[$field])) {
                $isCash = $data[$field];
                $fieldFound = $field;
                Log::info("CAMPO ENCONTRADO: {$field}", [
                    'valor' => $isCash,
                    'tipo' => gettype($isCash)
                ]);
                break;
            }
        }
        
        if ($fieldFound === null) {
            Log::warning('¡NINGÚN CAMPO DE TIPO DE PAGO ENCONTRADO!');
            Log::info('Campos disponibles en $data:', array_keys($data));
            // Asumimos que es crédito si no encontramos el campo
            $isCash = false;
        }

        // NORMALIZACIÓN DEL VALOR
        $isCashNormalized = $this->normalizeCashValue($isCash);
        
        Log::info('RESULTADO DE NORMALIZACIÓN:', [
            'campo_usado' => $fieldFound,
            'valor_original' => $isCash,
            'valor_normalizado' => $isCashNormalized,
            'es_contado' => $isCashNormalized === true
        ]);

        // Si es venta de contado, continuar sin validación
        if ($isCashNormalized === true) {
            Log::info('RESULTADO: Es venta de CONTADO - NO requiere validación');
            return;
        }
        
        Log::info('RESULTADO: Es venta a CRÉDITO - validando límite...');
        
        // Validar límite de crédito para ventas a crédito
        $clientId = $data['client_id'] ?? null;
        
        if (!$clientId) {
            Log::info('RESULTADO: Sin cliente - continuar');
            return;
        }
        
        $client = Client::find($clientId);
        
        if (!$client) {
            Log::info('RESULTADO: Cliente no encontrado - continuar');
            return;
        }

        // Solo validar si hay cliente con límite de crédito
        if (!isset($client->credit_limit) || $client->credit_limit <= 0) {
            Log::info('RESULTADO: Cliente sin límite de crédito válido - continuar');
            return;
        }

        $newSaleTotal = $this->calculateSaleTotal($data);
        $currentDebt = method_exists($client, 'getCurrentDebt') ? $client->getCurrentDebt() : 0;
        $totalAfterSale = $currentDebt + $newSaleTotal;

        if ($totalAfterSale > $client->credit_limit) {
            Log::info('RESULTADO: EXCEDE límite de crédito - BLOQUEANDO venta');
            
            $message = sprintf(
                'El cliente "%s" tiene una deuda actual de $%s. Con esta venta de $%s, su deuda total sería de $%s, lo que excede su límite de crédito de $%s.',
                $client->name ?? 'N/A',
                number_format($currentDebt, 2),
                number_format($newSaleTotal, 2),
                number_format($totalAfterSale, 2),
                number_format($client->credit_limit, 2)
            );

            Notification::make()
                ->title('Límite de Crédito Excedido')
                ->body($message)
                ->danger()
                ->persistent()
                ->send();

            $this->halt();
        }

        Log::info('RESULTADO: Dentro del límite de crédito - continuar');
    }

    /**
     * Normaliza el valor del campo cash/contado para manejar diferentes formatos
     */
    private function normalizeCashValue($value): bool
    {
        Log::info('Normalizando valor de cash:', [
            'valor_recibido' => $value,
            'tipo' => gettype($value)
        ]);

        // Si es null o no existe, asumimos crédito
        if ($value === null || $value === '') {
            return false;
        }

        // Si ya es boolean
        if (is_bool($value)) {
            return $value;
        }

        // Si es string
        if (is_string($value)) {
            $value = strtolower(trim($value));
            return in_array($value, ['1', 'true', 'yes', 'si', 'contado', 'cash'], true);
        }

        // Si es numérico
        if (is_numeric($value)) {
            return (int)$value === 1;
        }

        // Si es array (por ejemplo, de un select)
        if (is_array($value)) {
            return !empty($value) && (
                in_array('1', $value) || 
                in_array('true', $value) || 
                in_array('contado', $value)
            );
        }

        // Por defecto, crédito
        return false;
    }

    protected function handleRecordCreation(array $data): Model
    {
        Log::info('=== INICIANDO CREACIÓN DE VENTA ===');
        
        return DB::transaction(function () use ($data) {
            $locationId = $data['location_id'];

            // Validación de stock
            foreach ($data['items'] as $index => $itemData) {
                $product = Product::findOrFail($itemData['product_id']);
                $sellingUnit = UnitOfMeasure::findOrFail($itemData['unit_of_measure_id']);
                $quantityToDeduct = (float)$itemData['quantity'] * (float)$sellingUnit->conversion_factor;
                $inventory = Inventory::where('product_id', $product->id)->where('location_id', $locationId)->first();
                
                if (!$inventory || $inventory->stock < $quantityToDeduct) {
                    throw new \Exception("No hay stock para {$product->name} en la bodega seleccionada.");
                }
            }

            // USAR LA MISMA LÓGICA PARA DETECTAR EL CAMPO
            $possibleFields = ['is_cash', 'payment_type', 'tipo_pago', 'is_contado', 'cash', 'contado', 'payment_method'];
            $isCash = false; // Por defecto crédito
            
            foreach ($possibleFields as $field) {
                if (isset($data[$field])) {
                    $isCash = $this->normalizeCashValue($data[$field]);
                    Log::info("CREACIÓN: Usando campo {$field} con valor normalizado:", ['is_cash' => $isCash]);
                    break;
                }
            }
            
            $total = $this->calculateSaleTotal($data);
            $subtotal = $this->calculateSaleSubtotal($data);
            $tax = $total - $subtotal;
            
            // FORZAR EL CAMPO is_cash EN LOS DATOS PARA GUARDAR
            $data['is_cash'] = $isCash;
            $data['total'] = $total;
            $data['subtotal'] = $subtotal;
            $data['tax'] = $tax;
            $data['status'] = $isCash ? 'Pagada' : 'Pendiente';
            $data['pending_amount'] = $isCash ? 0 : $total;
            
            Log::info('Datos finales para crear venta:', [
                'is_cash' => $data['is_cash'],
                'status' => $data['status'],
                'pending_amount' => $data['pending_amount'],
                'total' => $data['total']
            ]);
            
            $sale = static::getModel()::create($data);

            // Crear items y actualizar inventario
            foreach ($data['items'] as $itemData) {
                $sellingUnit = UnitOfMeasure::findOrFail($itemData['unit_of_measure_id']);
                $quantityToDeduct = (float)$itemData['quantity'] * (float)$sellingUnit->conversion_factor;
                
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
            
            Log::info('=== VENTA CREADA EXITOSAMENTE ===');
            return $sale;
        });
    }
    
    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('view', ['record' => $this->getRecord()]);
    }

    protected function calculateSaleTotal(array $data): float
    {
        $total = 0;
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
        $subtotal = 0;
        if (isset($data['items']) && is_array($data['items'])) {
            foreach ($data['items'] as $item) {
                $subtotal += (float)($item['quantity'] ?? 0) * (float)($item['price'] ?? 0);
            }
        }
        return $subtotal;
    }
}