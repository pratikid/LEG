<?php

declare(strict_types=1);

namespace App\Traits;

use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use InvalidArgumentException;

/**
 * Trait for handling common model operations
 */
trait HandlesModelOperations
{
    use HandlesQueryFiltering;

    /**
     * Get a model instance by ID with optional relations.
     *
     * @param  string  $modelClass  The model class name
     * @param  int  $id  The model ID
     * @param  array  $relations  Relations to eager load
     * @return Model The model instance
     *
     * @throws ModelNotFoundException If the model is not found
     */
    protected function getModelById(string $modelClass, int $id, array $relations = []): Model
    {
        $query = $modelClass::query();

        if (! empty($relations)) {
            $query->with($relations);
        }

        return $query->findOrFail($id);
    }

    /**
     * Create a new model instance with the given attributes.
     *
     * @param  string  $modelClass  The model class name
     * @param  array  $attributes  The model attributes
     * @return Model The created model instance
     */
    protected function createModel(string $modelClass, array $attributes): Model
    {
        return $modelClass::create($attributes);
    }

    /**
     * Update a model instance with the given attributes.
     *
     * @param  Model  $model  The model instance
     * @param  array  $attributes  The model attributes to update
     * @return Model The updated model instance
     */
    protected function updateModel(Model $model, array $attributes): Model
    {
        $model->update($attributes);

        return $model->fresh();
    }

    /**
     * Delete a model instance.
     *
     * @param  Model  $model  The model instance
     * @return bool Whether the deletion was successful
     */
    protected function deleteModel(Model $model): bool
    {
        return $model->delete();
    }

    /**
     * Get a filtered and paginated list of models.
     *
     * @param  string  $modelClass  The model class name
     * @param  array  $filters  Filters to apply
     * @param  array  $relations  Relations to eager load
     * @param  int  $perPage  Number of items per page
     * @param  string  $sortBy  Column to sort by
     * @param  string  $sortDirection  Sort direction (asc or desc)
     * @return \Illuminate\Pagination\LengthAwarePaginator The paginated results
     */
    protected function getFilteredModels(
        string $modelClass,
        array $filters = [],
        array $relations = [],
        int $perPage = 15,
        string $sortBy = 'created_at',
        string $sortDirection = 'desc'
    ): \Illuminate\Pagination\LengthAwarePaginator {
        $query = $modelClass::query();

        // Apply relations
        if (! empty($relations)) {
            $query->with($relations);
        }

        // Apply filters
        $this->applyFiltersToQuery($query, $filters, $modelClass);

        // Apply sorting
        if (Schema::hasColumn((new $modelClass)->getTable(), $sortBy)) {
            $query->orderBy($sortBy, $sortDirection);
        }

        return $query->paginate($perPage);
    }

    // Using extractFiltersFromRequest, applyFiltersToQuery, applyFieldFilter from HandlesQueryFiltering trait

    /**
     * Perform a bulk operation on multiple models.
     *
     * @param  string  $modelClass  The model class name
     * @param  array  $ids  The model IDs
     * @param  string  $operation  The operation to perform (update, delete)
     * @param  array  $attributes  The attributes for update operation
     * @return array Result of the operation
     */
    protected function bulkOperation(
        string $modelClass,
        array $ids,
        string $operation,
        array $attributes = []
    ): array {
        $result = [
            'success' => true,
            'processed' => count($ids),
            'succeeded' => 0,
            'failed' => 0,
            'errors' => [],
        ];

        DB::beginTransaction();

        try {
            foreach ($ids as $id) {
                try {
                    $model = $modelClass::findOrFail($id);

                    switch ($operation) {
                        case 'update':
                            $model->update($attributes);
                            break;

                        case 'delete':
                            $model->delete();
                            break;

                        default:
                            throw new InvalidArgumentException("Unknown operation: {$operation}");
                    }

                    $result['succeeded']++;
                } catch (Exception $e) {
                    $result['failed']++;
                    $result['errors'][$id] = $e->getMessage();
                }
            }

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            $result['success'] = false;
            $result['message'] = 'Bulk operation failed: '.$e->getMessage();
            Log::error('Bulk operation failed', [
                'operation' => $operation,
                'model' => $modelClass,
                'ids' => $ids,
                'error' => $e->getMessage(),
            ]);
        }

        return $result;
    }

    /**
     * Toggle a boolean field on a model.
     *
     * @param  Model  $model  The model instance
     * @param  string  $field  The field to toggle
     * @return Model The updated model instance
     */
    protected function toggleModelField(Model $model, string $field): Model
    {
        $model->{$field} = ! $model->{$field};
        $model->save();

        return $model;
    }

    /**
     * Duplicate a model instance.
     *
     * @param  Model  $model  The model instance to duplicate
     * @param  array  $overrideAttributes  Attributes to override in the duplicate
     * @return Model The duplicated model instance
     */
    protected function duplicateModel(Model $model, array $overrideAttributes = []): Model
    {
        $attributes = $model->getAttributes();

        // Remove primary key and timestamps
        unset($attributes[$model->getKeyName()]);
        unset($attributes['created_at']);
        unset($attributes['updated_at']);

        // Apply override attributes
        $attributes = array_merge($attributes, $overrideAttributes);

        // Create the duplicate
        $duplicate = $model->replicate()->fill($attributes);
        $duplicate->save();

        return $duplicate;
    }

    /**
     * Get the validation rules for a model.
     *
     * @param  string  $modelClass  The model class name
     * @param  string  $operation  The operation (create, update)
     * @param  int|null  $id  The model ID for update operation
     * @return array The validation rules
     */
    protected function getModelValidationRules(string $modelClass, string $operation, ?int $id = null): array
    {
        if (method_exists($modelClass, 'getValidationRules')) {
            return $modelClass::getValidationRules($operation, $id);
        }

        return [];
    }

    /**
     * Check if a model exists by a specific field value.
     *
     * @param  string  $modelClass  The model class name
     * @param  string  $field  The field to check
     * @param  mixed  $value  The value to check for
     * @param  int|null  $excludeId  ID to exclude from the check
     * @return bool Whether the model exists
     */
    protected function modelExistsByField(
        string $modelClass,
        string $field,
        $value,
        ?int $excludeId = null
    ): bool {
        $query = $modelClass::where($field, $value);

        if ($excludeId !== null) {
            $query->where('id', '!=', $excludeId);
        }

        return $query->exists();
    }

    /**
     * Get a count of models based on filters.
     *
     * @param  string  $modelClass  The model class name
     * @param  array  $filters  Filters to apply
     * @return int The count of models
     */
    protected function getModelCount(string $modelClass, array $filters = []): int
    {
        $query = $modelClass::query();

        // Apply filters
        $this->applyFiltersToQuery($query, $filters, $modelClass);

        return $query->count();
    }

    /**
     * Get the latest models.
     *
     * @param  string  $modelClass  The model class name
     * @param  int  $limit  Number of models to retrieve
     * @param  array  $relations  Relations to eager load
     * @return \Illuminate\Database\Eloquent\Collection The collection of models
     */
    protected function getLatestModels(
        string $modelClass,
        int $limit = 5,
        array $relations = []
    ): \Illuminate\Database\Eloquent\Collection {
        $query = $modelClass::query();

        // Apply relations
        if (! empty($relations)) {
            $query->with($relations);
        }

        return $query->latest()->limit($limit)->get();
    }
}
