<?php

namespace App\Filament\Resources\PaymentResource\Pages;

use App\Filament\Resources\PaymentResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
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
     * Maneja la creación del registro de pago aplicando el abono desde la factura más antigua
     * hasta la más nueva hasta que se agote el monto del pago
     */
    protected function handleRecordCreation(array $data): Model
    {
        return DB::transaction(function () use ($data) {
            $client = Client::findOrFail($data['client_id']);
            $paymentAmount = floatval($data['amount']);
            $remainingPayment = $paymentAmount;
            $lastPayment = null;

            // 1. Obtener todas las facturas pendientes del cliente, de la más antigua a la más nueva
            $pendingSales = $client->sales()
                                   ->where('status','!=' ,'Pagada')
                                   ->orderBy('date', 'asc')
                                   ->get();
            

            if ($pendingSales->isEmpty()) {
                Notification::make()
                    ->title('Sin Deudas Pendientes')
                    ->body('Este cliente no tiene facturas pendientes de pago.')
                    ->warning()
                    ->send();
                
                
                // Detiene la ejecución para evitar el error.
                $this->halt();
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
                if ($sale->pending_amount <= 0.01) { // Tolerancia para decimales
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
                    ->body("Se aplicó el pago. El cliente ahora tiene un saldo a favor de $" . number_format($remainingPayment, 2))
                    ->success()->send();
            } elseif ($newDebt > 0) {
                Notification::make()
                    ->title('Abono Registrado')
                    ->body("Se aplicó el pago. El cliente aún tiene una deuda de $" . number_format($newDebt, 2))
                    ->warning()->send();
            } else {
                Notification::make()
                    ->title('Pago Registrado')
                    ->body('La deuda del cliente ha sido saldada por completo.')
                    ->success()->send();
            }
            
            // Devolvemos el último pago creado como registro principal
            return $lastPayment ?? static::getModel()::make($data);
        });
    }

    /**
     * Valida los datos del pago
     */
    private function validatePaymentData(array $data): void
    {
        if (!isset($data['amount']) || !is_numeric($data['amount']) || (float) $data['amount'] <= 0) {
            throw new \InvalidArgumentException('El monto del abono debe ser mayor a cero');
        }

        if (!isset($data['client_id']) || empty($data['client_id'])) {
            throw new \InvalidArgumentException('Debe especificar un cliente válido');
        }

        if (!isset($data['business_id']) || empty($data['business_id'])) {
            throw new \InvalidArgumentException('Debe especificar un negocio válido');
        }

        if (!isset($data['payment_date']) || empty($data['payment_date'])) {
            throw new \InvalidArgumentException('Debe especificar una fecha de pago válida');
        }
    }
}