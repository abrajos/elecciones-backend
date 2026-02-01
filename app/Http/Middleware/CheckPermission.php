<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckPermission
{
    public function handle(Request $request, Closure $next, $permission)
    {
        if (!$request->user() || !$request->user()->can($permission)) {
            return response()->json([
                'success' => false,
                'message' => 'No tiene permisos para realizar esta acción'
            ], 403);
        }
        
        return $next($request);
    }
}