<?php

declare(strict_types=1);

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware('api')->get('/ping', function (Request $request) {
    return response()->json(['message' => 'pong']);
});

Route::prefix('v1')->group(function () {
    Route::apiResource('individuals', App\Http\Controllers\Api\V1\IndividualController::class);
    
    // Import performance metrics routes
    Route::prefix('import-metrics')->group(function () {
        Route::get('comparison', [App\Http\Controllers\Api\V1\ImportMetricsController::class, 'comparison']);
        Route::get('recent', [App\Http\Controllers\Api\V1\ImportMetricsController::class, 'recent']);
        Route::get('method', [App\Http\Controllers\Api\V1\ImportMetricsController::class, 'method']);
        Route::get('aggregated', [App\Http\Controllers\Api\V1\ImportMetricsController::class, 'aggregated']);
        Route::get('summary', [App\Http\Controllers\Api\V1\ImportMetricsController::class, 'summary']);
    });
});
