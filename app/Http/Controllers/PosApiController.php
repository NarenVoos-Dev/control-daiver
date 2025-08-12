<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\{Product, Sale, StockMovement, UnitOfMeasure, Client, Category};
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class PosApiController extends Controller
{
    public function searchProducts(Request $request)
    {
        $query = Product::query()
            ->where('business_id', auth()->user()->business_id)
            ->with('unitOfMeasure'); // Eager load para eficiencia

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

        return response()->json($query->get());
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
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $client = Client::create(array_merge($request->all(), ['business_id' => auth()->user()->business_id]));
        return response()->json(['success' => true, 'client' => $client]);
    }

    public function storeSale(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'client_id' => 'required|exists:clients,id',
            'cart' => 'required|array|min:1',
            'cart.*.product_id' => 'required|exists:products,id',
            'cart.*.quantity' => 'required|numeric|min:1',
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
                    $itemSubtotal = (float)$item['quantity'] * (float)$item['price'];
                    $subtotal += $itemSubtotal;
                    $tax += $itemSubtotal * ((float)($item['tax_rate'] ?? 0) / 100);
                }
                $sale = Sale::create([ 'business_id' => auth()->user()->business_id, 'client_id' => $request->input('client_id'), 'date' => now(), 'subtotal' => $subtotal, 'tax' => $tax, 'total' => $subtotal + $tax ]);
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
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}