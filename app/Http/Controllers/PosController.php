<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Client;
use App\Models\UnitOfMeasure;
use App\Models\Sale;

class PosController extends Controller
{
    // Este método carga los datos iniciales y muestra la vista principal del POS.
    public function index()
    {
        $user = auth()->user();
        $user->tokens()->delete();
        $apiToken = $user->createToken('pos-token')->plainTextToken;

        // Solo pasamos los datos esenciales para la carga inicial
        $categories = Category::where('business_id', $user->business_id)->get(['id', 'name']);
        $units = UnitOfMeasure::where('business_id', $user->business_id)->get();

        return view('pos.index', compact('categories', 'units', 'apiToken'));
    }
    /*{
        $user = auth()->user();
        $businessId = $user->business_id;

        // Generamos un token de API para el usuario autenticado
        $user->tokens()->delete();
        $apiToken = $user->createToken('pos-token')->plainTextToken;

        //Cargamos todas las categorias con sus productos
        $categories = Category::where('business_id', $businessId)
                                ->with(['products' => fn($q) => $q->with('unitOfMeasure')])
                                ->get();
                
        
        //$clients = Client::where('business_id', $businessId)->get(['id', 'name']);
        $units = UnitOfMeasure::where('business_id', $businessId)->get();

        // Pasamos el token a la vista
        //return view('pos.index', compact('clients', 'units', 'apiToken'));
        return view('pos.index', compact('categories', 'units', 'apiToken'));
    }*/

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