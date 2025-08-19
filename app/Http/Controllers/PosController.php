<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Client;
use App\Models\UnitOfMeasure;
use App\Models\Sale;
use App\Models\Zone;
use App\Models\Product;
use Carbon\Carbon;
use Illuminate\Http\Request; 

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
    
    public function salesList(Request $request)
    {
        $user = auth()->user();
        $businessId = $user->business_id;

        // 1. Obtener las ventas paginadas y filtradas
        $salesQuery = Sale::where('business_id', $businessId)
                     ->with('client')
                     ->latest();

        // Aplicar filtros si existen en la petición
        if ($request->filled('search')) {
            $search = $request->input('search');
            $salesQuery->where(function ($query) use ($search) {
                $query->where('id', 'like', "%{$search}%")
                      ->orWhereHas('client', function ($q) use ($search) {
                          $q->where('name', 'like', "%{$search}%");
                      });
            });
        }
        if ($request->filled('status')) {
            $salesQuery->where('status', $request->input('status'));
        }
        if ($request->filled('date')) {
            $salesQuery->whereDate('date', $request->input('date'));
        }

        $sales = $salesQuery->paginate(10)->withQueryString();

        // 2. Calcular las estadísticas para las tarjetas
        $stats = [
            'totalSalesCount' => Sale::where('business_id', $businessId)->count(),
            'totalRevenue' => Sale::where('business_id', $businessId)->sum('total'),
            'salesTodayCount' => Sale::where('business_id', $businessId)->whereDate('created_at', Carbon::today())->count(),
            'totalClientsCount' => Client::where('business_id', $businessId)->count(),
        ];

        // 3. Pasar tanto las ventas como las estadísticas a la vista
        // CAMBIO: Ya no es necesario pasar la variable $request.
        return view('pos.sales-list', compact('sales', 'stats'));
    }
}