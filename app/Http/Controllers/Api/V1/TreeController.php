<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Tree;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

final class TreeController extends Controller
{
    /**
     * Display a listing of trees
     */
    public function index(Request $request): JsonResponse
    {
        $query = Tree::query();

        if ($request->has('user_id')) {
            $query->where('user_id', $request->integer('user_id'));
        }

        if ($request->has('search')) {
            $search = $request->string('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'ilike', "%{$search}%")
                    ->orWhere('description', 'ilike', "%{$search}%");
            });
        }

        $trees = $query->paginate($request->integer('per_page', 15));

        return response()->json([
            'data' => $trees->items(),
            'meta' => [
                'current_page' => $trees->currentPage(),
                'last_page' => $trees->lastPage(),
                'per_page' => $trees->perPage(),
                'total' => $trees->total(),
            ],
        ]);
    }

    /**
     * Display the specified tree
     */
    public function show(Tree $tree): JsonResponse
    {
        return response()->json([
            'data' => $tree->load(['user', 'individuals']),
        ]);
    }

    /**
     * Store a newly created tree
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'description' => 'nullable|string|max:1000',
                'user_id' => 'required|exists:users,id',
            ]);

            $tree = Tree::create($validated);

            return response()->json([
                'message' => 'Tree created successfully',
                'data' => $tree,
            ], 201);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        }
    }

    /**
     * Update the specified tree
     */
    public function update(Request $request, Tree $tree): JsonResponse
    {
        try {
            $validated = $request->validate([
                'name' => 'sometimes|required|string|max:255',
                'description' => 'nullable|string|max:1000',
            ]);

            $tree->update($validated);

            return response()->json([
                'message' => 'Tree updated successfully',
                'data' => $tree->fresh(),
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        }
    }

    /**
     * Remove the specified tree
     */
    public function destroy(Tree $tree): JsonResponse
    {
        $tree->delete();

        return response()->json([
            'message' => 'Tree deleted successfully',
        ]);
    }
}
