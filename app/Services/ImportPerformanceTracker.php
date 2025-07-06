<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\ImportProgress;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;

/**
 * Service to track and compare import performance metrics
 */
final class ImportPerformanceTracker
{
    private const METRICS_CACHE_TTL = 86400; // 24 hours
    private const METRICS_KEY_PREFIX = 'import_metrics:';
    private const COMPARISON_KEY = 'import_method_comparison';

    /**
     * Track import performance metrics
     */
    public function trackImportMetrics(
        string $importMethod,
        int $treeId,
        int $userId,
        array $importResults,
        float $duration,
        int $fileSize,
        int $totalRecords
    ): void {
        $metrics = [
            'import_method' => $importMethod,
            'tree_id' => $treeId,
            'user_id' => $userId,
            'duration_seconds' => $duration,
            'file_size_bytes' => $fileSize,
            'total_records' => $totalRecords,
            'records_per_second' => $totalRecords > 0 ? round($totalRecords / $duration, 2) : 0,
            'bytes_per_second' => $fileSize > 0 ? round($fileSize / $duration, 2) : 0,
            'individuals_count' => $this->extractIndividualsCount($importResults, $importMethod),
            'families_count' => $this->extractFamiliesCount($importResults, $importMethod),
            'sources_count' => $this->extractSourcesCount($importResults, $importMethod),
            'memory_used_mb' => $importResults['memory_used_mb'] ?? 0,
            'timestamp' => now()->toISOString(),
            'success' => true,
        ];

        // Store individual import metrics
        $this->storeImportMetrics($metrics);

        // Update aggregated metrics
        $this->updateAggregatedMetrics($metrics);

        // Log metrics for monitoring
        Log::info('Import performance metrics tracked', $metrics);
    }

    /**
     * Track import failure
     */
    public function trackImportFailure(
        string $importMethod,
        int $treeId,
        int $userId,
        string $errorMessage,
        float $duration = 0,
        int $fileSize = 0
    ): void {
        $metrics = [
            'import_method' => $importMethod,
            'tree_id' => $treeId,
            'user_id' => $userId,
            'duration_seconds' => $duration,
            'file_size_bytes' => $fileSize,
            'total_records' => 0,
            'records_per_second' => 0,
            'bytes_per_second' => $fileSize > 0 ? round($fileSize / $duration, 2) : 0,
            'individuals_count' => 0,
            'families_count' => 0,
            'sources_count' => 0,
            'memory_used_mb' => 0,
            'timestamp' => now()->toISOString(),
            'success' => false,
            'error_message' => $errorMessage,
        ];

        // Store individual import metrics
        $this->storeImportMetrics($metrics);

        // Update aggregated metrics
        $this->updateAggregatedMetrics($metrics);

        // Log failure metrics
        Log::error('Import failure metrics tracked', $metrics);
    }

    /**
     * Get performance comparison data
     */
    public function getPerformanceComparison(): array
    {
        $cacheKey = self::COMPARISON_KEY;
        
        return Cache::remember($cacheKey, self::METRICS_CACHE_TTL, function () {
            return $this->calculatePerformanceComparison();
        });
    }

    /**
     * Get recent import metrics for Grafana
     */
    public function getRecentMetrics(int $hours = 24): array
    {
        $cacheKey = "recent_metrics:{$hours}h";
        
        return Cache::remember($cacheKey, 300, function () use ($hours) { // 5 minutes cache
            return $this->fetchRecentMetrics($hours);
        });
    }

    /**
     * Get method-specific metrics
     */
    public function getMethodMetrics(string $method, int $hours = 24): array
    {
        $cacheKey = "method_metrics:{$method}:{$hours}h";
        
        return Cache::remember($cacheKey, 300, function () use ($method, $hours) {
            return $this->fetchMethodMetrics($method, $hours);
        });
    }

    /**
     * Store individual import metrics
     */
    private function storeImportMetrics(array $metrics): void
    {
        $importId = uniqid('import_', true);
        $key = self::METRICS_KEY_PREFIX . $importId;
        
        // Store in Redis for real-time access
        Redis::setex($key, self::METRICS_CACHE_TTL, json_encode($metrics));
        
        // Also store in cache for aggregated queries
        $method = $metrics['import_method'];
        $methodKey = "import_methods:{$method}";
        
        $methodMetrics = Cache::get($methodKey, []);
        $methodMetrics[] = $metrics;
        
        // Keep only last 100 imports per method
        if (count($methodMetrics) > 100) {
            $methodMetrics = array_slice($methodMetrics, -100);
        }
        
        Cache::put($methodKey, $methodMetrics, self::METRICS_CACHE_TTL);
    }

