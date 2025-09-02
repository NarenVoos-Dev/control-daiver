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
        Schema::create('egresses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained();
            $table->foreignId('user_id')->constrained(); // Quién registró el egreso

            // --- Vínculo opcional con la compra ---
            $table->foreignId('purchase_id')->nullable()->constrained()->onDelete('set null');

            $table->string('type')->comment('compra, gasto, retiro');
            $table->text('description');
            $table->decimal('amount', 12, 2);
            $table->string('payment_method')->comment('efectivo, crédito, transferencia');
            $table->foreignId('supplier_id')->nullable()->constrained(); // Vínculo opcional con proveedor

            // --- Vínculo opcional con la caja ---
            $table->foreignId('cash_session_id')->nullable()->constrained();

            $table->date('date');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('egresses');
    }
};
