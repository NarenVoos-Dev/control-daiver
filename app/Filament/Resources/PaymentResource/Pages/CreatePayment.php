<?php

namespace App\Filament\Resources\PaymentResource\Pages;

use App\Filament\Resources\PaymentResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Filament\Forms;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Client;
use App\Models\Sale;
use App\Models\Payment;
use Filament\Notifications\Notification;

class CreatePayment extends CreateRecord
{
    protected static string $resource = PaymentResource::class;

    /**
     * Override del método handleRecordCreation para manejar la lógica personalizada
     */
    protected function handleRecordCreation(array $data): Model
    {
        // Si se seleccionó una factura específica
        if (!empty($data['sale_id'])) {
            return $this->applyPaymentToSingleSale($data);
        } else {
            // Si no se seleccionó factura, aplicar a las más antiguas
            return $this->applyPaymentToOldestSales($data);
        }
    }

    /**
     * Aplica el pago a una factura específica seleccionada
     */
    private function applyPaymentToSingleSale(array $data): Model
    {
        $sale = Sale::findOrFail($data['sale_id']);
        $clientPayment = floatval($data['amount']); // Lo que realmente pagó el cliente
        $saleDebt = $sale->pending_amount; // Lo que debe la factura
        
        if ($clientPayment > $saleDebt) {
            // El cliente pagó más de lo que debe la factura
            $surplus = $clientPayment - $saleDebt;
            
            // Preguntar al usuario qué hacer con el excedente
            $this->js('
                if (confirm("La factura debe $' . number_format($saleDebt, 2) . ' pero pagó $' . number_format($clientPayment, 2) . '. ¿Desea aplicar el excedente de $' . number_format($surplus, 2) . ' a otras deudas del cliente?")) {
                    window.applySurplusToOtherDebts = true;
                } else {
                    window.applySurplusToOtherDebts = false;
                }
            ');
            
            // Por ahora aplicamos el excedente, pero puedes ajustar esta lógica
            return $this->paySpecificSaleAndApplySurplus($data, $sale, $surplus);
        }

        // Si el pago es exacto o menor, se aplica directamente a la factura
        return $this->applyPaymentToSpecificSale($data, $sale, $clientPayment);
    }

    /**
     * Aplica el pago específicamente a una factura seleccionada
     */
    private function applyPaymentToSpecificSale(array $data, Sale $sale, float $clientPayment): Model
    {
        return DB::transaction(function () use ($data, $sale, $clientPayment) {
            $saleDebt = $sale->pending_amount;
            $amountToRecord = min($clientPayment, $saleDebt); // Registra lo que realmente se aplicó
            
            // Crear el registro de pago
            $payment = static::getModel()::create([
                'business_id' => $data['business_id'],
                'client_id' => $data['client_id'],
                'sale_id' => $sale->id,
                'amount' => $amountToRecord,
                'payment_date' => $data['payment_date'],
            ]);
            
            // Actualizar la factura
            $sale->pending_amount -= $amountToRecord;
            if ($sale->pending_amount <= 0.01) {
                $sale->pending_amount = 0;
                $sale->status = 'Pagada';
            }
            $sale->save();

            // Calcular vuelto si lo hay
            $change = $clientPayment - $amountToRecord;
            
            if ($change > 0) {
                Notification::make()
                    ->title('Pago Registrado con Vuelto')
                    ->body("Factura pagada. Debe devolver un vuelto de $" . number_format($change, 2))
                    ->warning()
                    ->send();
            } else {
                Notification::make()
                    ->title('Abono a Factura Específica')
                    ->body("Se registró el abono de $" . number_format($amountToRecord, 2) . " a la factura seleccionada.")
                    ->success()
                    ->send();
            }
            
            return $payment;
        });
    }

    /**
     * Paga completamente la factura seleccionada y aplica el excedente a otras deudas
     */
    private function paySpecificSaleAndApplySurplus(array $data, Sale $sale, float $surplus): Model
    {
        return DB::transaction(function () use ($data, $sale, $surplus) {
            $saleDebt = $sale->pending_amount;
            
            // 1. Pagar completamente la factura seleccionada
            $mainPayment = static::getModel()::create([
                'business_id' => $data['business_id'],
                'client_id' => $data['client_id'],
                'sale_id' => $sale->id,
                'amount' => $saleDebt, // Registra el monto total de la deuda de esta factura
                'payment_date' => $data['payment_date'],
            ]);
            
            $sale->pending_amount = 0;
            $sale->status = 'Pagada';
            $sale->save();
            
            // 2. Aplicar el excedente a otras facturas pendientes
            if ($surplus > 0) {
                $this->applySurplusToOtherSales($data, $surplus, $sale->id);
            }
            
            Notification::make()
                ->title('Pago Completo Registrado')
                ->body("Factura seleccionada pagada completamente. Excedente de $" . number_format($surplus, 2) . " aplicado a otras deudas.")
                ->success()
                ->send();
            
            return $mainPayment;
        });
    }

    /**
     * Aplica el excedente a otras facturas pendientes del cliente
     */
    private function applySurplusToOtherSales(array $data, float $surplus, int $excludeSaleId): void
    {
        $client = Client::findOrFail($data['client_id']);
        $remainingPayment = $surplus;
        
        // Obtener otras facturas pendientes (excluyendo la ya pagada)
        $pendingSales = $client->sales()
                               ->where('status', '!=', 'Pagada')
                               ->where('id', '!=', $excludeSaleId)
                               ->orderBy('date', 'asc')
                               ->get();
        
        foreach ($pendingSales as $otherSale) {
            if ($remainingPayment <= 0) break;

            $amountToApply = min($remainingPayment, $otherSale->pending_amount);
            
            // Registrar el pago para esta factura
            static::getModel()::create([
                'business_id' => $data['business_id'],
                'client_id' => $client->id,
                'sale_id' => $otherSale->id,
                'amount' => $amountToApply,
                'payment_date' => $data['payment_date'],
            ]);
            
            // Actualizar el saldo pendiente de la factura
            $otherSale->pending_amount -= $amountToApply;
            
            if ($otherSale->pending_amount <= 0.01) {
                $otherSale->pending_amount = 0;
                $otherSale->status = 'Pagada';
            }
            $otherSale->save();

            $remainingPayment -= $amountToApply;
        }
    }

    /**
     * Aplica el pago automáticamente a las facturas más antiguas cuando no se selecciona factura específica
     */
    protected function applyPaymentToOldestSales(array $data): Model
    {
        return DB::transaction(function () use ($data) {
            $client = Client::findOrFail($data['client_id']);
            $paymentAmount = floatval($data['amount']);
            $remainingPayment = $paymentAmount;
            $lastPayment = null;

            // 1. Obtener todas las facturas pendientes del cliente, de la más antigua a la más nueva
            $pendingSales = $client->sales()
                                   ->where('status', '!=', 'Pagada')
                                   ->orderBy('date', 'asc')
                                   ->get();
            
            if ($pendingSales->isEmpty()) {
                Notification::make()
                    ->title('Sin Deudas Pendientes')
                    ->body('Este cliente no tiene facturas pendientes de pago.')
                    ->warning()
                    ->send();
                
                // Crear un pago vacío para evitar errores
                return static::getModel()::make($data);
            }

            foreach ($pendingSales as $sale) {
                if ($remainingPayment <= 0) break;

                $amountToApply = min($remainingPayment, $sale->pending_amount);
                
                // 2. Registrar el pago parcial o total para esta factura
                $lastPayment = static::getModel()::create([
                    'business_id' => $data['business_id'],
                    'client_id' => $client->id,
                    'sale_id' => $sale->id,
                    'amount' => $amountToApply,
                    'payment_date' => $data['payment_date'],
                ]);
                
                // 3. Actualizar el saldo pendiente de la factura
                $sale->pending_amount -= $amountToApply;
                
                // 4. Si la factura queda en cero, se marca como Pagada
                if ($sale->pending_amount <= 0.01) {
                    $sale->pending_amount = 0;
                    $sale->status = 'Pagada';
                }
                $sale->save();

                $remainingPayment -= $amountToApply;
            }

            // 5. Enviar notificaciones con el resultado
            $newDebt = $client->getCurrentDebt();

            if ($remainingPayment > 0) {
                Notification::make()
                    ->title('Pago Registrado con Saldo a Favor')
                    ->body("Se aplicó el pago. El a devolver al cliente es de $" . number_format($remainingPayment, 2))
                    ->success()
                    ->send();
            } elseif ($newDebt > 0) {
                Notification::make()
                    ->title('Abono Registrado')
                    ->body("Se aplicó el pago. El cliente aún tiene una deuda de $" . number_format($newDebt, 2))
                    ->warning()
                    ->send();
            } else {
                Notification::make()
                    ->title('Pago Registrado')
                    ->body('La deuda del cliente ha sido saldada por completo.')
                    ->success()
                    ->send();
            }
            
            return $lastPayment ?? static::getModel()::make($data);
        });
    }
}