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
         Schema::create('cash_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id_opened')->constrained('users')->cascadeOnDelete();
            $table->foreignId('user_id_closed')->nullable()->constrained('users')->nullOnDelete();
            $table->decimal('opening_balance', 15, 2);
            $table->decimal('closing_balance', 15, 2)->nullable(); // Contado por el usuario
            $table->decimal('calculated_balance', 15, 2)->nullable(); // Calculado por el sistema
            $table->decimal('difference', 15, 2)->nullable();
            $table->string('status')->default('Abierta'); // Abierta, Cerrada
            $table->text('notes')->nullable();
            $table->timestamp('opened_at');
            $table->timestamp('closed_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cash_sessions');
    }
};
