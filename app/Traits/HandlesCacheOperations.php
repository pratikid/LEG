<?php

declare(strict_types=1);

namespace App\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Trait for handling cache operations
 */
trait HandlesCacheOperations
{
    /**
     * Get a cached value or compute it if not cached.
     *
     * @param string $key The cache key
     * @param \Closure $callback The callback to compute the value if not cached
     * @param int $ttl The cache TTL in seconds
     * @return mixed The cached or computed value
     */
    protected function rememberCache(string $key, \Closure $callback, int $ttl = 3600)
    {
        return Cache::remember($key, $ttl, $callback);
    }
    
    /**
     * Get a cached value or compute it if not cached (forever).
     *
     * @param string $key The cache key
     * @param \Closure $callback The callback to compute the value if not cached
     * @return mixed The cached or computed value
     */
    protected function rememberCacheForever(string $key, \Closure $callback)
    {
        return Cache::rememberForever($key, $callback);
    }
    
    /**
     * Clear a specific cache key.
     *
     * @param string $key The cache key
     * @return bool Whether the operation was successful
     */
    protected function clearCache(string $key): bool
    {
        return Cache::forget($key);
    }
    
    /**
     * Clear multiple cache keys by prefix.
     *
     * @param string $prefix The cache key prefix
     * @return bool Whether the operation was successful
     */
    protected function clearCacheByPrefix(string $prefix): bool
    {
        try {
            $cache = Cache::getStore();
            
            if (method_exists($cache, 'clearAllCache')) {
                // Some cache stores have a clearAllCache method
                return Cache::getStore()->flush();
            } else {
                // For other stores, we need to use tags if available
                if (method_exists(Cache::getStore(), 'tags')) {
                    return Cache::tags($prefix)->flush();
                }
                
                // If neither method is available, log a warning
                Log::warning('Cache store does not support clearing by prefix or tags', [
                    'prefix' => $prefix,
                    'cache_driver' => config('cache.default'),
                ]);
                
                return false;
            }
        } catch (\Exception $e) {
            Log::error('Failed to clear cache by prefix', [
                'prefix' => $prefix,
                'error' => $e->getMessage(),
            ]);
            
            return false;
        }
    }
    
    /**
     * Generate a cache key for a model.
     *
     * @param Model $model The model
     * @param string $suffix Optional suffix
     * @return string The cache key
     */
    protected function generateModelCacheKey(Model $model, string $suffix = ''): string
    {
        $modelName = Str::snake(class_basename($model));
        $modelId = $model->getKey();
        
        $key = "model:{$modelName}:{$modelId}";
        
        if (!empty($suffix)) {
            $key .= ":{$suffix}";
        }
        
        return $key;
    }
    
    /**
     * Generate a cache key for a collection of models.
     *
     * @param string $modelClass The model class name
     * @param array $filters Optional filters
     * @param string $suffix Optional suffix
     * @return string The cache key
     */
    protected function generateCollectionCacheKey(string $modelClass, array $filters = [], string $suffix = ''): string
    {
        $modelName = Str::snake(class_basename($modelClass));
        $filterHash = !empty($filters) ? md5(json_encode($filters)) : 'all';
        
        $key = "collection:{$modelName}:{$filterHash}";
        
        if (!empty($suffix)) {
            $key .= ":{$suffix}";
        }
        
        return $key;
    }
    
    /**
     * Clear cache for a model.
     *
     * @param Model $model The model
     * @return void
     */
    protected function clearModelCache(Model $model): void
    {
        $modelName = Str::snake(class_basename($model));
        $modelId = $model->getKey();
        
        // Clear specific model cache
        $this->clearCache("model:{$modelName}:{$modelId}");
        
        // Clear any collection caches that might contain this model
        $this->clearCacheByPrefix("collection:{$modelName}");
        
        // If the model has relationships, clear related caches
        $this->clearRelatedModelCaches($model);
    }
    
