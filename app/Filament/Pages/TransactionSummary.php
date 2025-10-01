<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Form;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Section;
use Carbon\Carbon;
use App\Filament\Widgets\TransactionStatsOverview;

class TransactionSummary extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-chart-bar-square';
    protected static ?string $navigationGroup = 'Reportes';
    protected static ?string $navigationLabel = 'Resumen de Transacciones';
    protected static ?string $title = 'Resumen de Transacciones';

    protected static string $view = 'filament.pages.transaction-summary';

    public ?string $startDate = null;
    public ?string $endDate = null;

    // Registra el widget de estadísticas en la cabecera de la página.
    protected function getHeaderWidgets(): array
    {
        return [
            TransactionStatsOverview::class,
        ];
    }
    
    // Al cargar la página, establece las fechas por defecto (mes actual) y llena el formulario.
    public function mount(): void
    {
        $this->startDate = Carbon::now()->startOfMonth()->format('Y-m-d');
        $this->endDate = Carbon::now()->endOfMonth()->format('Y-m-d');
        
        $this->form->fill([
            'startDate' => $this->startDate,
            'endDate' => $this->endDate,
        ]);
    }

    // Define el formulario con los selectores de fecha.
    public function form(Form $form): Form
    {
        return $form->schema([
            Section::make('Filtros')
                ->schema([
                    DatePicker::make('startDate')->label('Fecha de Inicio')->required(),
                    DatePicker::make('endDate')->label('Fecha de Fin')->required(),
                ])->columns(2),
        ]);
    }

    // Esta función se ejecuta al presionar el botón "Aplicar Filtros".
    public function filter(): void
    {
        $data = $this->form->getState();
        $this->startDate = $data['startDate'];
        $this->endDate = $data['endDate'];
        
        // "Anuncia" un evento con las nuevas fechas para que el widget lo "escuche".
        $this->dispatch('datesUpdated', startDate: $this->startDate, endDate: $this->endDate);
    }
}