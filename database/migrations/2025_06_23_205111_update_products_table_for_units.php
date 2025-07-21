<?php

//migrations para eliminar tabla unit inicial creara la relacion con la tabla 
//unidades de medidas 

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
        Schema::table('products', function (Blueprint $table) {
            // Eliminamos la columna de texto antigua
            $table->dropColumn('unit');

            // Añadimos la nueva llave foránea para la unidad de medida base del producto
            $table->foreignId('unit_of_measure_id')
                  ->after('sku')
                  ->nullable()
                  ->constrained('unit_of_measures')
                  ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropForeign(['unit_of_measure_id']);
            $table->dropColumn('unit_of_measure_id');
            $table->string('unit')->default('unidad')->after('sku');
        });
    }
};
