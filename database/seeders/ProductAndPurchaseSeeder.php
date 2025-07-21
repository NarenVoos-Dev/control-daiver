<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Business;
use App\Models\Category;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\Purchase;
use App\Models\StockMovement;
use App\Models\UnitOfMeasure;
use Illuminate\Support\Facades\DB;

class ProductAndPurchaseSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            $business = Business::first();
            if (!$business) {
                $this->command->error('No business found. Please run UserSeeder first.');
                return;
            }

            // PROVEEDORES
            $supplier1 = Supplier::firstOrCreate(['name' => 'FerreHogar S.A.S', 'business_id' => $business->id]);
            $supplier2 = Supplier::firstOrCreate(['name' => 'ConstruCentro Colombia', 'business_id' => $business->id]);

            // UNIDADES DE MEDIDA
            $uUnidad = UnitOfMeasure::where('abbreviation', 'und')->firstOrFail();
            $uCaja100 = UnitOfMeasure::firstOrCreate(['name' => 'Caja de 100', 'abbreviation' => 'cja100', 'business_id' => $business->id], ['conversion_factor' => 100]);

            // CATEGORÍAS Y TIPOS
            $categoriesData = [
                'Tornillería'     => ['Tornillo', 'Chazo', 'Tuerca', 'Arandela'],
                'Pinturas'        => ['Pintura Acrílica', 'Esmalte', 'Laca', 'Barniz'],
                'Construcción'    => ['Cemento', 'Arena', 'Grava', 'Bloque', 'Ladrillo'],
                'Electricidad'    => ['Cable', 'Tomacorriente', 'Interruptor', 'Breaker'],
                'Plomería'        => ['Tubo PVC', 'Cinta de teflón', 'Llave de paso', 'Codo PVC'],
                'Herramientas'    => ['Martillo', 'Destornillador', 'Tenaza', 'Llave inglesa'],
                'Adhesivos'       => ['Silicona', 'Pegante', 'Resina', 'Espuma expansiva'],
            ];

            $categoryModels = [];
            foreach ($categoriesData as $categoryName => $subtypes) {
                $categoryModels[$categoryName] = Category::firstOrCreate(['name' => $categoryName, 'business_id' => $business->id]);
            }

            // CREACIÓN MASIVA DE PRODUCTOS
            $maxProducts = 500;
            $productCount = 0;

            foreach ($categoriesData as $categoryName => $subtypes) {
                foreach ($subtypes as $subtype) {
                    for ($i = 1; $i <= 25; $i++) {
                        if ($productCount >= $maxProducts) break 3; // Detener después de 500
                        $name = "{$subtype} Modelo {$i}";
                        Product::updateOrCreate(
                            ['name' => $name, 'business_id' => $business->id],
                            [
                                'category_id' => $categoryModels[$categoryName]->id,
                                'unit_of_measure_id' => $uUnidad->id,
                                'cost' => rand(200, 15000),
                                'price' => rand(16000, 60000),
                                'stock' => 0,
                                'business_id' => $business->id,
                            ]
                        );
                        $productCount++;
                    }
                }
            }

            $this->command->info("✅ Se crearon o actualizaron $productCount productos.");

            // OPCIONAL: CREAR UNA COMPRA DE ALGUNOS PRODUCTOS ALEATORIOS
            $someProducts = Product::where('business_id', $business->id)->inRandomOrder()->limit(10)->get();
            foreach ($someProducts as $product) {
                $qty = rand(1, 20);
                $cost = rand(1000, 5000);
                $this->createPurchase($business->id, $supplier1->id, [
                    ['name' => $product->name, 'qty' => $qty, 'unit' => $uUnidad, 'cost' => $cost],
                ]);
            }

            $this->command->info('✅ Se generaron algunas compras de prueba.');
        });
    }

    private function createPurchase($businessId, $supplierId, array $items)
    {
        $total = 0;
        foreach ($items as $item) {
            $total += $item['qty'] * $item['cost'];
        }

        $purchase = Purchase::create([
            'business_id' => $businessId,
            'supplier_id' => $supplierId,
            'date' => now()->subDays(rand(1, 30)),
            'total' => $total,
        ]);

        foreach ($items as $itemData) {
            $product = Product::where('name', $itemData['name'])->firstOrFail();
            $unit = $itemData['unit'];

            $purchase->items()->create([
                'product_id' => $product->id,
                'quantity' => $itemData['qty'],
                'price' => $itemData['cost'],
                'unit_of_measure_id' => $unit->id,
            ]);

            $quantityInBaseUnits = (float)$itemData['qty'] * (float)$unit->conversion_factor;
            $costPerBaseUnit = ((float)$unit->conversion_factor > 0)
                ? (float)$itemData['cost'] / (float)$unit->conversion_factor
                : 0;

            Product::where('id', $product->id)->update([
                'stock' => DB::raw("stock + {$quantityInBaseUnits}"),
                'cost' => $costPerBaseUnit,
            ]);

            StockMovement::create([
                'product_id' => $product->id,
                'type' => 'entrada',
                'quantity' => $quantityInBaseUnits,
                'source_type' => get_class($purchase),
                'source_id' => $purchase->id,
            ]);
        }
    }
}
