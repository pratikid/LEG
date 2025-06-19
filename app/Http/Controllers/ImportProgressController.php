<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\ImportProgress;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ImportProgressController extends Controller
{
    /**
     * Get import progress for a specific tree.
     */
    public function getProgress(Request $request, int $treeId): JsonResponse
    {
        $user = $request->user();
        if (! $user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $progress = ImportProgress::where([
            'user_id' => $user->id,
            'tree_id' => $treeId,
        ])->first();

        if (! $progress) {
            return response()->json(['error' => 'Import progress not found'], 404);
        }

        return response()->json([
            'status' => $progress->status,
            'total_records' => $progress->total_records,
            'processed_records' => $progress->processed_records,
            'progress_percentage' => $progress->progress_percentage,
            'error_message' => $progress->error_message,
            'created_at' => $progress->created_at,
            'updated_at' => $progress->updated_at,
        ]);
    }

    /**
     * Get all import progress for the current user.
     */
    public function getAllProgress(Request $request): JsonResponse
    {
        $user = $request->user();
        if (! $user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $progress = ImportProgress::where('user_id', $user->id)
            ->with(['tree'])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'progress' => $progress->map(function ($item) {
                return [
                    'id' => $item->id,
                    'tree_id' => $item->tree_id,
                    'tree_name' => $item->tree->name,
                    'status' => $item->status,
                    'total_records' => $item->total_records,
                    'processed_records' => $item->processed_records,
                    'progress_percentage' => $item->progress_percentage,
                    'error_message' => $item->error_message,
                    'created_at' => $item->created_at,
                    'updated_at' => $item->updated_at,
                ];
            }),
        ]);
    }
}
