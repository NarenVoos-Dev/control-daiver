<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Client;
use App\Models\UnitOfMeasure;
use App\Models\Sale;
use App\Models\Zone;
use App\Models\Product;
use App\Models\{CashSession, CashSessionTransaction,Location, PaymentMethod, BankAccount , Payment};
use Carbon\Carbon;
use Illuminate\Http\Request; 
use Illuminate\Support\Facades\Log;

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
        $paymentMethods = PaymentMethod::where('business_id', $businessId)->where('is_active', true)->get();
        $bankAccounts = BankAccount::where('business_id', $businessId)->where('is_active', true)->get();

        return view('pos.index', compact(
            'categories', 
            'units', 
            'apiToken', 
            'zones',
            'paymentMethods', 
            'bankAccounts'
        ));
    }
    
    public function salesList(Request $request)
    {
        $user = auth()->user();
        $businessId = $user->business_id;

        // 1. Obtener las ventas paginadas y filtradas
        $salesQuery = Sale::where('business_id', $businessId)
                     ->with(['client', 'location'])
                     ->latest();
        
        if (!$user->hasRole('admin')) {
            // Si no es admin, buscamos su caja activa para saber su sucursal
            $activeSession = CashSession::where('user_id_opened', $user->id)
                                        ->where('status', 'Abierta')
                                        ->first();

            if ($activeSession && $activeSession->location_id) {
                // Filtramos las ventas para que solo muestre las de su sucursal
                $salesQuery->where('location_id', $activeSession->location_id);
            } else {
                // Si no es admin y no tiene caja abierta, no debería ver ninguna venta.
                // Esto es una medida de seguridad.
                $salesQuery->whereRaw('1 = 0'); // Una condición que siempre es falsa
            }
        }

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
        $paymentMethods = PaymentMethod::where('business_id', $user->business_id)->where('is_active', true)->get();
        $bankAccounts = BankAccount::where('business_id', $user->business_id)->where('is_active', true)->get();
   
        
        return view('pos.client-statement', compact('client', 'pendingSales', 'stats', 'apiToken','paymentMethods','bankAccounts' ));
    }
    // NUEVO: Muestra el formulario para abrir la caja
    public function showOpenCashRegisterForm()
    {
        $locations = Location::where('business_id', auth()->user()->business_id)->get();
        return view('pos.open-cash-register ', compact('locations'));
    }

    // NUEVO: Procesa la apertura de la caja
    public function openCashRegister(Request $request)
    {
        $request->validate([
            'opening_balance' => 'required|numeric|min:0',
            'location_id' => 'required|exists:locations,id',
        ]);

        $user = auth()->user();

        // Se crea la sesión de caja
        $session = CashSession::create([
            'business_id' => $user->business_id,
            'location_id' => $request->location_id,
            'user_id_opened' => $user->id,
            'opening_balance' => $request->opening_balance,
            'status' => 'Abierta',
            'opened_at' => now(),
        ]);

        // Se registra la primera transacción (la base inicial)
        $session->transactions()->create([
            'type' => 'entrada',
            'amount' => $request->opening_balance,
            'description' => 'Base inicial de caja',
        ]);

        return redirect()->route('pos.index');
    }
    // NUEVO: Muestra el formulario para cerrar la caja con el resumen
    public function showCloseCashRegisterForm(Request $request)
    {
        $activeSession = $request->get('active_session');
        if (!$activeSession) {
            return redirect()->route('pos.open_cash_register.form');
        }

        $activeSession->load('location');

        // Calcular el total esperado en caja
        $entradas = $activeSession->transactions()->where('type', 'entrada')->sum('amount');
        $salidas = $activeSession->transactions()->where('type', 'salida')->sum('amount');
        $calculatedBalance = $entradas - $salidas;

        // ===================================================================
        // DESGLOSE SEPARADO: VENTAS vs ABONOS
        // ===================================================================
        
        $salesByMethod = []; // Ventas de contado
        $paymentsByMethod = []; // Abonos a crédito
        $paymentMethodSummary = []; // Resumen total

        // ===================================================================
        // 1. VENTAS DE CONTADO (tabla sales)
        // ===================================================================
        $cashSales = Sale::where('cash_session_id', $activeSession->id)
            ->where('is_cash', true)
            ->with('paymentMethod')
            ->get();

        Log::info('=== VENTAS DE CONTADO ===');
        Log::info('Total ventas encontradas:', ['count' => $cashSales->count()]);

        foreach ($cashSales as $sale) {
            if ($sale->paymentMethod) {
                $methodName = $sale->paymentMethod->name;
                
                // Para el desglose de ventas
                if (!isset($salesByMethod[$methodName])) {
                    $salesByMethod[$methodName] = 0;
                }
                $salesByMethod[$methodName] += $sale->total;

                // Para el resumen total
                if (!isset($paymentMethodSummary[$methodName])) {
                    $paymentMethodSummary[$methodName] = 0;
                }
                $paymentMethodSummary[$methodName] += $sale->total;

                Log::info('Venta procesada:', [
                    'sale_id' => $sale->id,
                    'method' => $methodName,
                    'amount' => $sale->total
                ]);
            }
        }

        // ===================================================================
        // 2. ABONOS A CRÉDITO (tabla payments)
        // ===================================================================
        // Obtenemos los IDs de los payments registrados en esta sesión
        $paymentIds = $activeSession->transactions()
            ->where('source_type', Payment::class)
            ->pluck('source_id');

        // Obtenemos los Payments con su Sale relacionada
        $payments = Payment::whereIn('id', $paymentIds)
            ->with('paymentMethod')
            ->get();

        Log::info('=== ABONOS A CRÉDITO ===');
        Log::info('Total abonos encontrados:', ['count' => $payments->count()]);

        foreach ($payments as $payment) {
            if ($payment->paymentMethod) {
                $methodName = $payment->paymentMethod->name;

                // Para el desglose de abonos
                if (!isset($paymentsByMethod[$methodName])) {
                    $paymentsByMethod[$methodName] = 0;
                }
                $paymentsByMethod[$methodName] += $payment->amount;

                // Para el resumen total
                if (!isset($paymentMethodSummary[$methodName])) {
                    $paymentMethodSummary[$methodName] = 0;
                }
                $paymentMethodSummary[$methodName] += $payment->amount;

                Log::info('Abono procesado:', [
                    'payment_id' => $payment->id,
                    'sale_id' => $payment->sale_id,
                    'method' => $methodName,
                    'amount' => $payment->amount
                ]);
            }
        }

        // Ordenar todos los arrays
        ksort($salesByMethod);
        ksort($paymentsByMethod);
        ksort($paymentMethodSummary);

        Log::info('=== RESUMEN FINAL ===');
        Log::info('Ventas por método:', $salesByMethod);
        Log::info('Abonos por método:', $paymentsByMethod);
        Log::info('Total por método:', $paymentMethodSummary);

        return view('pos.close-cash-register', compact(
            'activeSession',
            'calculatedBalance',
            'paymentMethodSummary',
            'salesByMethod',
            'paymentsByMethod'
        ));
    }

    // NUEVO: Procesa el cierre de la caja
    public function closeCashRegister(Request $request)
    {
        $request->validate([
            'closing_balance' => 'required|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        $activeSession = CashSession::where('business_id', auth()->user()->business_id)
                                    ->where('status', 'Abierta')
                                    ->firstOrFail();

        $entradas = $activeSession->transactions()->where('type', 'entrada')->sum('amount');
        $salidas = $activeSession->transactions()->where('type', 'salida')->sum('amount');
        $calculatedBalance = $entradas - $salidas;
        $closingBalance = $request->closing_balance;
        $difference = $closingBalance - $calculatedBalance;

        $activeSession->update([
            'closing_balance' => $closingBalance,
            'calculated_balance' => $calculatedBalance,
            'difference' => $difference,
            'notes' => $request->notes,
            'status' => 'Cerrada',
            'user_id_closed' => auth()->id(),
            'closed_at' => now(),
        ]);

        return redirect()->route('pos.open_cash_register.form')->with('status', '¡Caja cerrada exitosamente!');
    }
}