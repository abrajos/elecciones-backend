<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\VotoGeografico;
use App\Models\VotoTipoEleccion;
use App\Models\VotoPersona;
use App\Models\VotoUsuario;
use Spatie\Permission\Models\Role;

class InitialDataSeeder extends Seeder
{
    public function run(): void
    {
        // Crear estructura geográfica
        $bolivia = VotoGeografico::create([
            'nombre' => 'Bolivia',
            'codigo' => 'BOL',
            'tipo' => 'PAIS'
        ]);
        
        $laPaz = VotoGeografico::create([
            'nombre' => 'La Paz',
            'codigo' => 'LP',
            'tipo' => 'CIUDAD',
            'fk_id_geografico' => $bolivia->id_geografico
        ]);
        
        $municipio = VotoGeografico::create([
            'nombre' => 'La Paz',
            'codigo' => 'LP-MUN',
            'tipo' => 'MUNICIPIO',
            'fk_id_geografico' => $laPaz->id_geografico
        ]);
        
        $localidad = VotoGeografico::create([
            'nombre' => 'Centro',
            'codigo' => 'CTR',
            'tipo' => 'LOCALIDAD',
            'fk_id_geografico' => $municipio->id_geografico
        ]);
        
        $recinto = VotoGeografico::create([
            'nombre' => 'Colegio Bolívar',
            'codigo' => 'CBOL',
            'ubicacion' => 'Av. Arce #1234',
            'tipo' => 'RECINTO',
            'fk_id_geografico' => $localidad->id_geografico
        ]);
        
        // Crear tipos de elección
        VotoTipoEleccion::create([
            'nombre' => 'Presidencial',
            'codigo' => 'PRE',
            'descripcion' => 'Elección para Presidente y Vicepresidente',
            'max_candidatos' => 2
        ]);
        
        VotoTipoEleccion::create([
            'nombre' => 'Diputados',
            'codigo' => 'DIP',
            'descripcion' => 'Elección para Diputados',
            'max_candidatos' => 1
        ]);
        
        // Crear usuarios de ejemplo para cada rol
        $roles = Role::all();
        
        foreach ($roles as $role) {
            $persona = VotoPersona::create([
                'nombre' => 'Usuario',
                'apellido_paterno' => ucfirst(strtolower($role->name)),
                'apellido_materno' => 'Demo',
                'ci' => '111111' . $role->id,
                'email' => strtolower($role->name) . '@demo.com'
            ]);
            
            $usuario = VotoUsuario::create([
                'nombre_usuario' => strtolower($role->name),
                'contrasena' => 'demo123',
                'id_persona' => $persona->id_persona,
                'activo' => true
            ]);
            
            $usuario->assignRole($role->name);
        }
        
        $this->command->info('✅ Datos iniciales creados exitosamente!');
        $this->command->info('   Estructura geográfica: País > Ciudad > Municipio > Localidad > Recinto');
        $this->command->info('   Tipos de elección: Presidencial, Diputados');
        $this->command->info('   Usuarios demo: admin, operador, admin_secundario');
        $this->command->info('   Contraseña demo para todos: demo123');
    }
}