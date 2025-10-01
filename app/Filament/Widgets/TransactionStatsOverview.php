<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use App\Models\PaymentMethod;
use App\Models\Sale;
use App\Models\Payment;
use App\Filament\Pages\TransactionDetails;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\On;

class TransactionStatsOverview extends BaseWidget
{
    public ?string $startDate = null;
    public ?string $endDate = null;

    #[On('datesUpdated')]
    public function updateStats(string $startDate, string $endDate): void
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
    }

    protected function getStats(): array
    {
        if (!$this->startDate || !$this->endDate) {
            return [];
        }

        $startDate = $this->startDate;
        $endDate = $this->endDate;

        $salesData = Sale::query()
            ->where('is_cash', true)
            ->whereBetween('date', [$startDate, $endDate])
            ->groupBy('payment_method_id')
            ->select('payment_method_id', DB::raw('SUM(total) as total_amount'), DB::raw('COUNT(id) as transaction_count'))
            ->get()
            ->keyBy('payment_method_id');

        $paymentsData = Payment::query()
            ->whereBetween('payment_date', [$startDate, $endDate])
            ->groupBy('payment_method_id')
            ->select('payment_method_id', DB::raw('SUM(amount) as total_amount'), DB::raw('COUNT(id) as transaction_count'))
            ->get()
            ->keyBy('payment_method_id');

        return PaymentMethod::all()->map(function ($method) use ($salesData, $paymentsData, $startDate, $endDate) {
            
            $saleTotal = $salesData->get($method->id)->total_amount ?? 0;
            $paymentTotal = $paymentsData->get($method->id)->total_amount ?? 0;
            $saleCount = $salesData->get($method->id)->transaction_count ?? 0;
            $paymentCount = $paymentsData->get($method->id)->transaction_count ?? 0;

            $total = $saleTotal + $paymentTotal;
            $count = $saleCount + $paymentCount;

            return Stat::make($method->name, '$' . number_format($total, 0))
                ->description($count . ' transacciones')
                ->url(
                    TransactionDetails::getUrl([
                        'paymentMethodId' => $method->id,
                        'startDate' => $startDate,
                        'endDate' => $endDate,
                    ])
                )
                ->color($total > 0 ? 'success' : 'gray');
        })->all();
    }
}