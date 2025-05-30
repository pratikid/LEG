<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware('api')->get('/ping', function (Request $request) {
    return response()->json(['message' => 'pong']);
}); 