    /**
     * Update aggregated metrics
     */
    private function updateAggregatedMetrics(array $metrics): void
    {
        $method = $metrics['import_method'];
        $aggregatedKey = "aggregated:{$method}";
        
        $aggregated = Cache::get($aggregatedKey, [
            'total_imports' => 0,
            'successful_imports' => 0,
            'failed_imports' => 0,
            'total_duration' => 0,
            'total_records' => 0,
            'total_file_size' => 0,
            'avg_duration' => 0,
            'avg_records_per_second' => 0,
            'avg_bytes_per_second' => 0,
            'avg_memory_usage' => 0,
        ]);

        $aggregated['total_imports']++;
        
        if ($metrics['success']) {
            $aggregated['successful_imports']++;
            $aggregated['total_duration'] += $metrics['duration_seconds'];
            $aggregated['total_records'] += $metrics['total_records'];
            $aggregated['total_file_size'] += $metrics['file_size_bytes'];
            $aggregated['avg_memory_usage'] = $this->calculateRunningAverage(
                $aggregated['avg_memory_usage'],
                $metrics['memory_used_mb'],
                $aggregated['successful_imports']
            );
        } else {
            $aggregated['failed_imports']++;
        }

        // Calculate averages
        if ($aggregated['successful_imports'] > 0) {
            $aggregated['avg_duration'] = round($aggregated['total_duration'] / $aggregated['successful_imports'], 2);
            $aggregated['avg_records_per_second'] = round($aggregated['total_records'] / $aggregated['total_duration'], 2);
            $aggregated['avg_bytes_per_second'] = round($aggregated['total_file_size'] / $aggregated['total_duration'], 2);
        }

        Cache::put($aggregatedKey, $aggregated, self::METRICS_CACHE_TTL);
    }

    /**
     * Calculate performance comparison
     */
    private function calculatePerformanceComparison(): array
    {
        $standardMetrics = Cache::get('aggregated:standard', []);
        $optimizedMetrics = Cache::get('aggregated:optimized', []);

        $comparison = [
            'standard' => $standardMetrics,
            'optimized' => $optimizedMetrics,
            'comparison' => [],
        ];

        if (!empty($standardMetrics) && !empty($optimizedMetrics)) {
            $comparison['comparison'] = [
                'duration_improvement' => $this->calculateImprovement(
                    $standardMetrics['avg_duration'] ?? 0,
                    $optimizedMetrics['avg_duration'] ?? 0
                ),
                'throughput_improvement' => $this->calculateImprovement(
                    $optimizedMetrics['avg_records_per_second'] ?? 0,
                    $standardMetrics['avg_records_per_second'] ?? 0
                ),
                'success_rate_standard' => $this->calculateSuccessRate($standardMetrics),
                'success_rate_optimized' => $this->calculateSuccessRate($optimizedMetrics),
            ];
        }

        return $comparison;
    }

    /**
     * Fetch recent metrics
     */
    private function fetchRecentMetrics(int $hours): array
    {
        $cutoffTime = now()->subHours($hours);
        $recentMetrics = [];

        foreach (['standard', 'optimized'] as $method) {
            $methodMetrics = Cache::get("import_methods:{$method}", []);
            
            $recentMetrics[$method] = array_filter($methodMetrics, function ($metric) use ($cutoffTime) {
                return strtotime($metric['timestamp']) >= $cutoffTime->timestamp;
            });
        }

        return $recentMetrics;
    }

    /**
     * Fetch method-specific metrics
     */
    private function fetchMethodMetrics(string $method, int $hours): array
    {
        $cutoffTime = now()->subHours($hours);
        $methodMetrics = Cache::get("import_methods:{$method}", []);
        
        return array_filter($methodMetrics, function ($metric) use ($cutoffTime) {
            return strtotime($metric['timestamp']) >= $cutoffTime->timestamp;
        });
    }

    /**
     * Extract individuals count from import results
     */
    private function extractIndividualsCount(array $importResults, string $importMethod): int
    {
        if ($importMethod === 'optimized') {
            return $importResults['results']['individuals'] ?? 0;
        } else {
            return $importResults['postgresql']['individuals'] ?? 0;
        }
    }

    /**
     * Extract families count from import results
     */
    private function extractFamiliesCount(array $importResults, string $importMethod): int
    {
        if ($importMethod === 'optimized') {
            return $importResults['results']['families'] ?? 0;
        } else {
            return $importResults['postgresql']['families'] ?? 0;
        }
    }

    /**
     * Extract sources count from import results
     */
    private function extractSourcesCount(array $importResults, string $importMethod): int
    {
        if ($importMethod === 'optimized') {
            return $importResults['results']['sources'] ?? 0;
        } else {
            return $importResults['postgresql']['sources'] ?? 0;
        }
    }

    /**
     * Calculate running average
     */
    private function calculateRunningAverage(float $currentAvg, float $newValue, int $count): float
    {
        return round(($currentAvg * ($count - 1) + $newValue) / $count, 2);
    }

    /**
     * Calculate improvement percentage
     */
    private function calculateImprovement(float $baseline, float $newValue): float
    {
        if ($baseline === 0) {
            return $newValue > 0 ? 100 : 0;
        }
        
        return round((($newValue - $baseline) / $baseline) * 100, 2);
    }

    /**
     * Calculate success rate
     */
    private function calculateSuccessRate(array $metrics): float
    {
        $total = $metrics['total_imports'] ?? 0;
        $successful = $metrics['successful_imports'] ?? 0;
        
        return $total > 0 ? round(($successful / $total) * 100, 2) : 0;
    }
} 