    /**
     * Clear caches for models related to the given model.
     *
     * @param Model $model The model
     * @return void
     */
    protected function clearRelatedModelCaches(Model $model): void
    {
        // Get all loaded relations
        $relations = $model->getRelations();
        
        foreach ($relations as $relationName => $related) {
            if (empty($related)) {
                continue;
            }
            
            if ($related instanceof Model) {
                // Single model relation
                $this->clearModelCache($related);
            } elseif (is_iterable($related)) {
                // Collection of models
                foreach ($related as $relatedModel) {
                    if ($relatedModel instanceof Model) {
                        $this->clearModelCache($relatedModel);
                    }
                }
            }
        }
    }
    
    /**
     * Cache a model with its relationships.
     *
     * @param Model $model The model
     * @param array $with Relationships to eager load
     * @param int $ttl The cache TTL in seconds
     * @return Model The cached model
     */
    protected function cacheModel(Model $model, array $with = [], int $ttl = 3600): Model
    {
        $modelClass = get_class($model);
        $modelId = $model->getKey();
        
        // If relationships are specified, load them
        if (!empty($with)) {
            $model->load($with);
        }
        
        // Generate a cache key
        $cacheKey = $this->generateModelCacheKey($model, !empty($with) ? implode('_', $with) : '');
        
        // Cache the model
        Cache::put($cacheKey, $model, $ttl);
        
        return $model;
    }
    
    /**
     * Get a cached model or retrieve it from the database.
     *
     * @param string $modelClass The model class name
     * @param mixed $id The model ID
     * @param array $with Relationships to eager load
     * @param int $ttl The cache TTL in seconds
     * @return Model|null The model or null if not found
     */
    protected function getCachedModel(string $modelClass, $id, array $with = [], int $ttl = 3600): ?Model
    {
        $modelName = Str::snake(class_basename($modelClass));
        $withSuffix = !empty($with) ? implode('_', $with) : '';
        
        $cacheKey = "model:{$modelName}:{$id}" . (!empty($withSuffix) ? ":{$withSuffix}" : '');
        
        return $this->rememberCache($cacheKey, function () use ($modelClass, $id, $with) {
            $query = $modelClass::query();
            
            if (!empty($with)) {
                $query->with($with);
            }
            
            return $query->find($id);
        }, $ttl);
    }
    
    /**
     * Get a cached collection of models or retrieve them from the database.
     *
     * @param string $modelClass The model class name
     * @param array $filters Query filters
     * @param array $with Relationships to eager load
     * @param string $orderBy Field to order by
     * @param string $orderDirection Order direction
     * @param int $ttl The cache TTL in seconds
     * @return \Illuminate\Database\Eloquent\Collection The collection of models
     */
    protected function getCachedCollection(
        string $modelClass,
        array $filters = [],
        array $with = [],
        string $orderBy = 'created_at',
        string $orderDirection = 'desc',
        int $ttl = 3600
    ): \Illuminate\Database\Eloquent\Collection {
        $filterHash = !empty($filters) ? md5(json_encode($filters)) : 'all';
        $withSuffix = !empty($with) ? implode('_', $with) : '';
        $orderSuffix = "{$orderBy}_{$orderDirection}";
        
        $cacheKey = $this->generateCollectionCacheKey(
            $modelClass,
            $filters,
            "{$withSuffix}_{$orderSuffix}"
        );
        
        return $this->rememberCache($cacheKey, function () use (
            $modelClass,
            $filters,
            $with,
            $orderBy,
            $orderDirection
        ) {
            $query = $modelClass::query();
            
            // Apply filters
            foreach ($filters as $field => $value) {
                if (is_array($value)) {
                    $query->whereIn($field, $value);
                } else {
                    $query->where($field, $value);
                }
            }
            
            // Apply eager loading
            if (!empty($with)) {
                $query->with($with);
            }
            
            // Apply ordering
            $query->orderBy($orderBy, $orderDirection);
            
            return $query->get();
        }, $ttl);
    }
    
