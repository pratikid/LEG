<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\ApiBaseController;
use App\Models\Family;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class FamilyController extends ApiBaseController
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

        return $this->paginatedResponse($families);
    }

    /**
     * Display the specified family
     */
    public function show(Family $family): JsonResponse
    {
        return $this->successResponse(
            $family->load(['husband', 'wife', 'children', 'tree'])
        );
    }

    /**
     * Get the validation rules for store method.
     */
    protected function getStoreValidationRules(Request $request): array
    {
        return [
            'tree_id' => 'required|exists:trees,id',
            'husband_id' => 'nullable|exists:individuals,id',
            'wife_id' => 'nullable|exists:individuals,id',
            'marriage_date' => 'nullable|date',
            'marriage_place' => 'nullable|string|max:255',
            'divorce_date' => 'nullable|date|after_or_equal:marriage_date',
        ];
    }

    /**
     * Get the validation rules for update method.
     */
    protected function getUpdateValidationRules(Request $request, $model): array
    {
        return [
            'husband_id' => 'nullable|exists:individuals,id',
            'wife_id' => 'nullable|exists:individuals,id',
            'marriage_date' => 'nullable|date',
            'marriage_place' => 'nullable|string|max:255',
            'divorce_date' => 'nullable|date|after_or_equal:marriage_date',
        ];
    }

    /**
     * Get the model class name.
     */
    protected function getModelClass(): string
    {
        return Family::class;
    }

    /**
     * Get the model display name for messages.
     */
    protected function getModelDisplayName(): string
    {
        return 'Family';
    }

    /**
     * Process additional logic after model update.
     */
    protected function afterUpdate($model, Request $request): void
    {
        $model->load(['husband', 'wife', 'children']);
    }
}
