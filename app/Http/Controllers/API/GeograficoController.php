<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\VotoGeografico;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class GeograficoController extends Controller
{
    
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if (!$request->user()->can('ver-geografico')) {
            return response()->json([
                'success' => false,
                'message' => 'No tiene permiso para ver ubicaciones geográficas'
            ], 403);
        }
        
        try {
            $query = VotoGeografico::query();
            
            // Filtrar por tipo
            if ($request->has('tipo')) {
                $query->where('tipo', $request->tipo);
            }
            
            // Filtrar por padre
            if ($request->has('fk_id_geografico')) {
                $query->where('fk_id_geografico', $request->fk_id_geografico);
            }
            
            // Buscar por término
            if ($request->has('buscar')) {
                $query->where(function($q) use ($request) {
                    $q->where('nombre', 'LIKE', '%' . $request->buscar . '%')
                      ->orWhere('codigo', 'LIKE', '%' . $request->buscar . '%');
                });
            }
            
            $query->with(['padre', 'hijos']);
            $query->orderBy('tipo')->orderBy('nombre');
            
            $geograficos = $query->paginate($request->get('per_page', 15));
            
            return response()->json([
                'success' => true,
                'message' => 'Ubicaciones geográficas obtenidas',
                'data' => $geograficos
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener ubicaciones geográficas: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        if (!$request->user()->can('crear-geografico')) {
            return response()->json([
                'success' => false,
                'message' => 'No tiene permiso para crear ubicaciones geográficas'
            ], 403);
        }
        
        try {
            $validator = Validator::make($request->all(), [
                'nombre' => 'required|string|max:255',
                'codigo' => 'required|string|max:50|unique:voto_geografico,codigo',
                'ubicacion' => 'nullable|string|max:255',
                'tipo' => 'required|string|in:PAIS,CIUDAD,MUNICIPIO,LOCALIDAD,RECINTO',
                'fk_id_geografico' => 'nullable|integer|exists:voto_geografico,id_geografico'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Errores de validación',
                    'errors' => $validator->errors()
                ], 422);
            }
            
            // Validar jerarquía
            if ($request->fk_id_geografico) {
                $padre = VotoGeografico::find($request->fk_id_geografico);
                
                // Validar tipos padre-hijo
                $jerarquia = [
                    'PAIS' => null,
                    'CIUDAD' => 'PAIS',
                    'MUNICIPIO' => 'CIUDAD',
                    'LOCALIDAD' => 'MUNICIPIO',
                    'RECINTO' => 'LOCALIDAD'
                ];
                
                if (!isset($jerarquia[$request->tipo]) || $jerarquia[$request->tipo] !== $padre->tipo) {
                    return response()->json([
                        'success' => false,
                        'message' => "Tipo inválido. Un '{$padre->tipo}' solo puede tener hijos de tipo: " . ($jerarquia[$padre->tipo] ?? 'Ninguno')
                    ], 422);
                }
            } else {
                // Si no tiene padre, debe ser PAIS
                if ($request->tipo !== 'PAIS') {
                    return response()->json([
                        'success' => false,
                        'message' => "Solo los PAIS pueden no tener padre"
                    ], 422);
                }
            }

            $geografico = VotoGeografico::create($request->all());

            return response()->json([
                'success' => true,
                'message' => 'Ubicación geográfica creada correctamente',
                'data' => $geografico->load(['padre', 'hijos'])
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al crear ubicación geográfica: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        try {
            $geografico = VotoGeografico::with(['padre', 'hijos'])->find($id);
            
            if (!$geografico) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ubicación geográfica no encontrada'
                ], 404);
            }
            
            if (!request()->user()->can('ver-geografico')) {
                return response()->json([
                    'success' => false,
                    'message' => 'No tiene permiso para ver ubicaciones geográficas'
                ], 403);
            }
            
            return response()->json([
                'success' => true,
                'message' => 'Ubicación geográfica obtenida',
                'data' => $geografico
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener ubicación geográfica: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        if (!$request->user()->can('editar-geografico')) {
            return response()->json([
                'success' => false,
                'message' => 'No tiene permiso para editar ubicaciones geográficas'
            ], 403);
        }
        
        try {
            $geografico = VotoGeografico::find($id);
            
            if (!$geografico) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ubicación geográfica no encontrada'
                ], 404);
            }

            $validator = Validator::make($request->all(), [
                'nombre' => 'sometimes|string|max:255',
                'codigo' => 'sometimes|string|max:50|unique:voto_geografico,codigo,' . $id . ',id_geografico',
                'ubicacion' => 'nullable|string|max:255',
                'tipo' => 'sometimes|string|in:PAIS,CIUDAD,MUNICIPIO,LOCALIDAD,RECINTO',
                'fk_id_geografico' => 'nullable|integer|exists:voto_geografico,id_geografico'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Errores de validación',
                    'errors' => $validator->errors()
                ], 422);
            }
            
            // Validar que no sea padre de sí mismo
            if ($request->has('fk_id_geografico') && $request->fk_id_geografico == $id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Una ubicación no puede ser padre de sí misma'
                ], 422);
            }
            
            // Validar jerarquía si se cambia tipo o padre
            if ($request->has('tipo') || $request->has('fk_id_geografico')) {
                $nuevoTipo = $request->has('tipo') ? $request->tipo : $geografico->tipo;
                $nuevoPadreId = $request->has('fk_id_geografico') ? $request->fk_id_geografico : $geografico->fk_id_geografico;
                
                if ($nuevoPadreId) {
                    $padre = VotoGeografico::find($nuevoPadreId);
                    
                    $jerarquia = [
                        'PAIS' => null,
                        'CIUDAD' => 'PAIS',
                        'MUNICIPIO' => 'CIUDAD',
                        'LOCALIDAD' => 'MUNICIPIO',
                        'RECINTO' => 'LOCALIDAD'
                    ];
                    
                    if (!isset($jerarquia[$nuevoTipo]) || $jerarquia[$nuevoTipo] !== $padre->tipo) {
                        return response()->json([
                            'success' => false,
                            'message' => "Tipo inválido. Un '{$padre->tipo}' solo puede tener hijos de tipo: " . ($jerarquia[$padre->tipo] ?? 'Ninguno')
                        ], 422);
                    }
                } else {
                    // Si no tiene padre, debe ser PAIS
                    if ($nuevoTipo !== 'PAIS') {
                        return response()->json([
                            'success' => false,
                            'message' => "Solo los PAIS pueden no tener padre"
                        ], 422);
                    }
                }
            }

            $geografico->update($request->all());

            return response()->json([
                'success' => true,
                'message' => 'Ubicación geográfica actualizada correctamente',
                'data' => $geografico->fresh(['padre', 'hijos'])
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar ubicación geográfica: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {
            $geografico = VotoGeografico::find($id);
            
            if (!$geografico) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ubicación geográfica no encontrada'
                ], 404);
            }
            
            if (!request()->user()->can('eliminar-geografico')) {
                return response()->json([
                    'success' => false,
                    'message' => 'No tiene permiso para eliminar ubicaciones geográficas'
                ], 403);
            }

            // Verificar si tiene hijos
            if ($geografico->hijos()->count() > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se puede eliminar porque tiene ubicaciones hijas'
                ], 400);
            }

            $geografico->delete();

            return response()->json([
                'success' => true,
                'message' => 'Ubicación geográfica eliminada correctamente'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar ubicación geográfica: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Obtener tipos disponibles
     */
    public function tipos()
    {
        try {
            $tipos = VotoGeografico::select('tipo')
                ->distinct()
                ->orderBy('tipo')
                ->pluck('tipo');
            
            return response()->json([
                'success' => true,
                'message' => 'Tipos geográficos obtenidos',
                'data' => $tipos
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener tipos geográficos: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Obtener ubicaciones por tipo
     */
    public function porTipo($tipo)
    {
        try {
            // Validar tipo
            $tiposValidos = ['PAIS', 'CIUDAD', 'MUNICIPIO', 'LOCALIDAD', 'RECINTO'];
            if (!in_array($tipo, $tiposValidos)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tipo inválido'
                ], 422);
            }
            
            $geograficos = VotoGeografico::where('tipo', $tipo)
                ->with('padre')
                ->orderBy('nombre')
                ->get();
            
            return response()->json([
                'success' => true,
                'message' => "Ubicaciones de tipo {$tipo} obtenidas",
                'data' => $geograficos
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener ubicaciones por tipo: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Obtener jerarquía completa
     */
    public function jerarquiaCompleta($id)
    {
        try {
            $geografico = VotoGeografico::findOrFail($id);
            
            // Función recursiva para obtener árbol
            function getArbol($geografico) {
                $hijos = $geografico->hijos;
                $arbolHijos = [];
                
                foreach ($hijos as $hijo) {
                    $arbolHijos[] = getArbol($hijo);
                }
                
                return [
                    'id_geografico' => $geografico->id_geografico,
                    'nombre' => $geografico->nombre,
                    'codigo' => $geografico->codigo,
                    'tipo' => $geografico->tipo,
                    'hijos' => $arbolHijos
                ];
            }
            
            $arbol = getArbol($geografico);
            
            return response()->json([
                'success' => true,
                'message' => 'Jerarquía obtenida',
                'data' => $arbol
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener jerarquía: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Obtener recintos por localidad
     */
    public function recintosPorLocalidad($localidadId)
    {
        try {
            $localidad = VotoGeografico::where('id_geografico', $localidadId)
                ->where('tipo', 'LOCALIDAD')
                ->firstOrFail();
            
            $recintos = VotoGeografico::where('fk_id_geografico', $localidadId)
                ->where('tipo', 'RECINTO')
                ->orderBy('nombre')
                ->get();
            
            return response()->json([
                'success' => true,
                'message' => 'Recintos obtenidos',
                'data' => [
                    'localidad' => $localidad,
                    'recintos' => $recintos,
                    'total' => $recintos->count()
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener recintos: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Buscar ubicaciones
     */
    public function buscar($termino)
    {
        try {
            $geograficos = VotoGeografico::where('nombre', 'LIKE', "%{$termino}%")
                ->orWhere('codigo', 'LIKE', "%{$termino}%")
                ->with('padre')
                ->orderBy('tipo')
                ->orderBy('nombre')
                ->limit(50)
                ->get();
            
            return response()->json([
                'success' => true,
                'message' => 'Resultados de búsqueda',
                'data' => $geograficos
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al buscar: ' . $e->getMessage()
            ], 500);
        }
    }
}