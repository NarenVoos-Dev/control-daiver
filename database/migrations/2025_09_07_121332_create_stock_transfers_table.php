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
        Schema::create('stock_transfers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained();
            $table->foreignId('user_id')->constrained()->comment('Usuario que realizó el traslado');
            
            // Define las claves foráneas a la tabla 'locations'
            $table->foreignId('origin_location_id')->constrained('locations'); // Bodega de Origen
            $table->foreignId('destination_location_id')->constrained('locations'); // Bodega de Destino
            
            $table->date('date');
            $table->text('notes')->nullable();
            $table->string('status')->default('Completado');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_transfers');
    }
};
