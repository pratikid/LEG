<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Individual;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

final class IndividualController extends Controller
{
    /**
     * Display a listing of individuals
     */
    public function index(Request $request): JsonResponse
    {
        $query = Individual::query();
        
        // Apply filters
        if ($request->has('tree_id')) {
            $query->where('tree_id', $request->integer('tree_id'));
        }
        
        if ($request->has('sex')) {
            $query->where('sex', $request->string('sex'));
        }
        
        if ($request->has('search')) {
            $search = $request->string('search');
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'ilike', "%{$search}%")
                  ->orWhere('last_name', 'ilike', "%{$search}%");
            });
        }
        
        $individuals = $query->paginate($request->integer('per_page', 15));
        
        return response()->json([
            'data' => $individuals->items(),
            'meta' => [
                'current_page' => $individuals->currentPage(),
                'last_page' => $individuals->lastPage(),
                'per_page' => $individuals->perPage(),
                'total' => $individuals->total(),
            ],
        ]);
    }

    /**
     * Display the specified individual
     */
    public function show(Individual $individual): JsonResponse
    {
        return response()->json([
            'data' => $individual->load(['tree', 'familiesAsHusband', 'familiesAsWife', 'familiesAsChild']),
        ]);
    }

    /**
     * Store a newly created individual
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'first_name' => 'required|string|max:255',
                'last_name' => 'required|string|max:255',
                'name_prefix' => 'nullable|string|max:50',
                'name_suffix' => 'nullable|string|max:50',
                'nickname' => 'nullable|string|max:255',
                'sex' => 'required|in:M,F,U',
                'birth_date' => 'nullable|date',
                'death_date' => 'nullable|date|after_or_equal:birth_date',
                'birth_place' => 'nullable|string|max:255',
                'death_place' => 'nullable|string|max:255',
                'tree_id' => 'required|exists:trees,id',
            ]);

            $individual = Individual::create($validated);

            return response()->json([
                'message' => 'Individual created successfully',
                'data' => $individual,
            ], 201);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        }
    }

    /**
     * Update the specified individual
     */
    public function update(Request $request, Individual $individual): JsonResponse
    {
        try {
            $validated = $request->validate([
                'first_name' => 'sometimes|required|string|max:255',
                'last_name' => 'sometimes|required|string|max:255',
                'name_prefix' => 'nullable|string|max:50',
                'name_suffix' => 'nullable|string|max:50',
                'nickname' => 'nullable|string|max:255',
                'sex' => 'sometimes|required|in:M,F,U',
                'birth_date' => 'nullable|date',
                'death_date' => 'nullable|date|after_or_equal:birth_date',
                'birth_place' => 'nullable|string|max:255',
                'death_place' => 'nullable|string|max:255',
            ]);

            $individual->update($validated);

            return response()->json([
                'message' => 'Individual updated successfully',
                'data' => $individual->fresh(),
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        }
    }

    /**
     * Remove the specified individual
     */
    public function destroy(Individual $individual): JsonResponse
    {
        $individual->delete();

        return response()->json([
            'message' => 'Individual deleted successfully',
        ]);
    }
}
