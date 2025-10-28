<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
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
Route::prefix('simulations')->name('api.simulations.')->group(function () {
    // Public routes (optional auth - guest and authenticated users)
    Route::post('/', [SimulationController::class, 'store'])->name('store');
    Route::get('/{id}', [SimulationController::class, 'show'])->name('show');
    Route::get('/{id}/status', [SimulationController::class, 'status'])->name('status');
    
    // Protected routes - generate from guest session (requires authentication)
    Route::middleware(['auth:sanctum', 'simulation.rate.limit'])->group(function () {
        Route::get('/', [SimulationController::class, 'index'])->name('index');
        Route::post('/generate-from-guest', [GuestSessionController::class, 'generateFromGuest'])->name('generate-from-guest');
        Route::post('/{id}/regenerate', [SimulationController::class, 'regenerate'])->name('regenerate');
        Route::post('/{id}/export', [SimulationController::class, 'export'])->name('export');
    });
});

// n8n webhook routes (internal use)
Route::prefix('n8n')->name('api.n8n.')->group(function () {
    Route::post('/webhook', function (Request $request) {
        $n8nService = app(\App\Services\N8nService::class);
        $success = $n8nService->handleWebhook($request->all());
        
        return response()->json([
            'success' => $success,
            'message' => $success ? 'Webhook processed' : 'Webhook processing failed',
        ], $success ? 200 : 400);
    })->name('webhook');
});

// Export download routes
Route::prefix('exports')->name('api.exports.')->group(function () {
    Route::get('/download/{filename}', function ($filename) {
        $path = "exports/{$filename}";
        
        if (!Storage::disk('local')->exists($path)) {
            return response()->json([
                'success' => false,
                'message' => 'Export file not found or expired',
                'error' => 'FILE_NOT_FOUND',
            ], 404);
        }
        
        return Storage::disk('local')->download($path);
    })->middleware('auth:sanctum')->name('download');
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
