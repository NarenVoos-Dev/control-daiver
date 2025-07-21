// Migracion para las medidas de unidad de los productos cuando se trata de paquetes * X cantidad

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('unit_of_measures', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained()->onDelete('cascade'); //Empresa ID
            $table->string('name')->comment('Ej: Unidad, Caja, Bolsa de 100'); //nombre de la unidad
            $table->string('abbreviation')->comment('Ej: und, cja, bls'); // Abreviacion de la unnidad
            $table->decimal('conversion_factor', 12, 4)->default(1) //Factura de unidades base ejemplo bolsa de 100 contiene 100 unidades
                  ->comment('Cuántas unidades base contiene. Ej: "Bolsa de 100" tiene un factor de 100.');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('unit_of_measures');
    }
};