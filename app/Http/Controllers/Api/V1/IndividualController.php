<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Individual;
use Illuminate\Http\JsonResponse;

final class IndividualController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(Individual::all());
    }

    public function show(Individual $individual): JsonResponse
    {
        return response()->json($individual);
    }

    // Add store, update, destroy as needed
}
