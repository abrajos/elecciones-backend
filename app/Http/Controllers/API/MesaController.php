<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\VotoMesa;
use App\Models\VotoGeografico;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class MesaController extends Controller
{
    
    
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if (!$request->user()->can('ver-mesas')) {
            return response()->json([
                'success' => false,
                'message' => 'No tiene permiso para ver mesas'
            ], 403);
        }
        
        try {
            $query = VotoMesa::query();
            
            // Filtrar por recinto
            if ($request->has('id_recinto')) {
                $query->where('id_recinto', $request->id_recinto);
            }
            
            // Filtrar por estado
            if ($request->has('activa')) {
                $query->where('activa', $request->activa);
            }
            
            $query->with('recinto');
            $query->orderBy('codigo');
            
            $mesas = $query->paginate($request->get('per_page', 15));
            
            return response()->json([
                'success' => true,
                'message' => 'Mesas obtenidas correctamente',
                'data' => $mesas
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener mesas: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        if (!$request->user()->can('crear-mesas')) {
            return response()->json([
                'success' => false,
                'message' => 'No tiene permiso para crear mesas'
            ], 403);
        }
        
        try {
            $validator = Validator::make($request->all(), [
                'codigo' => 'required|string|max:50|unique:voto_mesa,codigo',
                'nombre' => 'nullable|string|max:255',
                'descripcion' => 'nullable|string',
                'numero_personas' => 'nullable|integer|min:0',
                'id_recinto' => 'required|integer|exists:voto_geografico,id_geografico',
                'activa' => 'sometimes|boolean'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Errores de validación',
                    'errors' => $validator->errors()
                ], 422);
            }
            
            // Verificar que el recinto sea de tipo RECINTO
            $recinto = VotoGeografico::find($request->id_recinto);
            if (!$recinto || $recinto->tipo !== 'RECINTO') {
                return response()->json([
                    'success' => false,
                    'message' => 'El ID proporcionado no corresponde a un recinto válido'
                ], 422);
            }

            $mesa = VotoMesa::create($request->all());

            return response()->json([
                'success' => true,
                'message' => 'Mesa creada correctamente',
                'data' => $mesa->load('recinto')
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al crear mesa: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        try {
            $mesa = VotoMesa::with('recinto')->find($id);
            
            if (!$mesa) {
                return response()->json([
                    'success' => false,
                    'message' => 'Mesa no encontrada'
                ], 404);
            }
            
            if (!request()->user()->can('ver-mesas')) {
                return response()->json([
                    'success' => false,
                    'message' => 'No tiene permiso para ver mesas'
                ], 403);
            }
            
            return response()->json([
                'success' => true,
                'message' => 'Mesa obtenida correctamente',
                'data' => $mesa
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener mesa: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        if (!$request->user()->can('editar-mesas')) {
            return response()->json([
                'success' => false,
                'message' => 'No tiene permiso para editar mesas'
            ], 403);
        }
        
        try {
            $mesa = VotoMesa::find($id);
            
            if (!$mesa) {
                return response()->json([
                    'success' => false,
                    'message' => 'Mesa no encontrada'
                ], 404);
            }

            $validator = Validator::make($request->all(), [
                'codigo' => 'sometimes|string|max:50|unique:voto_mesa,codigo,' . $id . ',id_mesa',
                'nombre' => 'nullable|string|max:255',
                'descripcion' => 'nullable|string',
                'numero_personas' => 'nullable|integer|min:0',
                'id_recinto' => 'sometimes|integer|exists:voto_geografico,id_geografico',
                'activa' => 'sometimes|boolean'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Errores de validación',
                    'errors' => $validator->errors()
                ], 422);
            }
            
            // Si se cambia el recinto, verificar que sea RECINTO
            if ($request->has('id_recinto')) {
                $recinto = VotoGeografico::find($request->id_recinto);
                if (!$recinto || $recinto->tipo !== 'RECINTO') {
                    return response()->json([
                        'success' => false,
                        'message' => 'El ID proporcionado no corresponde a un recinto válido'
                    ], 422);
                }
            }

            $mesa->update($request->all());

            return response()->json([
                'success' => true,
                'message' => 'Mesa actualizada correctamente',
                'data' => $mesa->fresh('recinto')
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar mesa: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {
            $mesa = VotoMesa::find($id);
            
            if (!$mesa) {
                return response()->json([
                    'success' => false,
                    'message' => 'Mesa no encontrada'
                ], 404);
            }
            
            if (!request()->user()->can('eliminar-mesas')) {
                return response()->json([
                    'success' => false,
                    'message' => 'No tiene permiso para eliminar mesas'
                ], 403);
            }

            $mesa->delete();

            return response()->json([
                'success' => true,
                'message' => 'Mesa eliminada correctamente'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar mesa: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Obtener mesas por recinto
     */
    public function mesasPorRecinto($recintoId)
    {
        try {
            $recinto = VotoGeografico::where('id_geografico', $recintoId)
                ->where('tipo', 'RECINTO')
                ->firstOrFail();
            
            $mesas = VotoMesa::where('id_recinto', $recintoId)
                ->orderBy('codigo')
                ->get();
            
            return response()->json([
                'success' => true,
                'message' => 'Mesas por recinto',
                'data' => [
                    'recinto' => $recinto,
                    'mesas' => $mesas,
                    'total' => $mesas->count()
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener mesas por recinto: ' . $e->getMessage()
            ], 500);
        }
    }
}