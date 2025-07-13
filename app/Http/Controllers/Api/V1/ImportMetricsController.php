<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\ImportPerformanceTracker;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class ImportMetricsController extends Controller
{
    public function __construct(
        private ImportPerformanceTracker $performanceTracker
    ) {}

    /**
     * Get performance comparison data
     */
    public function comparison(): JsonResponse
    {
        $comparison = $this->performanceTracker->getPerformanceComparison();

        return response()->json([
            'success' => true,
            'data' => $comparison,
        ]);
    }

    /**
     * Get recent metrics for a specific time period
     */
    public function recent(Request $request): JsonResponse
    {
        $hours = $request->input('hours', 24);
        $hours = max(1, min(168, (int) $hours)); // Between 1 hour and 1 week

        $metrics = $this->performanceTracker->getRecentMetrics($hours);

        return response()->json([
            'success' => true,
            'data' => $metrics,
            'period_hours' => $hours,
        ]);
    }

    /**
     * Get method-specific metrics
     */
    public function method(Request $request): JsonResponse
    {
        $method = $request->input('method');
        $hours = $request->input('hours', 24);
        $hours = max(1, min(168, (int) $hours));

        if (! in_array($method, ['standard', 'optimized'], true)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid method. Must be "standard" or "optimized".',
            ], 400);
        }

        $metrics = $this->performanceTracker->getMethodMetrics($method, $hours);

        return response()->json([
            'success' => true,
            'data' => $metrics,
            'method' => $method,
            'period_hours' => $hours,
        ]);
    }

    /**
     * Get aggregated metrics for Grafana
     */
    public function aggregated(): JsonResponse
    {
        $comparison = $this->performanceTracker->getPerformanceComparison();

        // Format for Grafana time series
        $timeSeriesData = [];
        $now = now();

        foreach (['standard', 'optimized'] as $method) {
            $metrics = $comparison[$method] ?? [];

            if (! empty($metrics)) {
                $timeSeriesData[] = [
                    'target' => "{$method}_avg_duration",
                    'datapoints' => [[$metrics['avg_duration'] ?? 0, $now->timestamp * 1000]],
                ];

                $timeSeriesData[] = [
                    'target' => "{$method}_avg_throughput",
                    'datapoints' => [[$metrics['avg_records_per_second'] ?? 0, $now->timestamp * 1000]],
                ];

                $timeSeriesData[] = [
                    'target' => "{$method}_success_rate",
                    'datapoints' => [[$comparison['comparison']["success_rate_{$method}"] ?? 0, $now->timestamp * 1000]],
                ];

                $timeSeriesData[] = [
                    'target' => "{$method}_total_imports",
                    'datapoints' => [[$metrics['total_imports'] ?? 0, $now->timestamp * 1000]],
                ];
            }
        }

        return response()->json($timeSeriesData);
    }

    /**
     * Get performance summary for dashboard
     */
    public function summary(): JsonResponse
    {
        $comparison = $this->performanceTracker->getPerformanceComparison();

        $summary = [
            'total_imports' => [
                'standard' => $comparison['standard']['total_imports'] ?? 0,
                'optimized' => $comparison['optimized']['total_imports'] ?? 0,
            ],
            'success_rates' => [
                'standard' => $comparison['comparison']['success_rate_standard'] ?? 0,
                'optimized' => $comparison['comparison']['success_rate_optimized'] ?? 0,
            ],
            'performance_improvements' => [
                'duration' => $comparison['comparison']['duration_improvement'] ?? 0,
                'throughput' => $comparison['comparison']['throughput_improvement'] ?? 0,
            ],
            'average_metrics' => [
                'standard' => [
                    'duration' => $comparison['standard']['avg_duration'] ?? 0,
                    'throughput' => $comparison['standard']['avg_records_per_second'] ?? 0,
                    'memory' => $comparison['standard']['avg_memory_usage'] ?? 0,
                ],
                'optimized' => [
                    'duration' => $comparison['optimized']['avg_duration'] ?? 0,
                    'throughput' => $comparison['optimized']['avg_records_per_second'] ?? 0,
                    'memory' => $comparison['optimized']['avg_memory_usage'] ?? 0,
                ],
            ],
        ];

        return response()->json([
            'success' => true,
            'data' => $summary,
        ]);
    }
}
