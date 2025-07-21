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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained()->onDelete('cascade');
            $table->foreignId('category_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('sku')->nullable()->unique()->comment('Stock Keeping Unit'); // Codigo unico del producto
            $table->string('unit')->default('unidad')->comment('Ej: unidad, metro, litro, caja');
            $table->decimal('price', 10, 2)->comment('Precio de venta al público');
            $table->decimal('cost', 10, 2)->nullable()->comment('Costo de adquisición'); //Cantidad actual de producto del producto
            $table->decimal('stock', 12, 4)->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
