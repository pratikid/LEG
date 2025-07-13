<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Family;
use App\Models\Individual;
use App\Models\Tree;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class SearchController extends Controller
{
    /**
     * Search individuals
     */
    public function individuals(Request $request): JsonResponse
    {
        $query = $request->string('q', '');
        $treeId = $request->integer('tree_id');

        $individuals = Individual::query()
            ->when($treeId, fn ($q) => $q->where('tree_id', $treeId))
            ->when($query, function ($q) use ($query) {
                $q->where(function ($subQ) use ($query) {
                    $subQ->where('first_name', 'ilike', "%{$query}%")
                        ->orWhere('last_name', 'ilike', "%{$query}%")
                        ->orWhere('nickname', 'ilike', "%{$query}%");
                });
            })
            ->paginate($request->integer('per_page', 15));

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
     * Search families
     */
    public function families(Request $request): JsonResponse
    {
        $query = $request->string('q', '');
        $treeId = $request->integer('tree_id');

        $families = Family::query()
            ->with(['husband', 'wife', 'children'])
            ->when($treeId, fn ($q) => $q->where('tree_id', $treeId))
            ->when($query, function ($q) use ($query) {
                $q->whereHas('husband', function ($subQ) use ($query) {
                    $subQ->where('first_name', 'ilike', "%{$query}%")
                        ->orWhere('last_name', 'ilike', "%{$query}%");
                })->orWhereHas('wife', function ($subQ) use ($query) {
                    $subQ->where('first_name', 'ilike', "%{$query}%")
                        ->orWhere('last_name', 'ilike', "%{$query}%");
                });
            })
            ->paginate($request->integer('per_page', 15));

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
     * Search trees
     */
    public function trees(Request $request): JsonResponse
    {
        $query = $request->string('q', '');
        $userId = $request->integer('user_id');

        $trees = Tree::query()
            ->when($userId, fn ($q) => $q->where('user_id', $userId))
            ->when($query, function ($q) use ($query) {
                $q->where('name', 'ilike', "%{$query}%")
                    ->orWhere('description', 'ilike', "%{$query}%");
            })
            ->paginate($request->integer('per_page', 15));

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
}
