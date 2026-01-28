<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('voto_mesa', function (Blueprint $table) {
            $table->id('id_mesa');
            $table->string('codigo', 50)->unique();
            $table->string('nombre', 255)->nullable();
            $table->text('descripcion')->nullable();
            $table->integer('numero_personas')->default(0);
            $table->foreignId('id_recinto')->constrained('voto_geografico', 'id_geografico');
            $table->boolean('activa')->default(true);
            $table->timestamps();
            $table->softDeletes();

            // Índices
            $table->index('codigo');
            $table->index('id_recinto');
            $table->index('activa');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('voto_mesa');
    }
};
