<?php
namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class SalesExport implements FromView, ShouldAutoSize
{
    protected $sales;

    public function __construct($sales)
    {
        $this->sales = $sales;
    }

    public function view(): View
    {
        return view('exports.sales-report-excel', [
            'sales' => $this->sales
        ]);
    }
}
