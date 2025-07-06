<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\Individual;
use App\Models\Tree;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;

/**
 * Monitoring and observability service for LEG platform
 */
final class MonitoringService
{
    private const CACHE_TTL = 300; // 5 minutes
    private const METRICS_PREFIX = 'leg:metrics:';

    /**
     * Get system health metrics
     */
    public function getSystemHealth(): array
    {
        return [
            'database' => $this->checkDatabaseHealth(),
            'redis' => $this->checkRedisHealth(),
            'neo4j' => $this->checkNeo4jHealth(),
            'mongodb' => $this->checkMongoHealth(),
            'memory' => $this->getMemoryUsage(),
            'disk' => $this->getDiskUsage(),
            'uptime' => $this->getUptime(),
        ];
    }

    /**
     * Get application performance metrics
     */
    public function getPerformanceMetrics(): array
    {
        return [
            'response_time' => $this->getAverageResponseTime(),
            'throughput' => $this->getRequestThroughput(),
            'error_rate' => $this->getErrorRate(),
            'active_users' => $this->getActiveUsers(),
            'database_queries' => $this->getDatabaseQueryMetrics(),
            'cache_hit_rate' => $this->getCacheHitRate(),
        ];
    }

    /**
     * Get business metrics
     */
    public function getBusinessMetrics(): array
    {
        return [
            'total_users' => $this->getTotalUsers(),
            'total_trees' => $this->getTotalTrees(),
            'total_individuals' => $this->getTotalIndividuals(),
            'gedcom_imports' => $this->getGedcomImportMetrics(),
            'user_engagement' => $this->getUserEngagementMetrics(),
            'data_growth' => $this->getDataGrowthMetrics(),
        ];
    }

