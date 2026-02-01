<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('voto_geografico', function (Blueprint $table) {
            $table->id('id_geografico');
            $table->string('nombre', 255);
            $table->string('codigo', 50)->unique();
            $table->string('ubicacion', 255)->nullable();
            $table->enum('tipo', ['PAIS', 'CIUDAD', 'MUNICIPIO', 'LOCALIDAD', 'RECINTO']);
            $table->foreignId('fk_id_geografico')
                  ->nullable()
                  ->constrained('voto_geografico', 'id_geografico')
                  ->nullOnDelete()
                  ->cascadeOnUpdate();
            $table->timestamps();
            $table->softDeletes();
            
            // Índices
            $table->index('tipo');
            $table->index('fk_id_geografico');
            $table->index('codigo');
        });
        
        // Para MySQL añadir CHECK constraint
        if (DB::getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE voto_geografico ADD CONSTRAINT chk_tipo_valido CHECK (tipo IN ("PAIS", "CIUDAD", "MUNICIPIO", "LOCALIDAD", "RECINTO"))');
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('voto_geografico');
    }
};