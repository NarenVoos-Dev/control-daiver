<?php
//Agregamos la unidad de medias en la tabla items de compras
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
        Schema::table('purchase_items', function (Blueprint $table) {
            $table->foreignId('unit_of_measure_id')
                  ->after('price')
                  ->nullable()
                  ->constrained('unit_of_measures')
                  ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('purchase_items', function (Blueprint $table) {
            $table->dropForeign(['unit_of_measure_id']);
            $table->dropColumn('unit_of_measure_id');
        });
    }
};
