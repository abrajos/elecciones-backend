<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\VotoTipoEleccion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class TipoEleccionController extends Controller
{
   
    
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if (!$request->user()->can('ver-tipos-eleccion')) {
            return response()->json([
                'success' => false,
                'message' => 'No tiene permiso para ver tipos de elección'
            ], 403);
        }
        
        try {
            $query = VotoTipoEleccion::query();
            
            // Filtrar por estado activo
            if ($request->has('activo')) {
                $query->where('activo', $request->activo);
            }
            
            $query->orderBy('nombre');
            
            $tipos = $query->paginate($request->get('per_page', 15));
            
            return response()->json([
                'success' => true,
                'message' => 'Tipos de elección obtenidos',
                'data' => $tipos
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener tipos de elección: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        if (!$request->user()->can('crear-tipos-eleccion')) {
            return response()->json([
                'success' => false,
                'message' => 'No tiene permiso para crear tipos de elección'
            ], 403);
        }
        
        try {
            $validator = Validator::make($request->all(), [
                'nombre' => 'required|string|max:255',
                'codigo' => 'required|string|max:50|unique:voto_tipo_eleccion,codigo',
                'descripcion' => 'nullable|string',
                'max_candidatos' => 'required|integer|min:1',
                'activo' => 'sometimes|boolean'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Errores de validación',
                    'errors' => $validator->errors()
                ], 422);
            }

            $tipo = VotoTipoEleccion::create($request->all());

            return response()->json([
                'success' => true,
                'message' => 'Tipo de elección creado correctamente',
                'data' => $tipo
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al crear tipo de elección: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        try {
            $tipo = VotoTipoEleccion::find($id);
            
            if (!$tipo) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tipo de elección no encontrado'
                ], 404);
            }
            
            if (!request()->user()->can('ver-tipos-eleccion')) {
                return response()->json([
                    'success' => false,
                    'message' => 'No tiene permiso para ver tipos de elección'
                ], 403);
            }
            
            return response()->json([
                'success' => true,
                'message' => 'Tipo de elección obtenido',
                'data' => $tipo
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener tipo de elección: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        if (!$request->user()->can('editar-tipos-eleccion')) {
            return response()->json([
                'success' => false,
                'message' => 'No tiene permiso para editar tipos de elección'
            ], 403);
        }
        
        try {
            $tipo = VotoTipoEleccion::find($id);
            
            if (!$tipo) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tipo de elección no encontrado'
                ], 404);
            }

            $validator = Validator::make($request->all(), [
                'nombre' => 'sometimes|string|max:255',
                'codigo' => 'sometimes|string|max:50|unique:voto_tipo_eleccion,codigo,' . $id . ',id_tipo_eleccion',
                'descripcion' => 'nullable|string',
                'max_candidatos' => 'sometimes|integer|min:1',
                'activo' => 'sometimes|boolean'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Errores de validación',
                    'errors' => $validator->errors()
                ], 422);
            }

            $tipo->update($request->all());

            return response()->json([
                'success' => true,
                'message' => 'Tipo de elección actualizado correctamente',
                'data' => $tipo
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar tipo de elección: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {
            $tipo = VotoTipoEleccion::find($id);
            
            if (!$tipo) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tipo de elección no encontrado'
                ], 404);
            }
            
            if (!request()->user()->can('eliminar-tipos-eleccion')) {
                return response()->json([
                    'success' => false,
                    'message' => 'No tiene permiso para eliminar tipos de elección'
                ], 403);
            }

            $tipo->delete();

            return response()->json([
                'success' => true,
                'message' => 'Tipo de elección eliminado correctamente'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar tipo de elección: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Obtener tipos activos
     */
    public function activos()
    {
        try {
            $tipos = VotoTipoEleccion::where('activo', true)
                ->orderBy('nombre')
                ->get();
            
            return response()->json([
                'success' => true,
                'message' => 'Tipos de elección activos obtenidos',
                'data' => $tipos
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener tipos activos: ' . $e->getMessage()
            ], 500);
        }
    }
}