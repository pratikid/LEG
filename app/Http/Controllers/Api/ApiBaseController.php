<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Traits\ApiResponseTrait;
use App\Traits\HandlesValidationErrors;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Base controller for API endpoints with common functionality
 */
abstract class ApiBaseController extends Controller
{
    use ApiResponseTrait;
    use HandlesValidationErrors;

    /**
     * Get the validation rules for store method.
     *
     * @return array<string, mixed>
     */
    abstract protected function getStoreValidationRules(Request $request): array;

    /**
     * Get the validation rules for update method.
     *
     * @param  mixed  $model
     * @return array<string, mixed>
     */
    abstract protected function getUpdateValidationRules(Request $request, $model): array;

    /**
     * Get the model class name.
     */
    abstract protected function getModelClass(): string;

    /**
     * Get the model display name for messages.
     */
    abstract protected function getModelDisplayName(): string;

    /**
     * Store a newly created resource.
     */
    final public function store(Request $request): JsonResponse
    {
        return $this->tryValidation(function () use ($request) {
            $validated = $request->validate($this->getStoreValidationRules($request));

            $modelClass = $this->getModelClass();
            $model = $modelClass::create($validated);

            $this->afterStore($model, $request);

            return $this->successResponse(
                $model,
                $this->getModelDisplayName().' created successfully',
                201
            );
        });
    }

    /**
     * Update the specified resource.
     *
     * @param  mixed  $model
     */
    final public function update(Request $request, $model): JsonResponse
    {
        return $this->tryValidation(function () use ($request, $model) {
            $validated = $request->validate($this->getUpdateValidationRules($request, $model));

            $model->update($validated);

            $this->afterUpdate($model, $request);

            return $this->successResponse(
                $model,
                $this->getModelDisplayName().' updated successfully'
            );
        });
    }

    /**
     * Remove the specified resource.
     *
     * @param  mixed  $model
     */
    final public function destroy($model, Request $request): JsonResponse
    {
        $model->delete();

        $this->afterDestroy($model, $request);

        return $this->successResponse(
            null,
            $this->getModelDisplayName().' deleted successfully'
        );
    }

    /**
     * Process additional logic after model creation.
     *
     * @param  mixed  $model  The created model
     * @param  Request  $request  The request object
     */
    protected function afterStore($model, Request $request): void
    {
        // Default implementation does nothing
    }

    /**
     * Process additional logic after model update.
     *
     * @param  mixed  $model  The updated model
     * @param  Request  $request  The request object
     */
    protected function afterUpdate($model, Request $request): void
    {
        // Default implementation does nothing
    }

    /**
     * Process additional logic after model deletion.
     *
     * @param  mixed  $model  The deleted model
     * @param  Request  $request  The request object
     */
    protected function afterDestroy($model, Request $request): void
    {
        // Default implementation does nothing
    }
}
