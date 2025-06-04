<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware('api')->get('/ping', function (Request $request) {
    return response()->json(['message' => 'pong']);
});

Route::prefix('v1')->group(function () {
    Route::apiResource('individuals', App\Http\Controllers\Api\V1\IndividualController::class);
});
