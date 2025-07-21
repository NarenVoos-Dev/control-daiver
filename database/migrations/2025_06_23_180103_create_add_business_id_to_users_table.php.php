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
        Schema::table('users', function (Blueprint $table) {
            // Solamente añadimos la llave foránea para el multi-tenancy.
            // La columna 'role' ya no es necesaria.
            $table->foreignId('business_id')
                  ->after('id')
                  ->nullable()
                  ->constrained()
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Importante tener el método down para poder revertir los cambios.
            $table->dropForeign(['business_id']);
            $table->dropColumn('business_id');
        });
    }
};
