<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\UnitOfMeasure;
use App\Models\Business;

class UnitOfMeasureSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        // Buscamos el primer negocio para asociarle estas unidades.
        $business = Business::first();
        if (!$business) {
            $this->command->info('No se encontró ningún negocio. Saltando el UnitOfMeasureSeeder.');
            return;
        }

        $units = [
            // --- UNIDADES BASE Y DE CONTEO ---
            ['name' => 'Unidad', 'abbreviation' => 'und', 'conversion_factor' => 1],
            ['name' => 'Docena', 'abbreviation' => 'doc', 'conversion_factor' => 12],
            ['name' => 'Ciento', 'abbreviation' => 'ciento', 'conversion_factor' => 100],
            ['name' => 'Millar', 'abbreviation' => 'millar', 'conversion_factor' => 1000],

            // --- UNIDADES DE LONGITUD (Base: Metro) ---
            ['name' => 'Metro', 'abbreviation' => 'm', 'conversion_factor' => 1],
            ['name' => 'Centímetro', 'abbreviation' => 'cm', 'conversion_factor' => 0.01],
            ['name' => 'Pulgada', 'abbreviation' => 'in', 'conversion_factor' => 0.0254],
            ['name' => 'Pie', 'abbreviation' => 'ft', 'conversion_factor' => 0.3048],
            ['name' => 'Vara', 'abbreviation' => 'vara', 'conversion_factor' => 0.8],

            // --- UNIDADES DE PESO (Base: Kilogramo) ---
            ['name' => 'Kilogramo', 'abbreviation' => 'kg', 'conversion_factor' => 1],
            ['name' => 'Gramo', 'abbreviation' => 'g', 'conversion_factor' => 0.001],
            ['name' => 'Libra (500g)', 'abbreviation' => 'lb', 'conversion_factor' => 0.5],
            ['name' => 'Arroba (12.5kg)', 'abbreviation' => '@', 'conversion_factor' => 12.5],
            ['name' => 'Bulto (Cemento 50kg)', 'abbreviation' => 'bulto', 'conversion_factor' => 50],
            ['name' => 'Tonelada', 'abbreviation' => 'ton', 'conversion_factor' => 1000],

            // --- UNIDADES DE VOLUMEN (Base: Litro) ---
            ['name' => 'Litro', 'abbreviation' => 'L', 'conversion_factor' => 1],
            ['name' => 'Mililitro', 'abbreviation' => 'mL', 'conversion_factor' => 0.001],
            ['name' => 'Cuarto de Galón', 'abbreviation' => '1/4 G', 'conversion_factor' => 0.946],
            ['name' => 'Medio Galón', 'abbreviation' => '1/2 G', 'conversion_factor' => 1.892],
            ['name' => 'Galón', 'abbreviation' => 'G', 'conversion_factor' => 3.785],
            ['name' => 'Caneca (5 Galones)', 'abbreviation' => 'caneca', 'conversion_factor' => 18.925],

            // --- UNIDADES DE ÁREA ---
            ['name' => 'Metro Cuadrado', 'abbreviation' => 'm²', 'conversion_factor' => 1],

            // --- UNIDADES DE EMPAQUE (El factor representa cuántas unidades base contiene) ---
            // Se recomienda crear unidades más específicas si es necesario, ej: "Caja de 50" con factor 50.
            ['name' => 'Caja', 'abbreviation' => 'cja', 'conversion_factor' => 1],
            ['name' => 'Paquete', 'abbreviation' => 'pqt', 'conversion_factor' => 1],
            ['name' => 'Bolsa', 'abbreviation' => 'bls', 'conversion_factor' => 1],
            ['name' => 'Rollo', 'abbreviation' => 'rollo', 'conversion_factor' => 1],
        ];

        foreach ($units as $unit) {
            UnitOfMeasure::firstOrCreate(
                [
                    'business_id' => $business->id,
                    'name' => $unit['name'],
                ],
                [
                    'abbreviation' => $unit['abbreviation'],
                    'conversion_factor' => $unit['conversion_factor'],
                ]
            );
        }

        $this->command->info('Unidades de medida cargadas exitosamente.');
    }
}