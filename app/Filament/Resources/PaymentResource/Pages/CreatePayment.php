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
     * Maneja la creación del registro de pago con lógica de aplicación automática
     * a facturas pendientes usando el método FIFO (First In, First Out)
     */
    protected function handleRecordCreation(array $data): Model
    {
        return DB::transaction(function () use ($data) {
            try {
                // Validaciones iniciales
                $this->validatePaymentData($data);
                
                $client = Client::findOrFail($data['client_id']);
                $paymentAmount = (float) $data['amount'];
                $remainingPayment = $paymentAmount;
                $paymentsCreated = [];
                $salesAffected = [];

                // Log del inicio del proceso
                Log::info('Iniciando proceso de aplicación de pago', [
                    'client_id' => $client->id,
                    'client_name' => $client->name,
                    'payment_amount' => $paymentAmount,
                    'payment_date' => $data['payment_date'],
                    'current_debt_before' => $client->getCurrentDebt()
                ]);

                // 1. Obtener todas las facturas pendientes del cliente ordenadas por antigüedad
                $pendingSales = $client->sales()
                    ->where('status', '!=', 'Pagada')
                    ->where('pending_amount', '>', 0)
                    ->orderBy('date', 'asc')
                    ->orderBy('id', 'asc') // Desempate por ID para facturas del mismo día
                    ->get(['id', 'date', 'total', 'pending_amount', 'status']);

                if ($pendingSales->isEmpty()) {
                    // No hay facturas pendientes, crear un solo pago como saldo a favor
                    return $this->createCreditBalancePayment($data, $client, $paymentAmount);
                }

                Log::info('Facturas pendientes encontradas para aplicar pago', [
                    'total_invoices' => $pendingSales->count(),
                    'total_pending_debt' => $pendingSales->sum('pending_amount'),
                    'oldest_invoice_date' => $pendingSales->first()->date->format('Y-m-d'),
                    'newest_invoice_date' => $pendingSales->last()->date->format('Y-m-d')
                ]);

                // 2. Aplicar el pago a las facturas de más antigua a más nueva
                foreach ($pendingSales as $sale) {
                    if ($remainingPayment <= 0) {
                        break;
                    }

                    $salesPendingAmount = (float) $sale->pending_amount;
                    $amountToApply = min($remainingPayment, $salesPendingAmount);
                    
                    // Crear el registro de pago específico para esta factura
                    $payment = Payment::create([
                        'business_id' => $data['business_id'],
                        'client_id' => $client->id,
                        'sale_id' => $sale->id,
                        'amount' => $amountToApply,
                        'payment_date' => $data['payment_date'],
                    ]);
                    
                    $paymentsCreated[] = $payment;
                    
                    // Actualizar el estado de la factura
                    $previousPending = $sale->pending_amount;
                    $sale->pending_amount = $salesPendingAmount - $amountToApply;
                    
                    // Si queda un monto muy pequeño (menor a 0.01), considerarlo como 0
                    if ($sale->pending_amount < 0.01) {
                        $sale->pending_amount = 0;
                        $sale->status = 'Pagada';
                    }
                    
                    $sale->save();
                    
                    // Registrar la información de esta aplicación de pago
                    $salesAffected[] = [
                        'sale_id' => $sale->id,
                        'sale_date' => $sale->date->format('Y-m-d'),
                        'sale_total' => $sale->total,
                        'amount_applied' => $amountToApply,
                        'previous_pending' => $previousPending,
                        'new_pending' => $sale->pending_amount,
                        'new_status' => $sale->status
                    ];

                    $remainingPayment -= $amountToApply;
                    
                    Log::info('Pago aplicado a factura', [
                        'sale_id' => $sale->id,
                        'amount_applied' => $amountToApply,
                        'sale_new_status' => $sale->status,
                        'remaining_payment' => $remainingPayment
                    ]);
                }

                // 3. Si queda dinero sin aplicar, crear un pago adicional como saldo a favor
                if ($remainingPayment > 0) {
                    $creditPayment = Payment::create([
                        'business_id' => $data['business_id'],
                        'client_id' => $client->id,
                        'sale_id' => null, // Sin factura específica - saldo a favor
                        'amount' => $remainingPayment,
                        'payment_date' => $data['payment_date'],
                    ]);
                    
                    $paymentsCreated[] = $creditPayment;
                    
                    Log::info('Saldo a favor creado', [
                        'amount' => $remainingPayment,
                        'payment_id' => $creditPayment->id
                    ]);
                }

                // 4. Calcular nueva deuda y generar resumen
                $newDebt = $client->fresh()->getCurrentDebt();
                $totalSalesPaidOff = collect($salesAffected)->where('new_status', 'Pagada')->count();
                
                $summary = [
                    'total_payment' => $paymentAmount,
                    'applied_to_invoices' => $paymentAmount - $remainingPayment,
                    'credit_balance' => $remainingPayment,
                    'invoices_affected' => count($salesAffected),
                    'invoices_paid_off' => $totalSalesPaidOff,
                    'new_debt' => $newDebt,
                    'payments_created' => count($paymentsCreated)
                ];

                // Log del resultado final
                Log::info('Proceso de aplicación de pago completado', array_merge($summary, [
                    'sales_details' => $salesAffected
                ]));

                // 5. Enviar notificación apropiada
                $this->sendPaymentNotification($summary, $client);
                
                // 6. Retornar el primer pago creado como referencia principal
                return $paymentsCreated[0];

            } catch (ModelNotFoundException $e) {
                Log::error('Cliente no encontrado al procesar pago', [
                    'client_id' => $data['client_id'] ?? 'N/A',
                    'error' => $e->getMessage()
                ]);
                
                Notification::make()
                    ->title('Error: Cliente No Encontrado')
                    ->body('El cliente especificado no existe en el sistema.')
                    ->danger()
                    ->duration(5000)
                    ->send();
                    
                throw new \Exception('Cliente no encontrado');
                
            } catch (\Exception $e) {
                Log::error('Error crítico al procesar pago', [
                    'error_message' => $e->getMessage(),
                    'error_trace' => $e->getTraceAsString(),
                    'payment_data' => $data
                ]);
                
                Notification::make()
                    ->title('Error al Procesar Pago')
                    ->body('Ha ocurrido un error inesperado. El pago no se ha registrado. Contacte al administrador del sistema.')
                    ->danger()
                    ->duration(8000)
                    ->send();
                    
                throw $e;
            }
        });
    }

    /**
     * Crea un pago cuando no hay facturas pendientes (saldo a favor completo)
     */
    private function createCreditBalancePayment(array $data, Client $client, float $amount): Payment
    {
        Log::info('Cliente sin facturas pendientes, creando saldo a favor completo', [
            'client_id' => $client->id,
            'amount' => $amount
        ]);

        $payment = Payment::create([
            'business_id' => $data['business_id'],
            'client_id' => $client->id,
            'sale_id' => null,
            'amount' => $amount,
            'payment_date' => $data['payment_date'],
        ]);

        Notification::make()
            ->title('Saldo a Favor Registrado')
            ->body(sprintf(
                'El cliente %s no tiene facturas pendientes. Se ha registrado un saldo a favor de $%s',
                $client->name,
                number_format($amount, 2)
            ))
            ->success()
            ->duration(8000)
            ->send();

        return $payment;
    }

    /**
     * Valida los datos del pago antes de procesarlo
     */
    private function validatePaymentData(array $data): void
    {
        $errors = [];

        if (!isset($data['amount']) || !is_numeric($data['amount']) || (float) $data['amount'] <= 0) {
            $errors[] = 'El monto del pago debe ser un número mayor a cero';
        }

        if (!isset($data['client_id']) || empty($data['client_id'])) {
            $errors[] = 'Debe especificar un cliente válido';
        }

        if (!isset($data['business_id']) || empty($data['business_id'])) {
            $errors[] = 'Debe especificar un negocio válido';
        }

        if (!isset($data['payment_date']) || empty($data['payment_date'])) {
            $errors[] = 'Debe especificar una fecha de pago válida';
        }

        if (!empty($errors)) {
            $errorMessage = 'Datos de pago inválidos: ' . implode(', ', $errors);
            
            Notification::make()
                ->title('Error de Validación')
                ->body($errorMessage)
                ->danger()
                ->duration(6000)
                ->send();
                
            throw new \InvalidArgumentException($errorMessage);
        }
    }

    /**
     * Envía la notificación apropiada según el resultado del pago
     */
    private function sendPaymentNotification(array $summary, Client $client): void
    {
        $clientName = $client->name;
        $totalPayment = $summary['total_payment'];
        $appliedAmount = $summary['applied_to_invoices'];
        $creditBalance = $summary['credit_balance'];
        $newDebt = $summary['new_debt'];
        $invoicesAffected = $summary['invoices_affected'];
        $invoicesPaidOff = $summary['invoices_paid_off'];

        if ($creditBalance > 0 && $newDebt > 0) {
            // Pago aplicado parcialmente con saldo a favor
            Notification::make()
                ->title('Pago Aplicado - Saldo a Favor Generado')
                ->body(sprintf(
                    '✅ Cliente: %s%s' .
                    '💰 Pago total: $%s%s' .
                    '📋 Aplicado a %d factura(s): $%s%s' .
                    '🏆 Facturas pagadas completamente: %d%s' .
                    '💳 Saldo a favor: $%s%s' .
                    '📊 Deuda restante: $%s',
                    $clientName, PHP_EOL,
                    number_format($totalPayment, 2), PHP_EOL,
                    $invoicesAffected, number_format($appliedAmount, 2), PHP_EOL,
                    $invoicesPaidOff, PHP_EOL,
                    number_format($creditBalance, 2), PHP_EOL,
                    number_format($newDebt, 2)
                ))
                ->success()
                ->duration(10000)
                ->send();

        } elseif ($creditBalance > 0 && $newDebt == 0) {
            // Deuda completamente saldada con saldo a favor
            Notification::make()
                ->title('¡Deuda Saldada Completamente!')
                ->body(sprintf(
                    '🎉 ¡Excelente! Cliente: %s%s' .
                    '💰 Pago recibido: $%s%s' .
                    '✅ Todas las facturas han sido pagadas (%d facturas)%s' .
                    '💳 Saldo a favor: $%s',
                    $clientName, PHP_EOL,
                    number_format($totalPayment, 2), PHP_EOL,
                    $invoicesAffected, PHP_EOL,
                    number_format($creditBalance, 2)
                ))
                ->success()
                ->duration(12000)
                ->send();

        } elseif ($newDebt > 0) {
            // Abono aplicado, deuda restante
            Notification::make()
                ->title('Abono Registrado Exitosamente')
                ->body(sprintf(
                    '📝 Cliente: %s%s' .
                    '💰 Abono aplicado: $%s%s' .
                    '📋 Facturas afectadas: %d%s' .
                    '🏆 Facturas pagadas: %d%s' .
                    '⚠️ Deuda pendiente: $%s',
                    $clientName, PHP_EOL,
                    number_format($totalPayment, 2), PHP_EOL,
                    $invoicesAffected, PHP_EOL,
                    $invoicesPaidOff, PHP_EOL,
                    number_format($newDebt, 2)
                ))
                ->warning()
                ->duration(8000)
                ->send();

        } else {
            // Deuda completamente saldada sin saldo a favor
            Notification::make()
                ->title('¡Deuda Totalmente Pagada!')
                ->body(sprintf(
                    '🎉 ¡Perfecto! Cliente: %s%s' .
                    '💰 Pago: $%s%s' .
                    '✅ %d factura(s) procesada(s)%s' .
                    '🏆 Todas las facturas pagadas%s' .
                    '✨ Sin deuda pendiente',
                    $clientName, PHP_EOL,
                    number_format($totalPayment, 2), PHP_EOL,
                    $invoicesAffected, PHP_EOL,
                    PHP_EOL
                ))
                ->success()
                ->duration(10000)
                ->send();
        }
    }

    /**
     * Desactivar la notificación por defecto de Filament
     */
    protected function getCreatedNotificationTitle(): ?string
    {
        return null;
    }

    /**
     * Acciones después de crear el registro
     */
    protected function afterCreate(): void
    {
        // Aquí puedes agregar lógica adicional como:
        // - Actualización de caché de cliente
        // - Envío de emails de confirmación
        // - Integración con sistemas contables externos
        // - Triggers para reportes automáticos
        
        parent::afterCreate();
    }
}