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
        Schema::create('reportes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('idUser')->constrained('users')->cascadeOnDelete();
            $table->string('Trabajo', 250);
            $table->date('FechaInicio');
            $table->date('FechaFin');
            $table->boolean('Revisado')->default(false);
            $table->string('Observaciones', 256)->nullable();
            $table->string('Comentarios', 256)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reportes');
    }
};
