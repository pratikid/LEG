<?php

declare(strict_types=1);

namespace App\Traits;

use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use InvalidArgumentException;
use ReflectionClass;

/**
 * Trait for handling common controller operations
 */
trait HandlesControllerOperations
{
    use HandlesQueryFiltering;
    // Using applyFiltersToQuery, applyFieldFilter from HandlesQueryFiltering trait

    /**
     * Apply sorting to a query based on request parameters.
     *
     * @param  Builder  $query  The query builder
     * @param  Request  $request  The HTTP request
     * @param  string  $defaultSortField  Default field to sort by
     * @param  string  $defaultSortDirection  Default sort direction
     * @param  array  $allowedSortFields  List of allowed sort fields
     * @return Builder The modified query builder
     */
    protected function applyRequestSorting(
        Builder $query,
        Request $request,
        string $defaultSortField = 'created_at',
        string $defaultSortDirection = 'desc',
        array $allowedSortFields = []
    ): Builder {
        $sortBy = $request->input('sort_by', $defaultSortField);
        $sortDirection = $request->input('sort_direction', $defaultSortDirection);

        // Validate sort direction
        if (! in_array(mb_strtolower($sortDirection), ['asc', 'desc'])) {
            $sortDirection = $defaultSortDirection;
        }

        // Only allow sorting by specified fields if provided
        if (! empty($allowedSortFields) && ! in_array($sortBy, $allowedSortFields)) {
            $sortBy = $defaultSortField;
        }

        $table = $query->getModel()->getTable();

        // Check if the sort field exists in the table
        if (Schema::hasColumn($table, $sortBy)) {
            $query->orderBy($sortBy, $sortDirection);
        } else {
            // Handle relation sorting (e.g., 'user.name')
            if (str_contains($sortBy, '.')) {
                [$relation, $relationField] = explode('.', $sortBy, 2);

                // Make sure the relation method exists on the model
                $model = $query->getModel();
                if (method_exists($model, $relation)) {
                    $relationObject = $model->{$relation}();
                    $relatedTable = $relationObject->getRelated()->getTable();

                    // Join the related table if it's a direct relation
                    if (method_exists($relationObject, 'getQualifiedForeignKeyName') &&
                        method_exists($relationObject, 'getQualifiedOwnerKeyName')) {
                        $query->join(
                            $relatedTable,
                            $relationObject->getQualifiedForeignKeyName(),
                            '=',
                            $relationObject->getQualifiedOwnerKeyName()
                        );

                        // Apply the sort
                        $query->orderBy("{$relatedTable}.{$relationField}", $sortDirection);

                        // Select only the main table fields to avoid duplicate results
                        $query->select("{$table}.*");
                    } else {
                        // Fall back to default sorting if relation is complex
                        $query->orderBy("{$table}.{$defaultSortField}", $defaultSortDirection);
                    }
                } else {
                    // Fall back to default sorting if relation doesn't exist
                    $query->orderBy("{$table}.{$defaultSortField}", $defaultSortDirection);
                }
            } else {
                // Fall back to default sorting if field doesn't exist
                $query->orderBy("{$table}.{$defaultSortField}", $defaultSortDirection);
            }
        }

        return $query;
    }

    /**
     * Apply pagination to a query based on request parameters.
     *
     * @param  Builder  $query  The query builder
     * @param  Request  $request  The HTTP request
     * @param  int  $defaultPerPage  Default number of items per page
     * @param  int  $maxPerPage  Maximum number of items per page
     * @return \Illuminate\Pagination\LengthAwarePaginator The paginated results
     */
    protected function applyRequestPagination(
        Builder $query,
        Request $request,
        int $defaultPerPage = 15,
        int $maxPerPage = 100
    ): \Illuminate\Pagination\LengthAwarePaginator {
        $perPage = (int) $request->input('per_page', $defaultPerPage);

        // Ensure per_page is within allowed limits
        if ($perPage < 1) {
            $perPage = $defaultPerPage;
        }

        if ($perPage > $maxPerPage) {
            $perPage = $maxPerPage;
        }

        return $query->paginate($perPage);
    }

    /**
     * Get a filtered, sorted, and paginated list of models.
     *
     * @param  string  $modelClass  The model class name
     * @param  Request  $request  The HTTP request
     * @param  array  $allowedFilters  List of allowed filter fields
     * @param  array  $allowedSortFields  List of allowed sort fields
     * @param  array  $relations  Relations to eager load
     * @param  int  $defaultPerPage  Default number of items per page
     * @return \Illuminate\Pagination\LengthAwarePaginator The paginated results
     */
    protected function getFilteredAndPaginatedModels(
        string $modelClass,
        Request $request,
        array $allowedFilters = [],
        array $allowedSortFields = [],
        array $relations = [],
        int $defaultPerPage = 15
    ): \Illuminate\Pagination\LengthAwarePaginator {
        $query = $modelClass::query();

        // Apply relations
        if (! empty($relations)) {
            $query->with($relations);
        }

        // Apply filters
        $this->applyRequestFilters($query, $request, $allowedFilters);

        // Apply sorting
        $this->applyRequestSorting($query, $request, 'created_at', 'desc', $allowedSortFields);

        // Apply pagination
        return $this->applyRequestPagination($query, $request, $defaultPerPage);
    }

