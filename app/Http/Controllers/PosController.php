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
    //Cuentas X Cobrar
     public function accountsReceivable(Request $request)
    {
        $user = auth()->user();
        $query = Client::where('business_id', $user->business_id)
                       ->whereHas('sales', fn($q) => $q->where('status', 'Pendiente'));

        // Aplicar filtro de búsqueda si existe
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('document', 'like', "%{$search}%");
            });
        }

        $clientsWithDebt = $query->withSum(['sales' => fn($q) => $q->where('status', 'Pendiente')], 'pending_amount')
                                 ->paginate(10) // Paginamos los resultados
                                 ->withQueryString();
        
        $totalReceivable = Sale::where('business_id', $user->business_id)->where('status', 'Pendiente')->sum('pending_amount');

        return view('pos.accounts-receivable', compact('clientsWithDebt', 'totalReceivable'));
    }
    // NUEVO MÉTODO para la página de Estado de Cuenta
    public function clientStatement(Request $request, Client $client)
    {
        if ($client->business_id !== auth()->user()->business_id) {
            abort(403);
        }

        $user = auth()->user();
        $user->tokens()->delete();
        $apiToken = $user->createToken('pos-token')->plainTextToken;

        $pendingSalesQuery = $client->sales()
                                   ->where('status', 'Pendiente')
                                   ->orderBy('date', 'asc');

        $allPendingSales = $pendingSalesQuery->clone()->get();
        $pendingSales = $pendingSalesQuery->paginate(10)->withQueryString();
        
        $stats = [
            'total_invoices' => $allPendingSales->count(),
            'total_debt' => $allPendingSales->sum('pending_amount'),
            // CAMBIO: Se redondea el promedio de días
            'average_days' => round($allPendingSales->avg(fn($sale) => Carbon::parse($sale->date)->diffInDays(now()))),
        ];
        
        return view('pos.client-statement', compact('client', 'pendingSales', 'stats', 'apiToken'));
    }
}