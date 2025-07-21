<?php

namespace App\Http\Controllers;

use App\Models\Sale;

class ReceiptController extends Controller
{
    public function print(Sale $sale)
    {
        // Cargamos las relaciones para tener todos los datos en la vista
        $sale->load(['client', 'business', 'items.product']);

        return view('receipts.thermal', ['sale' => $sale]);
    }
}