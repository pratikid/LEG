<?php

declare(strict_types=1);

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware('api')->get('/ping', function (Request $request) {
    return response()->json(['message' => 'pong']);
});

Route::prefix('v1')->middleware(['throttle:60,1'])->group(function () {
    // Individual management
    Route::apiResource('individuals', App\Http\Controllers\Api\V1\IndividualController::class);

    // Tree management
    Route::apiResource('trees', App\Http\Controllers\Api\V1\TreeController::class);

    // Family management
    Route::apiResource('families', App\Http\Controllers\Api\V1\FamilyController::class);

    // GEDCOM import/export
    Route::prefix('gedcom')->group(function () {
        Route::post('import', [App\Http\Controllers\Api\V1\GedcomController::class, 'import']);
        Route::get('export/{tree}', [App\Http\Controllers\Api\V1\GedcomController::class, 'export']);
        Route::get('validate', [App\Http\Controllers\Api\V1\GedcomController::class, 'validate']);
    });

    // Search functionality
    Route::prefix('search')->group(function () {
        Route::get('individuals', [App\Http\Controllers\Api\V1\SearchController::class, 'individuals']);
        Route::get('families', [App\Http\Controllers\Api\V1\SearchController::class, 'families']);
        Route::get('trees', [App\Http\Controllers\Api\V1\SearchController::class, 'trees']);
    });

    // Import performance metrics routes
    Route::prefix('import-metrics')->group(function () {
        Route::get('comparison', [App\Http\Controllers\Api\V1\ImportMetricsController::class, 'comparison']);
        Route::get('recent', [App\Http\Controllers\Api\V1\ImportMetricsController::class, 'recent']);
        Route::get('method', [App\Http\Controllers\Api\V1\ImportMetricsController::class, 'method']);
        Route::get('aggregated', [App\Http\Controllers\Api\V1\ImportMetricsController::class, 'aggregated']);
        Route::get('summary', [App\Http\Controllers\Api\V1\ImportMetricsController::class, 'summary']);
    });

    // Health check
    Route::get('health', function () {
        return response()->json([
            'status' => 'healthy',
            'timestamp' => now()->toISOString(),
            'version' => config('app.version', '1.0.0'),
        ]);
    });
});
