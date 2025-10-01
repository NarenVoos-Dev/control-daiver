<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use App\Models\PaymentMethod;
use App\Models\CashSessionTransaction;
use App\Models\Sale;
use App\Models\Payment;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Pages\Widgets\BankAccountStatsOverview;

class TransactionDetails extends Page implements HasTable
{
    use InteractsWithTable;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static bool $shouldRegisterNavigation = false;
    protected static string $view = 'filament.pages.transaction-details';
    
    protected static ?string $slug = 'transaction-details/{paymentMethodId}/{startDate}/{endDate}';

    public int $paymentMethodId;
    public string $startDate;
    public string $endDate;
    public ?PaymentMethod $paymentMethod;

    public function mount(int $paymentMethodId, string $startDate, string $endDate): void
    {
        $this->paymentMethodId = $paymentMethodId;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->paymentMethod = PaymentMethod::find($this->paymentMethodId);
        
        if (!$this->paymentMethod) {
            abort(404, 'Método de pago no encontrado');
        }
    }
    
    public function getTitle(): string
    {
        return 'Detalle: ' . ($this->paymentMethod?->name ?? 'Desconocido');
    }
    
    // ✅ Método correcto para header widgets
    protected function getHeaderWidgets(): array
    {
        return [
            BankAccountStatsOverview::make([
                'paymentMethodId' => $this->paymentMethodId,
                'startDate' => $this->startDate,
                'endDate' => $this->endDate,
            ]),
        ];
    }
    
    // ✅ Opcional: configurar el número de columnas para los widgets
    public function getHeaderWidgetsColumns(): int | array
    {
        return 3; // Ajusta según cuántas tarjetas quieras por fila
    }
    
    public function table(Table $table): Table
    {
        return $table
            ->query(
                CashSessionTransaction::query()
                    ->where('type', 'entrada')
                    ->whereBetween('created_at', [$this->startDate, $this->endDate])
                    ->whereHasMorph('source', [Sale::class, Payment::class], function (Builder $query) {
                        $query->where('payment_method_id', $this->paymentMethodId);
                    })
                    ->with(['source.client'])
            )
            ->columns([
                TextColumn::make('created_at')
                    ->label('Fecha')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
                TextColumn::make('source_type')
                    ->label('Tipo')
                    ->formatStateUsing(fn ($state) => $state === Sale::class ? 'Venta de Contado' : 'Abono a Crédito')
                    ->badge()
                    ->color(fn ($state) => $state === Sale::class ? 'success' : 'info'),
                TextColumn::make('source.sale_id')
                    ->label('Origen')
                    ->formatStateUsing(fn ($state, $record) => 'Venta #' . ($record->source->sale_id ?? $record->source->id)),
                TextColumn::make('source.client.name')
                    ->label('Cliente')
                    ->searchable(),
                TextColumn::make('amount')
                    ->label('Monto')
                    ->money('cop')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc');
    }
}