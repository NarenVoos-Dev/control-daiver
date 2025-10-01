<?php

namespace App\Filament\Pages\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use App\Models\Sale;
use App\Models\Payment;
use App\Models\BankAccount;
use Illuminate\Support\Facades\DB;

class BankAccountStatsOverview extends BaseWidget
{
    public ?int $paymentMethodId = null;
    public ?string $startDate = null;
    public ?string $endDate = null;

    protected function getStats(): array
    {
        // Validación mejorada con log para debugging
        if (!$this->paymentMethodId || !$this->startDate || !$this->endDate) {
            \Log::info('BankAccountStatsOverview - Datos faltantes', [
                'paymentMethodId' => $this->paymentMethodId,
                'startDate' => $this->startDate,
                'endDate' => $this->endDate,
            ]);
            return [
                Stat::make('Información', 'No hay datos disponibles')
                    ->description('Verifica los filtros')
                    ->color('gray')
            ];
        }

        // Subconsulta para Ventas de Contado
        $salesTotals = Sale::query()
            ->where('is_cash', true)
            ->where('payment_method_id', $this->paymentMethodId)
            ->whereBetween('date', [$this->startDate, $this->endDate])
            ->groupBy('bank_account_id')
            ->select('bank_account_id', DB::raw('SUM(total) as total_amount'))
            ->pluck('total_amount', 'bank_account_id');

        // Subconsulta para Abonos
        $paymentsTotals = Payment::query()
            ->where('payment_method_id', $this->paymentMethodId)
            ->whereBetween('payment_date', [$this->startDate, $this->endDate])
            ->groupBy('bank_account_id')
            ->select('bank_account_id', DB::raw('SUM(amount) as total_amount'))
            ->pluck('total_amount', 'bank_account_id');

        // Obtenemos todas las cuentas bancarias
        $stats = BankAccount::all()->map(function ($account) use ($salesTotals, $paymentsTotals) {
            $total = ($salesTotals[$account->id] ?? 0) + ($paymentsTotals[$account->id] ?? 0);

            // Mostramos todas las cuentas, incluso las que tienen $0
            return Stat::make($account->name, '$' . number_format($total, 0, ',', '.'))
                ->description($account->account_number ?? 'Sin número de cuenta')
                ->color($total > 0 ? 'success' : 'gray');
        })->all();

        // Si no hay stats, mostramos un mensaje
        if (empty($stats)) {
            return [
                Stat::make('Sin Cuentas', 'No hay cuentas bancarias configuradas')
                    ->description('Configure cuentas bancarias en el sistema')
                    ->color('warning')
            ];
        }

        return $stats;
    }
}