<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Form;
use Filament\Forms;
use App\Models\Product;
use App\Models\StockMovement;
use Carbon\Carbon;

class KardexReport extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-table-cells';
    protected static ?string $navigationGroup = 'Reportes';
    protected static ?string $navigationLabel = 'Reporte de Inventario (Kardex)';
    protected static ?string $title = '(Kardex) - Movimiento de Inventario ';
    protected static ?int $navigationSort = 42;
    protected static string $view = 'filament.pages.kardex-report';

    // Propiedades para los filtros
    public ?string $startDate = null;
    public ?string $endDate = null;
    public ?int $productId = null;
    public ?array $data = [];

    public function mount(): void
    {
        // Rango por defecto: el último mes
        $this->startDate = Carbon::now()->subMonth()->format('Y-m-d');
        $this->endDate = Carbon::now()->format('Y-m-d');
        $this->form->fill([
            'startDate' => $this->startDate,
            'endDate' => $this->endDate,
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Filtros del Reporte')
                    ->schema([
                        Forms\Components\Select::make('productId')
                            ->label('Producto')
                            ->options(Product::query()->pluck('name', 'id'))
                            ->searchable()
                            ->required() // El producto es obligatorio para este reporte
                            ->placeholder('Seleccione un producto'),
                        Forms\Components\DatePicker::make('startDate')
                            ->label('Fecha de Inicio')
                            ->required(),
                        Forms\Components\DatePicker::make('endDate')
                            ->label('Fecha de Fin')
                            ->required(),
                    ])->columns(3),
            ])
            ->statePath('data');
    }

    public function submit()
    {
        $formData = $this->form->getState();
        $this->productId = $formData['productId'];
        $this->startDate = $formData['startDate'];
        $this->endDate = $formData['endDate'];
    }

    /**
     * Obtiene el historial de movimientos para el producto seleccionado.
     */
    public function getStockMovements()
    {
        // Solo ejecuta la consulta si se ha seleccionado un producto
        if (!$this->productId) {
            return collect(); // Devuelve una colección vacía
        }

        return StockMovement::query()
            ->with('source') // Precarga la relación polimórfica (Venta, Compra, etc.)
            ->where('product_id', $this->productId)
            ->whereBetween('created_at', [
                Carbon::parse($this->startDate)->startOfDay(),
                Carbon::parse($this->endDate)->endOfDay(),
            ])
            ->orderBy('created_at', 'asc')
            ->get();
    }
}

