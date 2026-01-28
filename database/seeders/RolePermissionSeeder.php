<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\VotoUsuario;
use App\Models\VotoPersona;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Crear permisos
        $permissions = [
            // Dashboard
            'ver-dashboard',
            
            // Personas
            'ver-personas', 'crear-personas', 'editar-personas', 'eliminar-personas',
            
            // Usuarios
            'ver-usuarios', 'crear-usuarios', 'editar-usuarios', 'eliminar-usuarios', 'asignar-roles',
            
            // Geográfico
            'ver-geografico', 'crear-geografico', 'editar-geografico', 'eliminar-geografico',
            
            // Mesas
            'ver-mesas', 'crear-mesas', 'editar-mesas', 'eliminar-mesas',
            
            // Tipo Elección
            'ver-tipos-eleccion', 'crear-tipos-eleccion', 'editar-tipos-eleccion', 'eliminar-tipos-eleccion',
            
            // Partidos (futuro)
            'ver-partidos', 'crear-partidos', 'editar-partidos', 'eliminar-partidos',
            
            // Candidatos (futuro)
            'ver-candidatos', 'crear-candidatos', 'editar-candidatos', 'eliminar-candidatos',
            
            // Votación
            'registrar-votos', 'ver-resultados', 'exportar-resultados',
            
            // Sistema
            'ver-logs', 'ver-reportes', 'configurar-sistema'
        ];
        
        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission]);
        }

        // Crear roles y asignar permisos
        $roleAdmin = Role::create(['name' => 'ADMIN']);
        $roleAdmin->givePermissionTo(Permission::all());
        
        $roleOperador = Role::create(['name' => 'OPERADOR']);
        $roleOperador->givePermissionTo([
            'ver-dashboard',
            'ver-personas',
            'registrar-votos',
            'ver-resultados'
        ]);
        
        $roleAdminSecundario = Role::create(['name' => 'ADMIN_SECUNDARIO']);
        $roleAdminSecundario->givePermissionTo([
            'ver-dashboard',
            'ver-personas', 'crear-personas', 'editar-personas',
            'ver-usuarios', 'crear-usuarios', 'editar-usuarios',
            'ver-geografico', 'crear-geografico', 'editar-geografico',
            'ver-mesas', 'crear-mesas', 'editar-mesas',
            'ver-tipos-eleccion', 'crear-tipos-eleccion', 'editar-tipos-eleccion',
            'ver-partidos', 'crear-partidos', 'editar-partidos',
            'ver-candidatos', 'crear-candidatos', 'editar-candidatos'
        ]);
        
        // Crear usuario admin por defecto
        $personaAdmin = VotoPersona::create([
            'nombre' => 'Administrador',
            'apellido_paterno' => 'Sistema',
            'apellido_materno' => 'Balotaje',
            'ci' => '0000000',
            'email' => 'admin@balotaje.com'
        ]);
        
        $usuarioAdmin = VotoUsuario::create([
            'nombre_usuario' => 'admin',
            'contrasena' => 'admin123',
            'id_persona' => $personaAdmin->id_persona,
            'activo' => true
        ]);
        
        $usuarioAdmin->assignRole('ADMIN');
        
        $this->command->info('✅ Roles y permisos creados exitosamente!');
        $this->command->info('   Usuario admin: admin / admin123');
        $this->command->info('   Roles: ADMIN, OPERADOR, ADMIN_SECUNDARIO');
    }
}