<?php

namespace App\Filament\Resources\EgressResource\Pages;

use App\Filament\Resources\EgressResource;
use Filament\Actions;
use App\Models\Purchase;
use Filament\Resources\Pages\CreateRecord;

use App\Models\CashSession;
use App\Models\CashSessionTransaction;
use Illuminate\Database\Eloquent\Model;

class CreateEgress extends CreateRecord
{
    protected static string $resource = EgressResource::class;

    protected function afterCreate(): void
    {
        // 1. Obtenemos el registro del egreso que se acaba de crear.
        $egress = $this->getRecord();

        // 2. Verificamos si este egreso está relacionado con una compra.
        if ($egress->purchase_id) {
            $purchase = Purchase::find($egress->purchase_id);
            if ($purchase) {
                // Si la compra existe, actualizamos su estado a "Pagada".
                $purchase->status = 'Pagada';
                $purchase->save();
            }
        }

        // 3. Verificamos si el egreso se marcó para pagarse desde la caja.
        if ($egress->pay_from_cash_session) {
            
            // Buscamos la sesión de caja que esté actualmente "Abierta".
            $activeSession = CashSession::where('business_id', $egress->business_id)
                                        ->where('status', 'Abierta')
                                        ->first();

            // Si se encuentra una sesión activa...
            if ($activeSession) {
                // Creamos la transacción de SALIDA de dinero.
                CashSessionTransaction::create([
                    'cash_session_id' => $activeSession->id,
                    'amount' => $egress->amount,
                    'type' => 'salida', // <-- El tipo es 'salida' porque es un gasto.
                    'description' => 'Egreso: ' . $egress->description,
                    'source_type' => get_class($egress),
                    'source_id' => $egress->id,
                ]);

                // Finalmente, vinculamos el egreso a la sesión de caja.
                $egress->cash_session_id = $activeSession->id;
                $egress->save();
            }
        }
    }
}
