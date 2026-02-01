<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\PersonaController;
use App\Http\Controllers\API\UsuarioController;
use App\Http\Controllers\API\GeograficoController;
use App\Http\Controllers\API\MesaController;
use App\Http\Controllers\API\TipoEleccionController;

// ==================== RUTAS PÚBLICAS ====================
Route::post('/login', [AuthController::class, 'login'])->name('login');

// ==================== RUTAS PROTEGIDAS ====================
Route::middleware('auth:sanctum')->group(function () {
    
    // Auth
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/me', [AuthController::class, 'me'])->name('me');
    Route::get('/permissions', [AuthController::class, 'permissions'])->name('permissions');
    Route::get('/check-permission/{permission}', [AuthController::class, 'checkPermission'])->name('check-permission');
    
    // Personas
    Route::prefix('personas')->group(function () {
        Route::get('/', [PersonaController::class, 'index'])->name('personas.index');
        Route::post('/', [PersonaController::class, 'store'])->name('personas.store');
        Route::get('/{id}', [PersonaController::class, 'show'])->name('personas.show');
        Route::put('/{id}', [PersonaController::class, 'update'])->name('personas.update');
        Route::delete('/{id}', [PersonaController::class, 'destroy'])->name('personas.destroy');
        Route::get('/search/buscar', [PersonaController::class, 'search'])->name('personas.search');
    });
    
    // Usuarios
    Route::prefix('usuarios')->group(function () {
        Route::get('/', [UsuarioController::class, 'index'])->name('usuarios.index');
        Route::post('/', [UsuarioController::class, 'store'])->name('usuarios.store');
        Route::get('/{id}', [UsuarioController::class, 'show'])->name('usuarios.show');
        Route::put('/{id}', [UsuarioController::class, 'update'])->name('usuarios.update');
        Route::delete('/{id}', [UsuarioController::class, 'destroy'])->name('usuarios.destroy');
        Route::post('/{id}/assign-role', [UsuarioController::class, 'assignRole'])->name('usuarios.assign-role');
        Route::get('/roles/listar', [UsuarioController::class, 'getRoles'])->name('usuarios.get-roles');
    });
    
    // Geográfico
    Route::prefix('geograficos')->group(function () {
        // CRUD básico
        Route::get('/', [GeograficoController::class, 'index'])->name('geograficos.index');
        Route::post('/', [GeograficoController::class, 'store'])->name('geograficos.store');
        Route::get('/{id}', [GeograficoController::class, 'show'])->name('geograficos.show');
        Route::put('/{id}', [GeograficoController::class, 'update'])->name('geograficos.update');
        Route::delete('/{id}', [GeograficoController::class, 'destroy'])->name('geograficos.destroy');
        
        // Métodos especiales
        Route::get('/tipos/listar', [GeograficoController::class, 'tipos'])->name('geograficos.tipos');
        Route::get('/tipo/{tipo}', [GeograficoController::class, 'porTipo'])->name('geograficos.por-tipo');
        Route::get('/{id}/jerarquia-completa', [GeograficoController::class, 'jerarquiaCompleta'])->name('geograficos.jerarquia-completa');
        Route::get('/localidad/{id}/recintos', [GeograficoController::class, 'recintosPorLocalidad'])->name('geograficos.recintos-por-localidad');
        Route::get('/buscar/{termino}', [GeograficoController::class, 'buscar'])->name('geograficos.buscar');
    });
    
    // Mesas
    Route::prefix('mesas')->group(function () {
        Route::get('/', [MesaController::class, 'index'])->name('mesas.index');
        Route::post('/', [MesaController::class, 'store'])->name('mesas.store');
        Route::get('/{id}', [MesaController::class, 'show'])->name('mesas.show');
        Route::put('/{id}', [MesaController::class, 'update'])->name('mesas.update');
        Route::delete('/{id}', [MesaController::class, 'destroy'])->name('mesas.destroy');
        Route::get('/recinto/{id}', [MesaController::class, 'mesasPorRecinto'])->name('mesas.por-recinto');
    });
    
    // Tipo Elección
    Route::prefix('tipo-elecciones')->group(function () {
        Route::get('/', [TipoEleccionController::class, 'index'])->name('tipo-elecciones.index');
        Route::post('/', [TipoEleccionController::class, 'store'])->name('tipo-elecciones.store');
        Route::get('/{id}', [TipoEleccionController::class, 'show'])->name('tipo-elecciones.show');
        Route::put('/{id}', [TipoEleccionController::class, 'update'])->name('tipo-elecciones.update');
        Route::delete('/{id}', [TipoEleccionController::class, 'destroy'])->name('tipo-elecciones.destroy');
        Route::get('/activos/listar', [TipoEleccionController::class, 'activos'])->name('tipo-elecciones.activos');
    });
});