<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;

use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Contracts\HasTable;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Tables\Concerns\InteractsWithTable;
use App\Models\Supplier;
use App\Models\Purchase;
use App\Models\Egress;
use App\Models\SupplierPayment;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Tables\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\DB;

class ManageAccountsPayable extends Page implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;
    // <<< Configuración del Menú >>>
    protected static ?string $navigationIcon = 'heroicon-o-building-library';
    protected static ?string $navigationGroup = 'Finanzas';
    protected static ?int $navigationSort = 22;
    protected static ?string $navigationLabel = 'Cuentas por Pagar';
    protected static ?string $title = 'Cuentas por Pagar';
    // Ruta de la vista que usaremos más adelante
    protected static string $view = 'filament.pages.manage-accounts-payable';

    public function table(Table $table): Table
    {
        return $table
            ->query(
               Supplier::query()
                    ->whereHas('purchases', fn ($query) => $query->where('status', 'Pendiente'))
                    ->withSum(['purchases as pending_total' => function ($query) {
                        $query->where('status', 'Pendiente');
                    }], 'pending_amount') // Sumamos sobre el nuevo campo 'pending_amount'
            )
            ->columns([
                TextColumn::make('name')
                    ->label('Proveedor')
                    ->searchable(),
                TextColumn::make('document')
                    ->label('Documento'),
                TextColumn::make('pending_total')
                    ->label('Deuda Pendiente')
                    ->money('cop')
                    ->sortable(),
            ])
             ->actions([
                Action::make('pay')
                    ->label('Registrar Pago')
                    ->icon('heroicon-o-currency-dollar')
                    ->color('success')
                    // <<< AQUÍ EMPIEZA LA LÓGICA DEL MODAL Y EL PAGO >>>
                    ->form([
                        TextInput::make('amount')
                            ->label('Monto a Pagar')
                            ->required()
                            ->numeric()
                            ->prefix('$'),
                        DatePicker::make('payment_date')
                            ->label('Fecha del Pago')
                            ->required()
                            ->default(now()),
                        TextInput::make('notes')
                            ->label('Notas / Referencia de Pago'),
                    ])
                    ->action(function (Supplier $record, array $data) {
                        $this->applySupplierPayment($record, $data);
                    }),
            ]);
    }

    public function applySupplierPayment(Supplier $supplier, array $data): void
    {
        DB::transaction(function () use ($supplier, $data) {
            $paymentAmount = floatval($data['amount']);
            
            // 1. Crear el Egreso para registrar la salida de dinero
            $egress = Egress::create([
                'business_id' => auth()->user()->business_id,
                'user_id' => auth()->id(),
                'type' => 'pago_proveedor',
                'description' => 'Abono a deuda proveedor: ' . $supplier->name . '. ' . ($data['notes'] ?? ''),
                'amount' => $paymentAmount,
                'payment_method' => 'efectivo', // O podrías añadir un selector en el form
                'supplier_id' => $supplier->id,
                'date' => $data['payment_date'],
            ]);

            $remainingPayment = $paymentAmount;

            // 2. Obtener las compras pendientes más antiguas primero
            $pendingPurchases = $supplier->purchases()
                ->where('status', 'Pendiente')
                ->orderBy('date', 'asc')
                ->get();

            // 3. Aplicar el pago a las facturas
            foreach ($pendingPurchases as $purchase) {
                if ($remainingPayment <= 0) break;

                $amountToApply = min($remainingPayment, $purchase->pending_amount);
                
                // 4. Registrar el pago individual
                SupplierPayment::create([
                    'business_id' => auth()->user()->business_id,
                    'supplier_id' => $supplier->id,
                    'purchase_id' => $purchase->id,
                    'egress_id' => $egress->id,
                    'amount' => $amountToApply,
                    'payment_date' => $data['payment_date'],
                ]);

                // 5. Actualizar la compra
                $purchase->pending_amount -= $amountToApply;
                if ($purchase->pending_amount <= 0.01) {
                    $purchase->pending_amount = 0;
                    $purchase->status = 'Pagada';
                }
                $purchase->save();

                $remainingPayment -= $amountToApply;
            }
        });

        Notification::make()
            ->title('¡Pago registrado exitosamente!')
            ->success()
            ->send();
    }
}