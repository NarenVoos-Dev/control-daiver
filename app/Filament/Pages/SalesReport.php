<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Form;
use Filament\Forms;
use App\Models\Sale;
use App\Models\Client;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\SalesExport;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\Zone;

class SalesReport extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-document-chart-bar';
    protected static ?string $navigationGroup = 'Reportes';
    protected static ?string $navigationLabel = 'Reporte de Ventas';
    protected static ?string $title = 'Reporte de ventas';
    protected static string $view = 'filament.pages.sales-report';
    protected static ?int $navigationSort = 41;

    public ?string $startDate = null;
    public ?string $endDate = null;
    public ?int $clientId = null;
    public ?int $zoneId = null;
    public ?array $data = [];

    public function mount(): void
    {
        $this->startDate = Carbon::now()->startOfMonth()->format('Y-m-d');
        $this->endDate = Carbon::now()->endOfMonth()->format('Y-m-d');
        $this->form->fill([
            'startDate' => $this->startDate,
            'endDate' => $this->endDate,
            'clientId' => $this->clientId,
            'zoneId' => $this->zoneId,
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Filtros del Reporte')
                    ->schema([
                        Forms\Components\DatePicker::make('startDate')
                        ->label('Fecha de Inicio')->required(),
                        Forms\Components\DatePicker::make('endDate')->label('Fecha de Fin')->required(),
                        Forms\Components\Select::make('clientId')->label('Cliente')
                            ->options(Client::query()->pluck('name', 'id'))
                            ->searchable()->placeholder('Todos los clientes'),
                        Forms\Components\Select::make('zoneId')->label('Zona')
                            ->options(Zone::query()->pluck('name', 'id'))
                            ->searchable()->placeholder('Todas las zonas'),
                    ])->columns(4),
            ])
            ->statePath('data');
    }

    public function submit()
    {
        $formData = $this->form->getState();
        $this->startDate = $formData['startDate'];
        $this->endDate = $formData['endDate'];
        $this->clientId = $formData['clientId'];
        $this->zoneId = $formData['zoneId'];
    }

    public function getSalesData()
    {
        $query = Sale::query()
            ->with(['client.zone', 'items.product'])
            ->where('business_id', auth()->user()->business_id)
            ->whereBetween('date', [$this->startDate, $this->endDate]);
        
        if ($this->clientId) {
            $query->where('client_id', $this->clientId);
        }
        if ($this->zoneId) {
            $query->whereHas('client', function ($q) {
                $q->where('zone_id', $this->zoneId);
            });
        }

        return $query->get();
    }

    // --- NUEVOS MÉTODOS PARA EXPORTACIÓN ---
    public function exportToExcel()
    {
        return Excel::download(new SalesExport($this->getSalesData()), 'reporte_ventas.xlsx');
    }

    public function exportToPdf()
    {
        $data = $this->getSalesData();
        $pdf = Pdf::loadView('pdf.sales-report-pdf', ['sales' => $data]);
        
        return response()->streamDownload(function () use ($pdf) {
            echo $pdf->stream();
        }, 'reporte_ventas.pdf');
    }
}