    /**
     * Process a model creation request.
     *
     * @param  Request  $request  The HTTP request
     * @param  string  $modelClass  The model class name
     * @param  array  $validationRules  Validation rules
     * @param  array  $additionalAttributes  Additional attributes to add to the model
     * @param  callable|null  $beforeSave  Callback to execute before saving the model
     * @param  callable|null  $afterSave  Callback to execute after saving the model
     * @return Model The created model
     */
    protected function processModelCreation(
        Request $request,
        string $modelClass,
        array $validationRules,
        array $additionalAttributes = [],
        ?callable $beforeSave = null,
        ?callable $afterSave = null
    ): Model {
        // Validate the request
        $validatedData = $request->validate($validationRules);

        // Merge additional attributes
        $attributes = array_merge($validatedData, $additionalAttributes);

        // Create a new model instance
        $model = new $modelClass();
        $model->fill($attributes);

        // Execute before save callback if provided
        if ($beforeSave) {
            $beforeSave($model, $request);
        }

        // Save the model
        $model->save();

        // Execute after save callback if provided
        if ($afterSave) {
            $afterSave($model, $request);
        }

        return $model;
    }

    /**
     * Process a model update request.
     *
     * @param  Request  $request  The HTTP request
     * @param  Model  $model  The model to update
     * @param  array  $validationRules  Validation rules
     * @param  array  $additionalAttributes  Additional attributes to add to the model
     * @param  callable|null  $beforeSave  Callback to execute before saving the model
     * @param  callable|null  $afterSave  Callback to execute after saving the model
     * @return Model The updated model
     */
    protected function processModelUpdate(
        Request $request,
        Model $model,
        array $validationRules,
        array $additionalAttributes = [],
        ?callable $beforeSave = null,
        ?callable $afterSave = null
    ): Model {
        // Validate the request
        $validatedData = $request->validate($validationRules);

        // Merge additional attributes
        $attributes = array_merge($validatedData, $additionalAttributes);

        // Fill the model with the attributes
        $model->fill($attributes);

        // Execute before save callback if provided
        if ($beforeSave) {
            $beforeSave($model, $request);
        }

        // Save the model
        $model->save();

        // Execute after save callback if provided
        if ($afterSave) {
            $afterSave($model, $request);
        }

        return $model;
    }

    /**
     * Process a model deletion request.
     *
     * @param  Model  $model  The model to delete
     * @param  callable|null  $beforeDelete  Callback to execute before deleting the model
     * @param  callable|null  $afterDelete  Callback to execute after deleting the model
     * @return bool Whether the deletion was successful
     */
    protected function processModelDeletion(
        Model $model,
        ?callable $beforeDelete = null,
        ?callable $afterDelete = null
    ): bool {
        // Execute before delete callback if provided
        if ($beforeDelete) {
            $beforeDelete($model);
        }

        // Delete the model
        $result = $model->delete();

        // Execute after delete callback if provided
        if ($afterDelete && $result) {
            $afterDelete($model);
        }

        return $result;
    }

    /**
     * Process a bulk operation on multiple models.
     *
     * @param  string  $modelClass  The model class name
     * @param  array  $ids  The model IDs
     * @param  string  $operation  The operation to perform (update, delete)
     * @param  array  $attributes  The attributes for update operation
     * @param  callable|null  $beforeOperation  Callback to execute before each operation
     * @param  callable|null  $afterOperation  Callback to execute after each operation
     * @return array Result of the operation
     */
    protected function processBulkOperation(
        string $modelClass,
        array $ids,
        string $operation,
        array $attributes = [],
        ?callable $beforeOperation = null,
        ?callable $afterOperation = null
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

                    // Execute before operation callback if provided
                    if ($beforeOperation) {
                        $beforeOperation($model, $operation);
                    }

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

                    // Execute after operation callback if provided
                    if ($afterOperation) {
                        $afterOperation($model, $operation);
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
     * Add user ID to request data.
     *
     * @param  Request  $request  The HTTP request
     * @param  string  $field  The field name for the user ID
     * @return Request The modified request
     */
    protected function addUserIdToRequest(Request $request, string $field = 'user_id'): Request
    {
        $request->merge([$field => Auth::id()]);

        return $request;
    }

    /**
     * Add tree ID to request data.
     *
     * @param  Request  $request  The HTTP request
     * @param  string  $field  The field name for the tree ID
     * @return Request The modified request
     */
    protected function addTreeIdToRequest(Request $request, string $field = 'tree_id'): Request
    {
        // If tree_id is already set, don't override it
        if ($request->has($field)) {
            return $request;
        }

        // Try to get tree_id from the route parameters
        $treeId = $request->route('tree');

        if ($treeId) {
            $request->merge([$field => $treeId]);
        }

        return $request;
    }

    /**
     * Log a controller action.
     *
     * @param  string  $action  The action name
     * @param  mixed  $model  The model being acted upon
     * @param  Request  $request  The HTTP request
     */
    protected function logControllerAction(string $action, $model, Request $request): void
    {
        $modelClass = get_class($model);
        $modelId = $model->id ?? null;
        $userId = Auth::id();

        Log::info("Controller action: {$action}", [
            'model_class' => $modelClass,
            'model_id' => $modelId,
            'user_id' => $userId,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);
    }

    /**
     * Get a model display name from its class name.
     *
     * @param  string  $modelClass  The model class name
     * @return string The display name
     */
    protected function getModelDisplayNameFromClass(string $modelClass): string
    {
        $reflection = new ReflectionClass($modelClass);

        return Str::title(Str::snake(class_basename($reflection->getShortName()), ' '));
    }
}
