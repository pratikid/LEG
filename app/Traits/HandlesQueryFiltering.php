<?php

declare(strict_types=1);

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

/**
 * Trait for handling common query filtering operations
 */
trait HandlesQueryFiltering
{
    /**
     * Apply filters to a query based on request parameters.
     *
     * @param  Builder  $query  The query builder
     * @param  Request  $request  The HTTP request
     * @param  array  $allowedFilters  List of allowed filter fields
     * @return Builder The modified query builder
     */
    protected function applyFiltersToQuery(Builder $query, Request $request, array $allowedFilters = []): Builder
    {
        $filters = $this->extractFiltersFromRequest($request, $allowedFilters);
        $columns = $query->getModel()->getFillable();

        foreach ($filters as $field => $value) {
            if (empty($value)) {
                continue;
            }

            // Handle special filters
            if (str_contains($field, '.')) {
                // Relation filter (e.g., 'user.name')
                [$relation, $relationField] = explode('.', $field, 2);
                $query->whereHas($relation, function ($q) use ($relationField, $value) {
                    $this->applyFieldFilter($q, $relationField, $value);
                });
            } elseif (in_array($field, $columns)) {
                // Direct column filter
                $this->applyFieldFilter($query, $field, $value);
            }
        }

        return $query;
    }

    /**
     * Apply a filter to a specific field.
     *
     * @param  Builder  $query  The query builder
     * @param  string  $field  The field to filter
     * @param  mixed  $value  The filter value
     * @return Builder The modified query builder
     */
    protected function applyFieldFilter(Builder $query, string $field, $value): Builder
    {
        if (is_array($value)) {
            // Array value means 'in' condition
            $query->whereIn($field, $value);
        } elseif (is_string($value) && str_contains($value, '%')) {
            // Contains % means LIKE condition
            $query->where($field, 'LIKE', $value);
        } elseif (is_string($value) && str_starts_with($value, '>=')) {
            // Greater than or equal condition
            $query->where($field, '>=', mb_substr($value, 2));
        } elseif (is_string($value) && str_starts_with($value, '<=')) {
            // Less than or equal condition
            $query->where($field, '<=', mb_substr($value, 2));
        } elseif (is_string($value) && str_starts_with($value, '>')) {
            // Greater than condition
            $query->where($field, '>', mb_substr($value, 1));
        } elseif (is_string($value) && str_starts_with($value, '<')) {
            // Less than condition
            $query->where($field, '<', mb_substr($value, 1));
        } elseif (is_string($value) && str_starts_with($value, '!')) {
            // Not equal condition
            $query->where($field, '!=', mb_substr($value, 1));
        } else {
            // Exact match
            $query->where($field, $value);
        }

        return $query;
    }

    /**
     * Extract filters from a request.
     *
     * @param  Request  $request  The HTTP request
     * @param  array  $allowedFilters  List of allowed filter fields
     * @return array The extracted filters
     */
    protected function extractFiltersFromRequest(Request $request, array $allowedFilters = []): array
    {
        $filters = [];

        foreach ($request->all() as $key => $value) {
            // Skip pagination and sorting parameters
            if (in_array($key, ['page', 'per_page', 'sort_by', 'sort_dir'])) {
                continue;
            }

            // Only include allowed filters if specified
            if (! empty($allowedFilters) && ! in_array($key, $allowedFilters)) {
                continue;
            }

            $filters[$key] = $value;
        }

        return $filters;
    }
}
