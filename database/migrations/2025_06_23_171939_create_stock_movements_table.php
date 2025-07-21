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
        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->enum('type', ['entrada', 'salida', 'ajuste']);
            $table->decimal('quantity', 10, 2);
            $table->string('source_type')->nullable()->comment('Origen del movimiento: App\\Models\\Purchase, App\\Models\\Sale');
            $table->unsignedBigInteger('source_id')->nullable();
            $table->index(['source_type', 'source_id']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_movements');
    }
};