    /**
     * Check database health
     */
    private function checkDatabaseHealth(): array
    {
        try {
            $startTime = microtime(true);
            DB::select('SELECT 1');
            $responseTime = (microtime(true) - $startTime) * 1000;

            return [
                'status' => 'healthy',
                'response_time_ms' => round($responseTime, 2),
                'connections' => $this->getDatabaseConnections(),
                'slow_queries' => $this->getSlowQueries(),
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'unhealthy',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Check Redis health
     */
    private function checkRedisHealth(): array
    {
        try {
            $startTime = microtime(true);
            Redis::ping();
            $responseTime = (microtime(true) - $startTime) * 1000;

            return [
                'status' => 'healthy',
                'response_time_ms' => round($responseTime, 2),
                'memory_usage' => $this->getRedisMemoryUsage(),
                'connected_clients' => $this->getRedisConnectedClients(),
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'unhealthy',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Check Neo4j health
     */
    private function checkNeo4jHealth(): array
    {
        try {
            $startTime = microtime(true);
            // This would need to be implemented based on your Neo4j client
            $responseTime = (microtime(true) - $startTime) * 1000;

            return [
                'status' => 'healthy',
                'response_time_ms' => round($responseTime, 2),
                'node_count' => $this->getNeo4jNodeCount(),
                'relationship_count' => $this->getNeo4jRelationshipCount(),
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'unhealthy',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Check MongoDB health
     */
    private function checkMongoHealth(): array
    {
        try {
            $startTime = microtime(true);
            // This would need to be implemented based on your MongoDB client
            $responseTime = (microtime(true) - $startTime) * 1000;

            return [
                'status' => 'healthy',
                'response_time_ms' => round($responseTime, 2),
                'document_count' => $this->getMongoDocumentCount(),
                'collection_count' => $this->getMongoCollectionCount(),
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'unhealthy',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Get memory usage
     */
    private function getMemoryUsage(): array
    {
        $memoryLimit = ini_get('memory_limit');
        $memoryUsage = memory_get_usage(true);
        $peakMemory = memory_get_peak_usage(true);

        return [
            'current_mb' => round($memoryUsage / 1024 / 1024, 2),
            'peak_mb' => round($peakMemory / 1024 / 1024, 2),
            'limit_mb' => $this->parseMemoryLimit($memoryLimit),
            'usage_percentage' => round(($memoryUsage / $this->parseMemoryLimit($memoryLimit)) * 100, 2),
        ];
    }

    /**
     * Get disk usage
     */
    private function getDiskUsage(): array
    {
        $path = storage_path();
        $totalSpace = disk_total_space($path);
        $freeSpace = disk_free_space($path);
        $usedSpace = $totalSpace - $freeSpace;

        return [
            'total_gb' => round($totalSpace / 1024 / 1024 / 1024, 2),
            'used_gb' => round($usedSpace / 1024 / 1024 / 1024, 2),
            'free_gb' => round($freeSpace / 1024 / 1024 / 1024, 2),
            'usage_percentage' => round(($usedSpace / $totalSpace) * 100, 2),
        ];
    }

    /**
     * Get system uptime
     */
    private function getUptime(): array
    {
        $startTime = Cache::get('leg:start_time');
        if (!$startTime) {
            $startTime = now();
            Cache::put('leg:start_time', $startTime, 86400); // 24 hours
        }

        $uptime = now()->diff($startTime);

        return [
            'start_time' => $startTime->toISOString(),
            'uptime_days' => $uptime->days,
            'uptime_hours' => $uptime->h,
            'uptime_minutes' => $uptime->i,
        ];
    }

    /**
     * Get average response time
     */
    private function getAverageResponseTime(): float
    {
        $key = self::METRICS_PREFIX . 'response_times';
        $responseTimes = Cache::get($key, []);

        if (empty($responseTimes)) {
            return 0.0;
        }

        return round(array_sum($responseTimes) / count($responseTimes), 2);
    }

    /**
     * Get request throughput
     */
    private function getRequestThroughput(): array
    {
        $currentMinute = now()->format('Y-m-d H:i');
        $key = self::METRICS_PREFIX . 'requests:' . $currentMinute;
        $requests = Cache::get($key, 0);

        return [
            'requests_per_minute' => $requests,
            'requests_per_hour' => $this->getRequestsPerHour(),
            'requests_per_day' => $this->getRequestsPerDay(),
        ];
    }

    /**
     * Get error rate
     */
    private function getErrorRate(): array
    {
        $currentMinute = now()->format('Y-m-d H:i');
        $totalKey = self::METRICS_PREFIX . 'requests:' . $currentMinute;
        $errorKey = self::METRICS_PREFIX . 'errors:' . $currentMinute;

        $totalRequests = Cache::get($totalKey, 0);
        $totalErrors = Cache::get($errorKey, 0);

        $errorRate = $totalRequests > 0 ? ($totalErrors / $totalRequests) * 100 : 0;

        return [
            'error_rate_percentage' => round($errorRate, 2),
            'total_errors' => $totalErrors,
            'total_requests' => $totalRequests,
        ];
    }

    /**
     * Get active users
     */
    private function getActiveUsers(): array
    {
        $activeUsers = User::where('last_activity_at', '>=', now()->subMinutes(15))->count();
        $onlineUsers = User::where('last_activity_at', '>=', now()->subMinutes(5))->count();

        return [
            'active_users' => $activeUsers,
            'online_users' => $onlineUsers,
            'total_users' => User::count(),
        ];
    }

    /**
     * Get database query metrics
     */
    private function getDatabaseQueryMetrics(): array
    {
        $slowQueries = $this->getSlowQueries();
        $totalQueries = Cache::get(self::METRICS_PREFIX . 'db_queries', 0);

        return [
            'total_queries' => $totalQueries,
            'slow_queries' => count($slowQueries),
            'average_query_time_ms' => $this->getAverageQueryTime(),
        ];
    }

    /**
     * Get cache hit rate
     */
    private function getCacheHitRate(): array
    {
        $hits = Cache::get(self::METRICS_PREFIX . 'cache_hits', 0);
        $misses = Cache::get(self::METRICS_PREFIX . 'cache_misses', 0);
        $total = $hits + $misses;

        $hitRate = $total > 0 ? ($hits / $total) * 100 : 0;

        return [
            'hit_rate_percentage' => round($hitRate, 2),
            'hits' => $hits,
            'misses' => $misses,
            'total' => $total,
        ];
    }

    /**
     * Get total users
     */
    private function getTotalUsers(): int
    {
        return Cache::remember('leg:metrics:total_users', self::CACHE_TTL, function () {
            return User::count();
        });
    }

    /**
     * Get total trees
     */
    private function getTotalTrees(): int
    {
        return Cache::remember('leg:metrics:total_trees', self::CACHE_TTL, function () {
            return Tree::count();
        });
    }

    /**
     * Get total individuals
     */
    private function getTotalIndividuals(): int
    {
        return Cache::remember('leg:metrics:total_individuals', self::CACHE_TTL, function () {
            return Individual::count();
        });
    }

    /**
     * Get GEDCOM import metrics
     */
    private function getGedcomImportMetrics(): array
    {
        $today = now()->startOfDay();
        $thisWeek = now()->startOfWeek();
        $thisMonth = now()->startOfMonth();

        return [
            'imports_today' => ActivityLog::where('action', 'gedcom_import')
                ->where('created_at', '>=', $today)
                ->count(),
            'imports_this_week' => ActivityLog::where('action', 'gedcom_import')
                ->where('created_at', '>=', $thisWeek)
                ->count(),
            'imports_this_month' => ActivityLog::where('action', 'gedcom_import')
                ->where('created_at', '>=', $thisMonth)
                ->count(),
            'average_import_time' => $this->getAverageImportTime(),
        ];
    }

    /**
     * Get user engagement metrics
     */
    private function getUserEngagementMetrics(): array
    {
        $today = now()->startOfDay();
        $thisWeek = now()->startOfWeek();

        return [
            'active_users_today' => User::where('last_activity_at', '>=', $today)->count(),
            'active_users_this_week' => User::where('last_activity_at', '>=', $thisWeek)->count(),
            'new_users_today' => User::where('created_at', '>=', $today)->count(),
            'new_users_this_week' => User::where('created_at', '>=', $thisWeek)->count(),
            'average_session_duration' => $this->getAverageSessionDuration(),
        ];
    }

    /**
     * Get data growth metrics
     */
    private function getDataGrowthMetrics(): array
    {
        $lastWeek = now()->subWeek();
        $lastMonth = now()->subMonth();

        return [
            'individuals_growth_week' => Individual::where('created_at', '>=', $lastWeek)->count(),
            'individuals_growth_month' => Individual::where('created_at', '>=', $lastMonth)->count(),
            'trees_growth_week' => Tree::where('created_at', '>=', $lastWeek)->count(),
            'trees_growth_month' => Tree::where('created_at', '>=', $lastMonth)->count(),
        ];
    }

    /**
     * Record request metrics
     */
    public function recordRequest(float $responseTime, bool $isError = false): void
    {
        $currentMinute = now()->format('Y-m-d H:i');
        
        // Record response time
        $responseTimesKey = self::METRICS_PREFIX . 'response_times';
        $responseTimes = Cache::get($responseTimesKey, []);
        $responseTimes[] = $responseTime;
        
        // Keep only last 100 response times
        if (count($responseTimes) > 100) {
            $responseTimes = array_slice($responseTimes, -100);
        }
        
        Cache::put($responseTimesKey, $responseTimes, self::CACHE_TTL);
        
        // Record request count
        $requestsKey = self::METRICS_PREFIX . 'requests:' . $currentMinute;
        Cache::increment($requestsKey);
        Cache::expire($requestsKey, 120); // 2 minutes
        
        // Record error count
        if ($isError) {
            $errorsKey = self::METRICS_PREFIX . 'errors:' . $currentMinute;
            Cache::increment($errorsKey);
            Cache::expire($errorsKey, 120); // 2 minutes
        }
    }

    /**
     * Record database query
     */
    public function recordDatabaseQuery(float $queryTime): void
    {
        Cache::increment(self::METRICS_PREFIX . 'db_queries');
        
        // Record slow queries
        if ($queryTime > 1000) { // > 1 second
            $slowQueriesKey = self::METRICS_PREFIX . 'slow_queries';
            $slowQueries = Cache::get($slowQueriesKey, []);
            $slowQueries[] = [
                'time' => $queryTime,
                'timestamp' => now()->toISOString(),
            ];
            
            // Keep only last 50 slow queries
            if (count($slowQueries) > 50) {
                $slowQueries = array_slice($slowQueries, -50);
            }
            
            Cache::put($slowQueriesKey, $slowQueries, self::CACHE_TTL);
        }
    }

    /**
     * Record cache hit/miss
     */
    public function recordCacheAccess(bool $isHit): void
    {
        if ($isHit) {
            Cache::increment(self::METRICS_PREFIX . 'cache_hits');
        } else {
            Cache::increment(self::METRICS_PREFIX . 'cache_misses');
        }
    }

    /**
     * Get slow queries
     */
    private function getSlowQueries(): array
    {
        return Cache::get(self::METRICS_PREFIX . 'slow_queries', []);
    }

    /**
     * Get database connections
     */
    private function getDatabaseConnections(): int
    {
        // This would need to be implemented based on your database configuration
        return 0;
    }

    /**
     * Get Redis memory usage
     */
    private function getRedisMemoryUsage(): array
    {
        try {
            $info = Redis::info('memory');
            return [
                'used_memory_mb' => round($info['used_memory'] / 1024 / 1024, 2),
                'used_memory_peak_mb' => round($info['used_memory_peak'] / 1024 / 1024, 2),
            ];
        } catch (\Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }

    /**
     * Get Redis connected clients
     */
    private function getRedisConnectedClients(): int
    {
        try {
            $info = Redis::info('clients');
            return $info['connected_clients'] ?? 0;
        } catch (\Exception $e) {
            return 0;
        }
    }

    /**
     * Get Neo4j node count
     */
    private function getNeo4jNodeCount(): int
    {
        // This would need to be implemented based on your Neo4j client
        return 0;
    }

    /**
     * Get Neo4j relationship count
     */
    private function getNeo4jRelationshipCount(): int
    {
        // This would need to be implemented based on your Neo4j client
        return 0;
    }

    /**
     * Get MongoDB document count
     */
    private function getMongoDocumentCount(): int
    {
        // This would need to be implemented based on your MongoDB client
        return 0;
    }

    /**
     * Get MongoDB collection count
     */
    private function getMongoCollectionCount(): int
    {
        // This would need to be implemented based on your MongoDB client
        return 0;
    }

    /**
     * Parse memory limit string
     */
    private function parseMemoryLimit(string $limit): int
    {
        $value = (int) $limit;
        $unit = strtolower(substr($limit, -1));
        
        switch ($unit) {
            case 'g':
                return $value * 1024 * 1024 * 1024;
            case 'm':
                return $value * 1024 * 1024;
            case 'k':
                return $value * 1024;
            default:
                return $value;
        }
    }

    /**
     * Get requests per hour
     */
    private function getRequestsPerHour(): int
    {
        $currentHour = now()->format('Y-m-d H');
        $key = self::METRICS_PREFIX . 'requests:' . $currentHour;
        return Cache::get($key, 0);
    }

    /**
     * Get requests per day
     */
    private function getRequestsPerDay(): int
    {
        $currentDay = now()->format('Y-m-d');
        $key = self::METRICS_PREFIX . 'requests:' . $currentDay;
        return Cache::get($key, 0);
    }

    /**
     * Get average query time
     */
    private function getAverageQueryTime(): float
    {
        $slowQueries = $this->getSlowQueries();
        
        if (empty($slowQueries)) {
            return 0.0;
        }
        
        $times = array_column($slowQueries, 'time');
        return round(array_sum($times) / count($times), 2);
    }

    /**
     * Get average import time
     */
    private function getAverageImportTime(): float
    {
        // This would need to be implemented based on your import tracking
        return 0.0;
    }

    /**
     * Get average session duration
     */
    private function getAverageSessionDuration(): float
    {
        // This would need to be implemented based on your session tracking
        return 0.0;
    }
} 