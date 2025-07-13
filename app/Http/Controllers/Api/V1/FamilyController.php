<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Family;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

final class FamilyController extends Controller
{
    /**
     * Display a listing of families
     */
    public function index(Request $request): JsonResponse
    {
        $query = Family::query();

        if ($request->has('tree_id')) {
            $query->where('tree_id', $request->integer('tree_id'));
        }

        if ($request->has('husband_id')) {
            $query->where('husband_id', $request->integer('husband_id'));
        }

        if ($request->has('wife_id')) {
            $query->where('wife_id', $request->integer('wife_id'));
        }

        $families = $query->with(['husband', 'wife', 'children'])->paginate($request->integer('per_page', 15));

        return response()->json([
            'data' => $families->items(),
            'meta' => [
                'current_page' => $families->currentPage(),
                'last_page' => $families->lastPage(),
                'per_page' => $families->perPage(),
                'total' => $families->total(),
            ],
        ]);
    }

    /**
     * Display the specified family
     */
    public function show(Family $family): JsonResponse
    {
        return response()->json([
            'data' => $family->load(['husband', 'wife', 'children', 'tree']),
        ]);
    }

    /**
     * Store a newly created family
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'tree_id' => 'required|exists:trees,id',
                'husband_id' => 'nullable|exists:individuals,id',
                'wife_id' => 'nullable|exists:individuals,id',
                'marriage_date' => 'nullable|date',
                'marriage_place' => 'nullable|string|max:255',
                'divorce_date' => 'nullable|date|after_or_equal:marriage_date',
            ]);

            $family = Family::create($validated);

            return response()->json([
                'message' => 'Family created successfully',
                'data' => $family,
            ], 201);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        }
    }

    /**
     * Update the specified family
     */
    public function update(Request $request, Family $family): JsonResponse
    {
        try {
            $validated = $request->validate([
                'husband_id' => 'nullable|exists:individuals,id',
                'wife_id' => 'nullable|exists:individuals,id',
                'marriage_date' => 'nullable|date',
                'marriage_place' => 'nullable|string|max:255',
                'divorce_date' => 'nullable|date|after_or_equal:marriage_date',
            ]);

            $family->update($validated);

            return response()->json([
                'message' => 'Family updated successfully',
                'data' => $family->fresh(),
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        }
    }

    /**
     * Remove the specified family
     */
    public function destroy(Family $family): JsonResponse
    {
        $family->delete();

        return response()->json([
            'message' => 'Family deleted successfully',
        ]);
    }
}
