<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ControlSalidaController;
use App\Http\Controllers\ProyectoController;
use App\Http\Controllers\ReporteController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Secure API endpoints with JWT Authentication, CORS and Rate Limiting.
|
*/

Route::prefix('v1')->group(function () {
    // Public authentication routes
    Route::prefix('auth')->group(function () {
        Route::post('register', [AuthController::class, 'register'])->middleware('throttle:10,1');
        Route::post('login', [AuthController::class, 'login'])->middleware('throttle:10,1');
    });

    // Protected routes requiring valid JWT token
    Route::middleware(['auth:api'])->group(function () {
        Route::prefix('auth')->group(function () {
            Route::get('profile', [AuthController::class, 'profile']);
            Route::post('refresh', [AuthController::class, 'refresh']);
            Route::post('logout', [AuthController::class, 'logout']);
        });

        // Control de Salidas Endpoints
        Route::prefix('control-salidas')->group(function () {
            Route::get('/', [ControlSalidaController::class, 'index']);
            Route::post('/', [ControlSalidaController::class, 'store']);
            Route::patch('/{id}/toggle-revisado', [ControlSalidaController::class, 'toggleRevisado']);
            Route::delete('/{id}', [ControlSalidaController::class, 'destroy']);
        });

        // Reportes Diarios / Actividades Endpoints
        Route::prefix('reportes')->group(function () {
            Route::get('/', [ReporteController::class, 'index']);
            Route::post('/', [ReporteController::class, 'store']);
            Route::patch('/{id}/toggle-revisado', [ReporteController::class, 'toggleRevisado']);
            Route::delete('/{id}', [ReporteController::class, 'destroy']);
        });

        // Proyectos de IT / Sistemas Endpoints
        Route::prefix('proyectos')->group(function () {
            Route::get('/', [ProyectoController::class, 'index']);
            Route::post('/', [ProyectoController::class, 'store']);
            Route::get('/{id}', [ProyectoController::class, 'show']);
            Route::put('/{id}', [ProyectoController::class, 'update']);
            Route::patch('/{id}', [ProyectoController::class, 'update']);
            Route::delete('/{id}', [ProyectoController::class, 'destroy']);
        });
    });
});
