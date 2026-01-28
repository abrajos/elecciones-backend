<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\VotoUsuario;
use App\Models\VotoPersona;
use Spatie\Permission\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;

class UsuarioController extends Controller
{
    
    
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if (!$request->user()->can('ver-usuarios')) {
            return response()->json([
                'success' => false,
                'message' => 'No tiene permiso para ver usuarios'
            ], 403);
        }
        
        try {
            $usuarios = VotoUsuario::with(['persona', 'roles'])
                ->orderBy('nombre_usuario')
                ->paginate($request->get('per_page', 15));
            
            return response()->json([
                'success' => true,
                'message' => 'Usuarios obtenidos correctamente',
                'data' => $usuarios
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener usuarios: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        if (!$request->user()->can('crear-usuarios')) {
            return response()->json([
                'success' => false,
                'message' => 'No tiene permiso para crear usuarios'
            ], 403);
        }
        
        try {
            $validator = Validator::make($request->all(), [
                'nombre_usuario' => 'required|string|max:255|unique:voto_usuario',
                'contrasena' => 'required|string|min:6',
                'id_persona' => 'required|integer|exists:voto_persona,id_persona',
                'fecha_fin' => 'nullable|date',
                'activo' => 'sometimes|boolean',
                'roles' => 'required|array',
                'roles.*' => 'exists:roles,name'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Errores de validación',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Verificar que la persona no tenga ya un usuario
            $personaConUsuario = VotoUsuario::where('id_persona', $request->id_persona)->first();
            if ($personaConUsuario) {
                return response()->json([
                    'success' => false,
                    'message' => 'Esta persona ya tiene un usuario asignado'
                ], 422);
            }

            $usuario = VotoUsuario::create([
                'nombre_usuario' => $request->nombre_usuario,
                'contrasena' => Hash::make($request->contrasena),
                'id_persona' => $request->id_persona,
                'fecha_fin' => $request->fecha_fin,
                'activo' => $request->activo ?? true
            ]);

            // Asignar roles
            if ($request->has('roles')) {
                $usuario->syncRoles($request->roles);
            }

            return response()->json([
                'success' => true,
                'message' => 'Usuario creado correctamente',
                'data' => $usuario->load(['persona', 'roles'])
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al crear usuario: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        try {
            $usuario = VotoUsuario::with(['persona', 'roles', 'permissions'])->find($id);
            
            if (!$usuario) {
                return response()->json([
                    'success' => false,
                    'message' => 'Usuario no encontrado'
                ], 404);
            }
            
            if (!request()->user()->can('ver-usuarios')) {
                return response()->json([
                    'success' => false,
                    'message' => 'No tiene permiso para ver usuarios'
                ], 403);
            }
            
            return response()->json([
                'success' => true,
                'message' => 'Usuario obtenido correctamente',
                'data' => $usuario
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener usuario: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        if (!$request->user()->can('editar-usuarios')) {
            return response()->json([
                'success' => false,
                'message' => 'No tiene permiso para editar usuarios'
            ], 403);
        }
        
        try {
            $usuario = VotoUsuario::find($id);
            
            if (!$usuario) {
                return response()->json([
                    'success' => false,
                    'message' => 'Usuario no encontrado'
                ], 404);
            }

            $validator = Validator::make($request->all(), [
                'nombre_usuario' => 'sometimes|string|max:255|unique:voto_usuario,nombre_usuario,' . $id . ',id_usuario',
                'contrasena' => 'sometimes|string|min:6',
                'fecha_fin' => 'nullable|date',
                'activo' => 'sometimes|boolean',
                'roles' => 'sometimes|array',
                'roles.*' => 'exists:roles,name'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Errores de validación',
                    'errors' => $validator->errors()
                ], 422);
            }

            $data = $request->only(['nombre_usuario', 'fecha_fin', 'activo']);
            
            if ($request->has('contrasena')) {
                $data['contrasena'] = Hash::make($request->contrasena);
            }
            
            $usuario->update($data);
            
            // Actualizar roles si se enviaron
            if ($request->has('roles')) {
                $usuario->syncRoles($request->roles);
            }

            return response()->json([
                'success' => true,
                'message' => 'Usuario actualizado correctamente',
                'data' => $usuario->fresh(['persona', 'roles'])
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar usuario: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {
            $usuario = VotoUsuario::find($id);
            
            if (!$usuario) {
                return response()->json([
                    'success' => false,
                    'message' => 'Usuario no encontrado'
                ], 404);
            }
            
            if (!request()->user()->can('eliminar-usuarios')) {
                return response()->json([
                    'success' => false,
                    'message' => 'No tiene permiso para eliminar usuarios'
                ], 403);
            }

            $usuario->delete();

            return response()->json([
                'success' => true,
                'message' => 'Usuario eliminado correctamente'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar usuario: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Asignar roles a usuario
     */
    public function assignRole(Request $request, $id)
    {
        if (!$request->user()->can('asignar-roles')) {
            return response()->json([
                'success' => false,
                'message' => 'No tiene permiso para asignar roles'
            ], 403);
        }
        
        try {
            $usuario = VotoUsuario::find($id);
            
            if (!$usuario) {
                return response()->json([
                    'success' => false,
                    'message' => 'Usuario no encontrado'
                ], 404);
            }

            $validator = Validator::make($request->all(), [
                'roles' => 'required|array',
                'roles.*' => 'exists:roles,name'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Errores de validación',
                    'errors' => $validator->errors()
                ], 422);
            }
            
            $usuario->syncRoles($request->roles);

            return response()->json([
                'success' => true,
                'message' => 'Roles asignados correctamente',
                'data' => $usuario->load(['persona', 'roles'])
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al asignar roles: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Obtener todos los roles disponibles
     */
    public function getRoles()
    {
        try {
            $roles = Role::all();
            
            return response()->json([
                'success' => true,
                'message' => 'Roles obtenidos',
                'data' => $roles
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener roles: ' . $e->getMessage()
            ], 500);
        }
    }
}