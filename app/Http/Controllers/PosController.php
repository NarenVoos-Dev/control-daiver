<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Client;
use App\Models\UnitOfMeasure;
use App\Models\Sale;
use App\Models\Zone;
use App\Models\Product;

class PosController extends Controller
{
    // Este método carga los datos iniciales y muestra la vista principal del POS.
    public function index()
    {
        $user = auth()->user();
        $businessId = $user->business_id;

        $user->tokens()->delete();
        $apiToken = $user->createToken('pos-token')->plainTextToken;

        // Solo pasamos los datos esenciales para la carga inicial
        $categories = Category::where('business_id', $user->business_id)->get(['id', 'name']);
        $hasUncategorized = Product::where('business_id', $businessId)->whereNull('category_id')->exists();
        if ($hasUncategorized) {
            $categories->push((object)['id' => 'uncategorized', 'name' => 'Sin Categoría']);
        }
        $units = UnitOfMeasure::where('business_id', $user->business_id)->get();
        $zones = Zone::where('business_id', $businessId)->get(['id', 'name']); // <-- OBTENER LAS ZONAS


        return view('pos.index', compact('categories', 'units', 'apiToken', 'zones'));
    }
    
    public function salesList()
    {
        $user = auth()->user();
        $sales = Sale::where('business_id', $user->business_id)
                     ->with('client')
                     ->latest() // Ordena por más reciente
                     ->paginate(20); // Pagina los resultados
        return view('pos.sales-list', compact('sales'));
    }
}