<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\VotoUsuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    /**
     * Login de usuario
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nombre_usuario' => 'required|string',
            'contrasena' => 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Errores de validación',
                'errors' => $validator->errors()
            ], 422);
        }
        
        $usuario = VotoUsuario::where('nombre_usuario', $request->nombre_usuario)->first();
        
        if (!$usuario || !Hash::check($request->contrasena, $usuario->contrasena)) {
            return response()->json([
                'success' => false,
                'message' => 'Credenciales incorrectas'
            ], 401);
        }
        
        if (!$usuario->isActive()) {
            return response()->json([
                'success' => false,
                'message' => 'Usuario inactivo o expirado'
            ], 403);
        }
        
        $token = $usuario->createToken('auth_token')->plainTextToken;
        
        // Actualizar token
        $usuario->update(['token' => $token]);
        
        return response()->json([
            'success' => true,
            'message' => 'Login exitoso',
            'data' => [
                'usuario' => $usuario->load(['persona', 'roles', 'permissions']),
                'token' => $token,
                'token_type' => 'Bearer'
            ]
        ]);
    }
    
    /**
     * Logout
     */
    public function logout(Request $request)
    {
        try {
            $request->user()->currentAccessToken()->delete();
            
            return response()->json([
                'success' => true,
                'message' => 'Sesión cerrada correctamente'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al cerrar sesión'
            ], 500);
        }
    }
    
    /**
     * Obtener usuario actual
     */
    public function me(Request $request)
    {
        return response()->json([
            'success' => true,
            'data' => $request->user()->load(['persona', 'roles', 'permissions'])
        ]);
    }
    
    /**
     * Verificar permisos del usuario
     */
    public function checkPermission(Request $request, $permission)
    {
        $hasPermission = $request->user()->can($permission);
        
        return response()->json([
            'success' => true,
            'data' => [
                'permission' => $permission,
                'has_permission' => $hasPermission
            ]
        ]);
    }
    
    /**
     * Obtener todos los permisos del usuario
     */
    public function permissions(Request $request)
    {
        $permissions = $request->user()->getAllPermissions()->pluck('name');
        
        return response()->json([
            'success' => true,
            'data' => $permissions
        ]);
    }
}