<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\SimulationController;
use App\Http\Controllers\GuestSessionController;
use App\Http\Controllers\IngredientController;
use App\Http\Controllers\IngredientCategoryController;

// Authentication routes
Route::prefix('auth')->group(function () {
    // Public routes
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
    Route::post('/reset-password', [AuthController::class, 'resetPassword']);
    
    // Google OAuth routes
    Route::get('/google', [AuthController::class, 'redirectToGoogle']);
    Route::get('/google/callback', [AuthController::class, 'handleGoogleCallback']);
    
    // Protected routes
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::post('/logout-all', [AuthController::class, 'logoutAll']);
        Route::post('/refresh', [AuthController::class, 'refresh']);
        Route::get('/me', [AuthController::class, 'me']);
    });
});

// Guest session routes (public access)
Route::prefix('guest')->group(function () {
    Route::post('/save-form-data', [GuestSessionController::class, 'save']);
    Route::get('/session/{session_id}', [GuestSessionController::class, 'show']);
    Route::delete('/session/{session_id}', [GuestSessionController::class, 'destroy']);
    
    // Admin stats (requires authentication)
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/stats', [GuestSessionController::class, 'stats']);
    });
});

// Simulation routes
Route::prefix('simulations')->group(function () {
    // Protected routes - generate from guest session (requires authentication)
    Route::middleware(['auth:sanctum', 'simulation.rate.limit'])->group(function () {
        Route::post('/generate-from-guest', [GuestSessionController::class, 'generateFromGuest']);
    });
});

// Ingredient management routes
Route::prefix('ingredients')->group(function () {
    // Public routes (read-only)
    Route::get('/', [IngredientController::class, 'index']);
    Route::get('/{id}', [IngredientController::class, 'show']);
    Route::get('/inci/{inci_name}', [IngredientController::class, 'showByInci']);
    Route::get('/{id}/related', [IngredientController::class, 'related']);
    
    // Protected routes (requires authentication)
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/', [IngredientController::class, 'store']);
        Route::put('/{id}', [IngredientController::class, 'update']);
        Route::delete('/{id}', [IngredientController::class, 'destroy']);
    });
});

// Ingredient category routes
Route::prefix('ingredient-categories')->group(function () {
    // Public routes (read-only)
    Route::get('/', [IngredientCategoryController::class, 'index']);
    Route::get('/{id}', [IngredientCategoryController::class, 'show']);
    
    // Protected routes (requires authentication)
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/', [IngredientCategoryController::class, 'store']);
        Route::put('/{id}', [IngredientCategoryController::class, 'update']);
        Route::delete('/{id}', [IngredientCategoryController::class, 'destroy']);
    });
});

// Legacy user route (keeping for compatibility)
Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
