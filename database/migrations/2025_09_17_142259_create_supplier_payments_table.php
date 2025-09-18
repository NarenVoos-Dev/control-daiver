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
        Schema::create('supplier_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained();
            $table->foreignId('supplier_id')->constrained()->onDelete('cascade');

            // El pago puede estar asociado a una compra específica
            $table->foreignId('purchase_id')->nullable()->constrained()->onDelete('set null');

            // Cada pago es un egreso, lo vinculamos para consistencia contable
            $table->foreignId('egress_id')->constrained()->onDelete('cascade');

            $table->decimal('amount', 12, 2);
            $table->date('payment_date');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('supplier_payments');
    }
};