    /**
     * Get a cached paginated collection of models or retrieve them from the database.
     *
     * @param string $modelClass The model class name
     * @param int $perPage Number of items per page
     * @param array $filters Query filters
     * @param array $with Relationships to eager load
     * @param string $orderBy Field to order by
     * @param string $orderDirection Order direction
     * @param int $ttl The cache TTL in seconds
     * @return \Illuminate\Pagination\LengthAwarePaginator The paginated collection
     */
    protected function getCachedPaginatedCollection(
        string $modelClass,
        int $perPage = 15,
        array $filters = [],
        array $with = [],
        string $orderBy = 'created_at',
        string $orderDirection = 'desc',
        int $ttl = 3600
    ): \Illuminate\Pagination\LengthAwarePaginator {
        $page = request()->input('page', 1);
        $filterHash = !empty($filters) ? md5(json_encode($filters)) : 'all';
        $withSuffix = !empty($with) ? implode('_', $with) : '';
        $orderSuffix = "{$orderBy}_{$orderDirection}";
        
        $cacheKey = $this->generateCollectionCacheKey(
            $modelClass,
            $filters,
            "page_{$page}_per_page_{$perPage}_{$withSuffix}_{$orderSuffix}"
        );
        
        return $this->rememberCache($cacheKey, function () use (
            $modelClass,
            $perPage,
            $filters,
            $with,
            $orderBy,
            $orderDirection
        ) {
            $query = $modelClass::query();
            
            // Apply filters
            foreach ($filters as $field => $value) {
                if (is_array($value)) {
                    $query->whereIn($field, $value);
                } else {
                    $query->where($field, $value);
                }
            }
            
            // Apply eager loading
            if (!empty($with)) {
                $query->with($with);
            }
            
            // Apply ordering
            $query->orderBy($orderBy, $orderDirection);
            
            return $query->paginate($perPage);
        }, $ttl);
    }
    
    /**
     * Cache the count of models.
     *
     * @param string $modelClass The model class name
     * @param array $filters Query filters
     * @param int $ttl The cache TTL in seconds
     * @return int The count
     */
    protected function getCachedCount(string $modelClass, array $filters = [], int $ttl = 3600): int
    {
        $modelName = Str::snake(class_basename($modelClass));
        $filterHash = !empty($filters) ? md5(json_encode($filters)) : 'all';
        
        $cacheKey = "count:{$modelName}:{$filterHash}";
        
        return $this->rememberCache($cacheKey, function () use ($modelClass, $filters) {
            $query = $modelClass::query();
            
            // Apply filters
            foreach ($filters as $field => $value) {
                if (is_array($value)) {
                    $query->whereIn($field, $value);
                } else {
                    $query->where($field, $value);
                }
            }
            
            return $query->count();
        }, $ttl);
    }
    
    /**
     * Clear all application cache.
     *
     * @return bool Whether the operation was successful
     */
    protected function clearAllCache(): bool
    {
        try {
            Cache::flush();
            return true;
        } catch (\Exception $e) {
            Log::error('Failed to clear all cache', [
                'error' => $e->getMessage(),
            ]);
            
            return false;
        }
    }
    
    /**
     * Determine if caching should be used based on environment.
     *
     * @return bool Whether caching should be used
     */
    protected function shouldUseCache(): bool
    {
        // Don't use cache in local or testing environments unless explicitly enabled
        if (app()->environment(['local', 'testing']) && !config('cache.force_enable')) {
            return false;
        }
        
        return config('cache.enabled', true);
    }
    
    /**
     * Get the appropriate cache TTL based on the model and operation.
     *
     * @param string $modelClass The model class name
     * @param string $operation The operation (show, index, count)
     * @return int The cache TTL in seconds
     */
    protected function getCacheTtl(string $modelClass, string $operation = 'show'): int
    {
        $modelName = Str::snake(class_basename($modelClass));
        
        // Check for model-specific cache TTL in config
        $modelTtl = config("cache.ttl.{$modelName}.{$operation}");
        if ($modelTtl !== null) {
            return $modelTtl;
        }
        
        // Check for operation-specific cache TTL in config
        $operationTtl = config("cache.ttl.{$operation}");
        if ($operationTtl !== null) {
            return $operationTtl;
        }
        
        // Default TTLs based on operation
        switch ($operation) {
            case 'index':
                return config('cache.ttl.default.index', 1800); // 30 minutes
                
            case 'count':
                return config('cache.ttl.default.count', 3600); // 1 hour
                
            case 'show':
            default:
                return config('cache.ttl.default.show', 3600); // 1 hour
        }
    }
}