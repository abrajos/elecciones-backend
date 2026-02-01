<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\VotoPersona;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PersonaController extends Controller
{
    
    
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if (!$request->user()->can('ver-personas')) {
            return response()->json([
                'success' => false,
                'message' => 'No tiene permiso para ver personas'
            ], 403);
        }
        
        try {
            $query = VotoPersona::query();
            
            // Filtros
            if ($request->has('ci')) {
                $query->where('ci', 'like', '%' . $request->ci . '%');
            }
            
            if ($request->has('nombre')) {
                $query->where('nombre', 'like', '%' . $request->nombre . '%');
            }
            
            if ($request->has('apellido')) {
                $query->where(function($q) use ($request) {
                    $q->where('apellido_paterno', 'like', '%' . $request->apellido . '%')
                      ->orWhere('apellido_materno', 'like', '%' . $request->apellido . '%');
                });
            }
            
            $personas = $query->orderBy('apellido_paterno')->paginate($request->get('per_page', 15));
            
            return response()->json([
                'success' => true,
                'message' => 'Personas obtenidas correctamente',
                'data' => $personas
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener personas: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        if (!$request->user()->can('crear-personas')) {
            return response()->json([
                'success' => false,
                'message' => 'No tiene permiso para crear personas'
            ], 403);
        }
        
        try {
            $validator = Validator::make($request->all(), [
                'nombre' => 'required|string|max:255',
                'apellido_paterno' => 'required|string|max:255',
                'apellido_materno' => 'required|string|max:255',
                'ci' => 'required|string|max:50|unique:voto_persona,ci',
                'celular' => 'nullable|string|max:20',
                'email' => 'nullable|email|max:255|unique:voto_persona,email'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Errores de validación',
                    'errors' => $validator->errors()
                ], 422);
            }

            $persona = VotoPersona::create($request->all());

            return response()->json([
                'success' => true,
                'message' => 'Persona creada correctamente',
                'data' => $persona
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al crear persona: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        try {
            $persona = VotoPersona::find($id);
            
            if (!$persona) {
                return response()->json([
                    'success' => false,
                    'message' => 'Persona no encontrada'
                ], 404);
            }
            
            if (!request()->user()->can('ver-personas')) {
                return response()->json([
                    'success' => false,
                    'message' => 'No tiene permiso para ver personas'
                ], 403);
            }
            
            return response()->json([
                'success' => true,
                'message' => 'Persona obtenida correctamente',
                'data' => $persona
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener persona: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        if (!$request->user()->can('editar-personas')) {
            return response()->json([
                'success' => false,
                'message' => 'No tiene permiso para editar personas'
            ], 403);
        }
        
        try {
            $persona = VotoPersona::find($id);
            
            if (!$persona) {
                return response()->json([
                    'success' => false,
                    'message' => 'Persona no encontrada'
                ], 404);
            }

            $validator = Validator::make($request->all(), [
                'nombre' => 'sometimes|string|max:255',
                'apellido_paterno' => 'sometimes|string|max:255',
                'apellido_materno' => 'sometimes|string|max:255',
                'ci' => 'sometimes|string|max:50|unique:voto_persona,ci,' . $id . ',id_persona',
                'celular' => 'nullable|string|max:20',
                'email' => 'nullable|email|max:255|unique:voto_persona,email,' . $id . ',id_persona'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Errores de validación',
                    'errors' => $validator->errors()
                ], 422);
            }

            $persona->update($request->all());

            return response()->json([
                'success' => true,
                'message' => 'Persona actualizada correctamente',
                'data' => $persona
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar persona: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {
            $persona = VotoPersona::find($id);
            
            if (!$persona) {
                return response()->json([
                    'success' => false,
                    'message' => 'Persona no encontrada'
                ], 404);
            }
            
            if (!request()->user()->can('eliminar-personas')) {
                return response()->json([
                    'success' => false,
                    'message' => 'No tiene permiso para eliminar personas'
                ], 403);
            }

            // Verificar si la persona tiene usuarios asociados
            if ($persona->usuario()->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se puede eliminar la persona porque tiene usuarios asociados'
                ], 400);
            }

            $persona->delete();

            return response()->json([
                'success' => true,
                'message' => 'Persona eliminada correctamente'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar persona: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Buscar personas
     */
    public function search(Request $request)
    {
        if (!$request->user()->can('ver-personas')) {
            return response()->json([
                'success' => false,
                'message' => 'No tiene permiso para ver personas'
            ], 403);
        }
        
        try {
            $query = VotoPersona::query();
            
            if ($request->has('ci')) {
                $query->where('ci', 'like', '%' . $request->ci . '%');
            }
            
            if ($request->has('nombre')) {
                $query->where('nombre', 'like', '%' . $request->nombre . '%');
            }
            
            if ($request->has('apellido')) {
                $query->where(function($q) use ($request) {
                    $q->where('apellido_paterno', 'like', '%' . $request->apellido . '%')
                      ->orWhere('apellido_materno', 'like', '%' . $request->apellido . '%');
                });
            }
            
            $personas = $query->orderBy('apellido_paterno')->limit(50)->get();
            
            return response()->json([
                'success' => true,
                'message' => 'Búsqueda completada',
                'data' => $personas
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error en la búsqueda: ' . $e->getMessage()
            ], 500);
        }
    }
}