<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('voto_usuario', function (Blueprint $table) {
            $table->id('id_usuario');
            $table->string('nombre_usuario', 255)->unique();
            $table->string('contrasena', 255);
            $table->date('fecha_fin')->nullable();
            $table->string('token', 255)->nullable();
            $table->boolean('activo')->default(true);
            $table->foreignId('id_persona')->constrained('voto_persona', 'id_persona');
            $table->timestamps();
            $table->softDeletes();

            // Índices
            $table->index('nombre_usuario');
            $table->index('activo');
            $table->index('id_persona');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('voto_usuario');
    }
};
