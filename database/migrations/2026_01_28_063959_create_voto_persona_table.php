<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('voto_persona', function (Blueprint $table) {
            $table->id('id_persona');
            $table->string('nombre', 255);
            $table->string('apellido_paterno', 255);
            $table->string('apellido_materno', 255);
            $table->string('ci', 50)->unique();
            $table->string('celular', 20)->nullable();
            $table->string('email', 255)->nullable()->unique();
            $table->timestamps();
            $table->softDeletes();
            
            // Índices
            $table->index('ci');
            $table->index('email');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('voto_persona');
    }
};