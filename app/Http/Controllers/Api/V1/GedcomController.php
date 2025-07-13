<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Tree;
use App\Services\GedcomService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

final class GedcomController extends Controller
{
    public function __construct(
        private readonly GedcomService $gedcomService
    ) {}

    /**
     * Import GEDCOM file
     */
    public function import(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'file' => 'required|file|mimes:ged|max:10240', // 10MB max
                'tree_id' => 'required|exists:trees,id',
                'import_method' => 'nullable|in:standard,optimized',
            ]);

            $file = $request->file('file');
            $gedcomContent = file_get_contents($file->getPathname());

            if ($gedcomContent === false) {
                return response()->json([
                    'message' => 'Failed to read GEDCOM file',
                ], 400);
            }

            $parsed = $this->gedcomService->parse($gedcomContent);
            $this->gedcomService->importToDatabase($parsed, $validated['tree_id']);

            return response()->json([
                'message' => 'GEDCOM imported successfully',
                'data' => [
                    'individuals_count' => count($parsed['individuals']),
                    'families_count' => count($parsed['families']),
                    'tree_id' => $validated['tree_id'],
                ],
            ], 201);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Import failed',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Export GEDCOM file
     */
    public function export(Tree $tree): JsonResponse
    {
        try {
            $gedcomContent = $this->gedcomService->exportFromDatabase($tree->id);

            return response()->json([
                'message' => 'GEDCOM exported successfully',
                'data' => [
                    'gedcom_content' => $gedcomContent,
                    'tree_id' => $tree->id,
                ],
            ]);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Export failed',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Validate GEDCOM file
     */
    public function validate(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'file' => 'required|file|mimes:ged|max:10240',
            ]);

            $file = $request->file('file');
            $gedcomContent = file_get_contents($file->getPathname());

            if ($gedcomContent === false) {
                return response()->json([
                    'message' => 'Failed to read GEDCOM file',
                ], 400);
            }

            $parsed = $this->gedcomService->parse($gedcomContent);

            return response()->json([
                'message' => 'GEDCOM validation completed',
                'data' => [
                    'is_valid' => true,
                    'individuals_count' => count($parsed['individuals']),
                    'families_count' => count($parsed['families']),
                    'sources_count' => count($parsed['sources']),
                    'notes_count' => count($parsed['notes']),
                ],
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'GEDCOM validation failed',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
