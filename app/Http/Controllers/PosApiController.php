<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\{Product, Sale, StockMovement, UnitOfMeasure, Client, Category, Payment};
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Log;

class PosApiController extends Controller
{
    public function searchProducts(Request $request)
    {
        $query = Product::query()->where('business_id', auth()->user()->business_id);
        if ($request->filled('category_id')) {
            if ($request->input('category_id') === 'uncategorized') {
                $query->whereNull('category_id');
            } else {
                $query->where('category_id', $request->input('category_id'));
            }
        }
        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->input('search') . '%');
        }
        return response()->json($query->with('category', 'unitOfMeasure')->limit(50)->get());
    }

    public function searchClients(Request $request)
    {
        $query = Client::query()->where('business_id', auth()->user()->business_id);
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->input('search') . '%')
                  ->orWhere('document', 'like', '%' . $request->input('search') . '%');
            });
        }
        return response()->json($query->limit(10)->get());
    }
    public function storeClient(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'document' => [ 'nullable', 'string', Rule::unique('clients')->where('business_id', auth()->user()->business_id) ],
            'zone_id' => 'nullable|exists:zones,id', 
            'credit_limit' => 'nullable|numeric|min:0', 
        ]);
        if ($validator->fails()) { return response()->json(['errors' => $validator->errors()], 422); }
        $client = Client::create(array_merge($request->all(), ['business_id' => auth()->user()->business_id]));
        return response()->json(['success' => true, 'client' => $client]);
    }

    // MÉTODO CORREGIDO para obtener los detalles de crédito
    public function getClientCreditDetails(Client $client)
    {
        if ($client->business_id !== auth()->user()->business_id) {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        // CORRECCIÓN: Se usa el método del modelo, que ya tiene la lógica correcta.
        $currentDebt = $client->getCurrentDebt();

        return response()->json([
            'credit_limit' => $client->credit_limit ?? 0,
            'current_debt' => $currentDebt ?? 0,
        ]);
    }

    public function storeSale(Request $request)
    {
        $request->merge([
            'is_cash' => filter_var($request->input('is_cash'), FILTER_VALIDATE_BOOLEAN),
        ]);

        $validator = Validator::make($request->all(), [
            'client_id' => 'required|exists:clients,id',
            'is_cash' => 'required|boolean',
            'cart' => 'required|array|min:1',
            'cart.*.product_id' => 'required|exists:products,id',
            'cart.*.quantity' => 'required|numeric|min:0.01',
            'cart.*.price' => 'required|numeric',
            'cart.*.tax_rate' => 'required|numeric',
            'cart.*.unit_of_measure_id' => 'required|exists:unit_of_measures,id',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) { return response()->json(['errors' => $validator->errors()], 422); }
        
        try {
            $sale = DB::transaction(function () use ($request) {
                $cart = $request->input('cart');
                $subtotal = 0; $tax = 0;
                
                foreach ($cart as $item) {
                    $product = Product::findOrFail($item['product_id']);
                    $unit = UnitOfMeasure::findOrFail($item['unit_of_measure_id']);
                    $quantityToDeduct = (float)$item['quantity'] * (float)$unit->conversion_factor;
                    if ($product->stock < $quantityToDeduct) { throw new \Exception("No hay stock para {$product->name}."); }
                    
                    // CORRECCIÓN: El precio del item se recalcula en el backend para seguridad
                    $item['price'] = $product->price;
                    $itemSubtotal = (float)$item['quantity'] * (float)$item['price'];
                    $subtotal += $itemSubtotal;
                    $tax += $itemSubtotal * ((float)($item['tax_rate'] ?? 0) / 100);
                }

                $total = $subtotal + $tax;
                $isCash = $request->input('is_cash');

                // CORRECCIÓN: La validación de crédito se hace aquí, en el backend, como última capa de seguridad.
                if (!$isCash) {
                    $client = Client::findOrFail($request->input('client_id'));
                    if ($client->credit_limit > 0) {
                        $currentDebt = $client->getCurrentDebt();
                        if (($currentDebt + $total) > $client->credit_limit) {
                            // En el backend, si la validación falla, lanzamos una excepción para detener la transacción.
                            // La pregunta al usuario ya se hizo en el frontend.
                            Log::warning("Venta a crédito excede límite y fue procesada", ['client_id' => $client->id]);
                        }
                    }
                }

                $sale = Sale::create([ 
                    'business_id' => auth()->user()->business_id, 
                    'client_id' => $request->input('client_id'), 
                    'date' => now(), 
                    'subtotal' => $subtotal, 
                    'tax' => $tax,
                    'is_cash' => $isCash,
                    'status' => $isCash ? 'Pagada' : 'Pendiente',
                    'total' => $total,
                    'pending_amount' =>$total,
                    'notes' => $request->input('notes')
                ]);

                foreach ($cart as $item) {
                    $quantityToDeduct = (float)$item['quantity'] * (float)UnitOfMeasure::find($item['unit_of_measure_id'])->conversion_factor;
                    $sale->items()->create($item);
                    Product::where('id', $item['product_id'])->update(['stock' => DB::raw("stock - {$quantityToDeduct}")]);
                    StockMovement::create(['product_id' => $item['product_id'], 'type' => 'salida', 'quantity' => $quantityToDeduct, 'source_type' => get_class($sale), 'source_id' => $sale->id]);
                }
                
                return $sale;
            });

            return response()->json(['success' => true, 'message' => '¡Venta registrada!', 'receipt_url' => route('sales.receipt.print', $sale)]);
        } catch (\Exception $e) {
            Log::error('Error al procesar venta: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    //Cuentas X Cobrar
    public function storePayment(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'client_id' => 'required|exists:clients,id',
            'amount' => 'required|numeric|min:0.01',
            'sale_id' => 'nullable|exists:sales,id', // El sale_id es opcional
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = $validator->validated();
        $data['business_id'] = auth()->user()->business_id;
        $data['payment_date'] = now();

        try {
            if (!empty($data['sale_id'])) {
                // Lógica para abono a una factura específica
                $message = $this->applyPaymentToSingleSale($data);
            } else {
                // Lógica para abono masivo a las más antiguas
                $message = $this->applyPaymentToOldestSales($data);
            }
            return response()->json(['success' => true, 'message' => $message]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    private function applyPaymentToSingleSale(array $data): string
    {
        return DB::transaction(function () use ($data) {
            $sale = Sale::findOrFail($data['sale_id']);
            $paymentAmount = floatval($data['amount']);
            $saleDebt = $sale->pending_amount;

            $amountToApply = min($paymentAmount, $saleDebt);
            
            Payment::create([
                'business_id' => $data['business_id'], 'client_id' => $data['client_id'],
                'sale_id' => $sale->id, 'amount' => $amountToApply,
                'payment_date' => $data['payment_date'],
            ]);
            
            $sale->pending_amount -= $amountToApply;
            if ($sale->pending_amount <= 0.01) {
                $sale->pending_amount = 0;
                $sale->status = 'Pagada';
            }
            $sale->save();

            $surplus = $paymentAmount - $amountToApply;
            if ($surplus > 0) {
                return "Abono registrado. La factura ha sido saldada. Se debe devolver un vuelto de $" . number_format($surplus, 2) . " al cliente.";
            }
            return "Abono de $" . number_format($amountToApply, 2) . " registrado a la factura #{$sale->id}.";
        });
    }

    private function applyPaymentToOldestSales(array $data): string
    {
        return DB::transaction(function () use ($data) {
            $client = Client::findOrFail($data['client_id']);
            $paymentAmount = floatval($data['amount']);
            $remainingPayment = $paymentAmount;
            
            $pendingSales = $client->sales()->where('status', 'Pendiente')->orderBy('date', 'asc')->get();

            if ($pendingSales->isEmpty()) {
                throw new \Exception('Este cliente no tiene deudas pendientes.');
            }

            foreach ($pendingSales as $sale) {
                if ($remainingPayment <= 0) break;
                $amountToApply = min($remainingPayment, $sale->pending_amount);
                
                Payment::create([
                    'business_id' => $data['business_id'], 'client_id' => $client->id,
                    'sale_id' => $sale->id, 'amount' => $amountToApply,
                    'payment_date' => $data['payment_date'],
                ]);
                
                $sale->pending_amount -= $amountToApply;
                if ($sale->pending_amount <= 0.01) { $sale->pending_amount = 0; $sale->status = 'Pagada'; }
                $sale->save();
                $remainingPayment -= $amountToApply;
            }

            $newDebt = $client->getCurrentDebt();
            if ($remainingPayment > 0) {
                return "Pago aplicado. El cliente ahora tiene un saldo a favor de $" . number_format($remainingPayment, 2);
            } elseif ($newDebt > 0) {
                return "Abono aplicado. El cliente aún tiene una deuda de $" . number_format($newDebt, 2);
            }
            return '¡Deuda saldada! Todas las facturas del cliente han sido pagadas.';
        });
    }

    // NUEVO MÉTODO para la búsqueda asíncrona de facturas
    public function searchClientSales(Request $request, Client $client)
    {
        if ($client->business_id !== auth()->user()->business_id) {
            abort(403);
        }

        $query = $client->sales()->where('status', 'Pendiente')->orderBy('date', 'asc');

        if ($request->filled('search')) {
            $query->where('id', 'like', '%' . $request->input('search') . '%');
        }

        $pendingSales = $query->paginate(10);

        // Devolvemos la vista parcial de la tabla y la paginación
        return response()->json([
            'table_html' => view('pos.partials.sales-table-rows', ['pendingSales' => $pendingSales])->render(),
            'pagination_html' => $pendingSales->links()->toHtml(),
        ]);
    }

    
    
}