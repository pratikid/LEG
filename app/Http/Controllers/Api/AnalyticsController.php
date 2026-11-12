<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Analytics\DemographicsService;
use App\Services\Analytics\GeographicService;
use App\Services\Analytics\AnalyticsETLService;
use App\Services\Analytics\Neo4jAnalyticsService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Exception;

/**
 * Analytics API Controller
 * Provides REST endpoints for genealogy analytics and insights
 */
class AnalyticsController extends Controller
{
    private DemographicsService $demographics;
    private GeographicService $geographic;
    private AnalyticsETLService $etl;
    private Neo4jAnalyticsService $graphAnalytics;

    public function __construct(
        DemographicsService $demographics,
        GeographicService $geographic,
        AnalyticsETLService $etl,
        Neo4jAnalyticsService $graphAnalytics
    ) {
        $this->demographics = $demographics;
        $this->geographic = $geographic;
        $this->etl = $etl;
        $this->graphAnalytics = $graphAnalytics;
    }

    /**
     * Get comprehensive analytics overview for a tree
     * GET /api/v1/analytics/trees/{treeId}/overview
     */
    public function getTreeOverview(int $treeId): JsonResponse
    {
        try {
            // Check if user has access to this tree
            if (!$this->canAccessTree($treeId)) {
                return response()->json(['error' => 'Unauthorized access to tree'], 403);
            }

            $cacheKey = "analytics:tree_overview:{$treeId}";
            
            $overview = Cache::remember($cacheKey, 1800, function () use ($treeId) { // 30 minutes cache
                return [
                    'tree_id' => $treeId,
                    'demographics' => $this->demographics->getTreeDemographics($treeId),
                    'geographic' => $this->geographic->getTreeGeographics($treeId),
                    'insights' => [
                        'demographic_insights' => $this->demographics->getDemographicInsights($treeId),
                        'geographic_insights' => $this->geographic->getGeographicInsights($treeId)
                    ],
                    'generated_at' => now()->toISOString()
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $overview,
                'meta' => [
                    'cache_ttl_minutes' => 30,
                    'last_updated' => $overview['generated_at']
                ]
            ]);

        } catch (Exception $e) {
            Log::error('Failed to generate tree analytics overview', [
                'tree_id' => $treeId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Failed to generate analytics overview',
                'message' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * Get demographics analytics for a tree
     * GET /api/v1/analytics/trees/{treeId}/demographics
     */
    public function getDemographics(Request $request, int $treeId): JsonResponse
    {
        try {
            if (!$this->canAccessTree($treeId)) {
                return response()->json(['error' => 'Unauthorized access to tree'], 403);
            }

            $section = $request->query('section', 'all');
            $compareWith = $request->query('compare_with');

            if ($compareWith && !$this->canAccessTree((int) $compareWith)) {
                return response()->json(['error' => 'Unauthorized access to comparison tree'], 403);
            }

            $data = match ($section) {
                'population' => ['population_summary' => $this->demographics->getPopulationSummary($treeId)],
                'age' => ['age_demographics' => $this->demographics->getAgeDemographics($treeId)],
                'gender' => ['gender_distribution' => $this->demographics->getGenderDistribution($treeId)],
                'lifespan' => ['lifespan_analysis' => $this->demographics->getLifespanAnalysis($treeId)],
                'births' => ['birth_trends' => $this->demographics->getBirthTrends($treeId)],
                'generations' => ['generation_statistics' => $this->demographics->getGenerationStatistics($treeId)],
                'insights' => ['insights' => $this->demographics->getDemographicInsights($treeId)],
                default => $this->demographics->getTreeDemographics($treeId)
            };

            if ($compareWith) {
                $data['comparison'] = $this->demographics->compareTrees($treeId, (int) $compareWith);
            }

            return response()->json([
                'success' => true,
                'data' => $data,
                'meta' => [
                    'tree_id' => $treeId,
                    'section' => $section,
                    'comparison_tree_id' => $compareWith
                ]
            ]);

        } catch (Exception $e) {
            Log::error('Failed to generate demographics analytics', [
                'tree_id' => $treeId,
                'section' => $section ?? null,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Failed to generate demographics analytics'
            ], 500);
        }
    }

    /**
     * Get geographic analytics for a tree
     * GET /api/v1/analytics/trees/{treeId}/geographic
     */
    public function getGeographic(Request $request, int $treeId): JsonResponse
    {
        try {
            if (!$this->canAccessTree($treeId)) {
                return response()->json(['error' => 'Unauthorized access to tree'], 403);
            }

            $section = $request->query('section', 'all');
            $compareWith = $request->query('compare_with');

            if ($compareWith && !$this->canAccessTree((int) $compareWith)) {
                return response()->json(['error' => 'Unauthorized access to comparison tree'], 403);
            }

            $data = match ($section) {
                'locations' => ['location_summary' => $this->geographic->getLocationSummary($treeId)],
                'migration' => ['migration_patterns' => $this->geographic->getMigrationPatterns($treeId)],
                'distribution' => ['geographic_distribution' => $this->geographic->getGeographicDistribution($treeId)],
                'trends' => ['birth_location_trends' => $this->geographic->getBirthLocationTrends($treeId)],
                'settlements' => ['settlement_analysis' => $this->geographic->getSettlementAnalysis($treeId)],
                'distances' => ['distance_metrics' => $this->geographic->getDistanceMetrics($treeId)],
                'insights' => ['insights' => $this->geographic->getGeographicInsights($treeId)],
                default => $this->geographic->getTreeGeographics($treeId)
            };

            if ($compareWith) {
                $data['comparison'] = $this->geographic->compareTrees($treeId, (int) $compareWith);
            }

            return response()->json([
                'success' => true,
                'data' => $data,
                'meta' => [
                    'tree_id' => $treeId,
                    'section' => $section,
                    'comparison_tree_id' => $compareWith
                ]
            ]);

        } catch (Exception $e) {
            Log::error('Failed to generate geographic analytics', [
                'tree_id' => $treeId,
                'section' => $section ?? null,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Failed to generate geographic analytics'
            ], 500);
        }
    }

    /**
     * Get analytics insights and recommendations for a tree
     * GET /api/v1/analytics/trees/{treeId}/insights
     */
    public function getInsights(int $treeId): JsonResponse
    {
        try {
            if (!$this->canAccessTree($treeId)) {
                return response()->json(['error' => 'Unauthorized access to tree'], 403);
            }

            $insights = [
                'demographic_insights' => $this->demographics->getDemographicInsights($treeId),
                'geographic_insights' => $this->geographic->getGeographicInsights($treeId)
            ];

            // Combine all insights and recommendations
            $allInsights = array_merge(
                $insights['demographic_insights']['insights'],
                $insights['geographic_insights']['insights']
            );

            $allRecommendations = array_merge(
                $insights['demographic_insights']['recommendations'],
                $insights['geographic_insights']['recommendations']
            );

            return response()->json([
                'success' => true,
                'data' => [
                    'tree_id' => $treeId,
                    'insights' => $allInsights,
                    'recommendations' => $allRecommendations,
                    'detailed_insights' => $insights,
                    'insight_summary' => [
                        'total_insights' => count($allInsights),
                        'high_priority' => count(array_filter($allInsights, fn($i) => ($i['priority'] ?? '') === 'high')),
                        'medium_priority' => count(array_filter($allInsights, fn($i) => ($i['priority'] ?? '') === 'medium')),
                        'low_priority' => count(array_filter($allInsights, fn($i) => ($i['priority'] ?? '') === 'low')),
                    ]
                ]
            ]);

        } catch (Exception $e) {
            Log::error('Failed to generate analytics insights', [
                'tree_id' => $treeId,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Failed to generate insights'
            ], 500);
        }
    }

    /**
     * Export analytics data in various formats
     * GET /api/v1/analytics/trees/{treeId}/export
     */
    public function exportAnalytics(Request $request, int $treeId): JsonResponse
    {
        try {
            if (!$this->canAccessTree($treeId)) {
                return response()->json(['error' => 'Unauthorized access to tree'], 403);
            }

            $format = $request->query('format', 'json');
            $sections = $request->query('sections', 'all');

            // Check if user has export permissions (premium feature)
            if (!$this->canExportAnalytics()) {
                return response()->json([
                    'error' => 'Export feature requires premium subscription',
                    'upgrade_url' => route('subscription.upgrade')
                ], 402); // Payment Required
            }

            // Generate export data
            $exportData = $this->generateExportData($treeId, $sections);

            // Queue export job for large datasets
            if ($this->isLargeExport($exportData)) {
                $jobId = $this->queueExportJob($treeId, $format, $sections);
                
                return response()->json([
                    'success' => true,
                    'message' => 'Export queued for processing',
                    'job_id' => $jobId,
                    'estimated_completion' => now()->addMinutes(5)->toISOString(),
                    'status_url' => route('api.analytics.export.status', ['jobId' => $jobId])
                ]);
            }

            // Return immediate export for small datasets
            return response()->json([
                'success' => true,
                'data' => $exportData,
                'meta' => [
                    'tree_id' => $treeId,
                    'format' => $format,
                    'sections' => $sections,
                    'exported_at' => now()->toISOString(),
                    'record_count' => $this->countExportRecords($exportData)
                ]
            ]);

        } catch (Exception $e) {
            Log::error('Failed to export analytics data', [
                'tree_id' => $treeId,
                'format' => $format ?? 'json',
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Failed to export analytics data'
            ], 500);
        }
    }

    /**
     * Get export job status
     * GET /api/v1/analytics/export/{jobId}/status
     */
    public function getExportStatus(string $jobId): JsonResponse
    {
        try {
            $status = Cache::get("export:job:{$jobId}");
            
            if (!$status) {
                return response()->json(['error' => 'Export job not found'], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $status
            ]);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Failed to get export status'
            ], 500);
        }
    }

    /**
     * Trigger ETL process for a tree
     * POST /api/v1/analytics/trees/{treeId}/etl
     */
    public function triggerETL(Request $request, int $treeId): JsonResponse
    {
        try {
            if (!$this->canAccessTree($treeId)) {
                return response()->json(['error' => 'Unauthorized access to tree'], 403);
            }

            // Check if user has ETL trigger permissions (admin feature)
            if (!$this->canTriggerETL()) {
                return response()->json(['error' => 'Insufficient permissions'], 403);
            }

            $force = $request->boolean('force', false);
            
            // Check if ETL is already running
            $lockKey = "etl:running:tree:{$treeId}";
            if (Cache::has($lockKey) && !$force) {
                return response()->json([
                    'success' => false,
                    'error' => 'ETL process already running for this tree',
                    'message' => 'Use force=true to override the lock'
                ], 409);
            }

            // Set lock and trigger ETL
            Cache::put($lockKey, true, 3600); // 1 hour lock

            try {
                $tree = \App\Models\Tree::findOrFail($treeId);
                $results = $this->etl->processTree($tree);
                
                Cache::forget($lockKey);
                
                return response()->json([
                    'success' => true,
                    'message' => 'ETL process completed successfully',
                    'data' => $results,
                    'completed_at' => now()->toISOString()
                ]);

            } catch (Exception $e) {
                Cache::forget($lockKey);
                throw $e;
            }

        } catch (Exception $e) {
            Log::error('Failed to trigger ETL process', [
                'tree_id' => $treeId,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Failed to trigger ETL process',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get analytics API usage metrics
     * GET /api/v1/analytics/usage
     */
    public function getUsageMetrics(Request $request): JsonResponse
    {
        try {
            // Check if user has usage monitoring permissions
            if (!Gate::allows('view-analytics-usage')) {
                return response()->json(['error' => 'Insufficient permissions'], 403);
            }

            $period = $request->query('period', '24h');
            $userId = $request->user()->id;

            $metrics = [
                'period' => $period,
                'user_id' => $userId,
                'api_calls' => $this->getAPICallCount($userId, $period),
                'data_exported_mb' => $this->getDataExportedSize($userId, $period),
                'cache_hit_rate' => $this->getCacheHitRate($userId, $period),
                'average_response_time_ms' => $this->getAverageResponseTime($userId, $period),
                'quota_limits' => $this->getQuotaLimits($userId),
                'current_usage_percentage' => $this->getCurrentUsagePercentage($userId)
            ];

            return response()->json([
                'success' => true,
                'data' => $metrics
            ]);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Failed to get usage metrics'
            ], 500);
        }
    }

    // Helper methods

    private function canAccessTree(int $treeId): bool
    {
        // Check if user has access to this tree
        $user = request()->user();
        if (!$user) return false;

        return $user->trees()->where('id', $treeId)->exists() || 
               $user->hasRole('admin');
    }

    private function canExportAnalytics(): bool
    {
        $user = request()->user();
        return $user && ($user->hasSubscription('plus') || $user->hasSubscription('pro') || $user->hasRole('admin'));
    }

    private function canTriggerETL(): bool
    {
        $user = request()->user();
        return $user && ($user->hasRole('admin') || $user->hasPermission('trigger-etl'));
    }

    private function generateExportData(int $treeId, string $sections): array
    {
        $data = ['tree_id' => $treeId];

        if ($sections === 'all' || str_contains($sections, 'demographics')) {
            $data['demographics'] = $this->demographics->getTreeDemographics($treeId);
        }

        if ($sections === 'all' || str_contains($sections, 'geographic')) {
            $data['geographic'] = $this->geographic->getTreeGeographics($treeId);
        }

        if ($sections === 'all' || str_contains($sections, 'insights')) {
            $data['insights'] = [
                'demographic_insights' => $this->demographics->getDemographicInsights($treeId),
                'geographic_insights' => $this->geographic->getGeographicInsights($treeId)
            ];
        }

        return $data;
    }

    private function isLargeExport(array $data): bool
    {
        // Consider export large if JSON size > 1MB
        return strlen(json_encode($data)) > 1024 * 1024;
    }

    private function queueExportJob(int $treeId, string $format, string $sections): string
    {
        $jobId = uniqid('export_', true);
        
        // Queue background job
        \Illuminate\Support\Facades\Queue::push(
            \App\Jobs\ExportAnalyticsJob::class,
            ['tree_id' => $treeId, 'format' => $format, 'sections' => $sections, 'job_id' => $jobId]
        );

        return $jobId;
    }

    private function countExportRecords(array $data): int
    {
        // Simple record count estimation
        return array_sum(array_map(function ($section) {
            if (is_array($section)) {
                return count($section, COUNT_RECURSIVE);
            }
            return 1;
        }, $data));
    }

    private function getAPICallCount(int $userId, string $period): int
    {
        // This would implement actual usage tracking
        return rand(50, 200); // Placeholder
    }

    private function getDataExportedSize(int $userId, string $period): float
    {
        // This would implement actual data size tracking
        return round(rand(10, 500) / 10, 1); // Placeholder MB
    }

    private function getCacheHitRate(int $userId, string $period): float
    {
        // This would implement actual cache hit rate tracking
        return round(rand(70, 95), 1); // Placeholder percentage
    }

    private function getAverageResponseTime(int $userId, string $period): int
    {
        // This would implement actual response time tracking
        return rand(150, 800); // Placeholder milliseconds
    }

    private function getQuotaLimits(int $userId): array
    {
        $user = \App\Models\User::find($userId);
        
        // Return quota limits based on subscription
        return match ($user?->subscription_type) {
            'pro' => ['api_calls' => 10000, 'exports_mb' => 1000],
            'plus' => ['api_calls' => 1000, 'exports_mb' => 100],
            default => ['api_calls' => 100, 'exports_mb' => 10]
        };
    }

    private function getCurrentUsagePercentage(int $userId): float
    {
        // Calculate current usage as percentage of quota
        $limits = $this->getQuotaLimits($userId);
        $currentCalls = $this->getAPICallCount($userId, '30d');
        
        return round(($currentCalls / $limits['api_calls']) * 100, 1);
    }

    /**
     * Get Neo4j graph analytics for a tree
     * GET /api/v1/analytics/trees/{treeId}/graph
     */
    public function getGraphAnalytics(Request $request, int $treeId): JsonResponse
    {
        try {
            if (!$this->canAccessTree($treeId)) {
                return response()->json(['error' => 'Unauthorized access to tree'], 403);
            }

            $section = $request->query('section', 'all');
            $cacheKey = "analytics:graph:{$treeId}:{$section}";

            $data = Cache::remember($cacheKey, 3600, function () use ($treeId, $section) { // 1 hour cache
                return match ($section) {
                    'network' => ['network_metrics' => $this->graphAnalytics->getNetworkMetrics($treeId)],
                    'centrality' => ['centrality_analysis' => $this->graphAnalytics->getCentralityAnalysis($treeId)],
                    'communities' => ['communities' => $this->graphAnalytics->getCommunityAnalysis($treeId)],
                    'relationships' => ['relationship_patterns' => $this->graphAnalytics->getRelationshipPatterns($treeId)],
                    'ancestry' => ['ancestry_paths' => $this->graphAnalytics->getAncestryPaths($treeId)],
                    'clusters' => ['family_clusters' => $this->graphAnalytics->getFamilyClusters($treeId)],
                    default => [
                        'network_metrics' => $this->graphAnalytics->getNetworkMetrics($treeId),
                        'centrality_analysis' => $this->graphAnalytics->getCentralityAnalysis($treeId),
                        'communities' => $this->graphAnalytics->getCommunityAnalysis($treeId),
                        'relationship_patterns' => $this->graphAnalytics->getRelationshipPatterns($treeId)
                    ]
                };
            });

            return response()->json([
                'success' => true,
                'data' => $data,
                'meta' => [
                    'tree_id' => $treeId,
                    'section' => $section,
                    'cache_ttl_minutes' => 60
                ]
            ]);

        } catch (Exception $e) {
            Log::error('Failed to generate graph analytics', [
                'tree_id' => $treeId,
                'section' => $section ?? null,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Failed to generate graph analytics'
            ], 500);
        }
    }

    /**
     * Get relationship strength analysis between two individuals
     * GET /api/v1/analytics/trees/{treeId}/relationship-strength
     */
    public function getRelationshipStrength(Request $request, int $treeId): JsonResponse
    {
        try {
            if (!$this->canAccessTree($treeId)) {
                return response()->json(['error' => 'Unauthorized access to tree'], 403);
            }

            $request->validate([
                'individual1_id' => 'required|integer',
                'individual2_id' => 'required|integer'
            ]);

            $individual1Id = (int) $request->query('individual1_id');
            $individual2Id = (int) $request->query('individual2_id');

            $strength = $this->graphAnalytics->getRelationshipStrength($treeId, $individual1Id, $individual2Id);

            return response()->json([
                'success' => true,
                'data' => $strength,
                'meta' => [
                    'tree_id' => $treeId,
                    'individual1_id' => $individual1Id,
                    'individual2_id' => $individual2Id
                ]
            ]);

        } catch (Exception $e) {
            Log::error('Failed to calculate relationship strength', [
                'tree_id' => $treeId,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Failed to calculate relationship strength'
            ], 500);
        }
    }

    /**
     * Find paths between two individuals
     * GET /api/v1/analytics/trees/{treeId}/paths
     */
    public function findPaths(Request $request, int $treeId): JsonResponse
    {
        try {
            if (!$this->canAccessTree($treeId)) {
                return response()->json(['error' => 'Unauthorized access to tree'], 403);
            }

            $request->validate([
                'from_individual_id' => 'required|integer',
                'to_individual_id' => 'required|integer',
                'max_depth' => 'nullable|integer|min:1|max:10',
                'relationship_types' => 'nullable|string'
            ]);

            $fromId = (int) $request->query('from_individual_id');
            $toId = (int) $request->query('to_individual_id');
            $maxDepth = $request->query('max_depth', 6);
            $relationshipTypes = $request->query('relationship_types');

            $paths = $this->graphAnalytics->findPaths(
                $treeId, 
                $fromId, 
                $toId, 
                (int) $maxDepth, 
                $relationshipTypes ? explode(',', $relationshipTypes) : null
            );

            return response()->json([
                'success' => true,
                'data' => $paths,
                'meta' => [
                    'tree_id' => $treeId,
                    'from_individual_id' => $fromId,
                    'to_individual_id' => $toId,
                    'max_depth' => $maxDepth,
                    'relationship_types' => $relationshipTypes
                ]
            ]);

        } catch (Exception $e) {
            Log::error('Failed to find paths between individuals', [
                'tree_id' => $treeId,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Failed to find paths between individuals'
            ], 500);
        }
    }

    /**
     * Find most recent common ancestor (MRCA) for individuals
     * GET /api/v1/analytics/trees/{treeId}/mrca
     */
    public function findMRCA(Request $request, int $treeId): JsonResponse
    {
        try {
            if (!$this->canAccessTree($treeId)) {
                return response()->json(['error' => 'Unauthorized access to tree'], 403);
            }

            $request->validate([
                'individual_ids' => 'required|string'
            ]);

            $individualIds = array_map('intval', explode(',', $request->query('individual_ids')));

            if (count($individualIds) < 2) {
                return response()->json([
                    'success' => false,
                    'error' => 'At least 2 individuals are required'
                ], 400);
            }

            $mrca = $this->graphAnalytics->findMRCA($treeId, $individualIds);

            return response()->json([
                'success' => true,
                'data' => $mrca,
                'meta' => [
                    'tree_id' => $treeId,
                    'individual_ids' => $individualIds
                ]
            ]);

        } catch (Exception $e) {
            Log::error('Failed to find MRCA', [
                'tree_id' => $treeId,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Failed to find most recent common ancestor'
            ], 500);
        }
    }

    /**
     * Get influential individuals in the family network
     * GET /api/v1/analytics/trees/{treeId}/influential
     */
    public function getInfluentialIndividuals(Request $request, int $treeId): JsonResponse
    {
        try {
            if (!$this->canAccessTree($treeId)) {
                return response()->json(['error' => 'Unauthorized access to tree'], 403);
            }

            $metric = $request->query('metric', 'pagerank');
            $limit = (int) $request->query('limit', 20);

            $influential = match ($metric) {
                'degree' => $this->graphAnalytics->getTopIndividualsByDegree($treeId, $limit),
                'betweenness' => $this->graphAnalytics->getTopIndividualsByBetweenness($treeId, $limit),
                'closeness' => $this->graphAnalytics->getTopIndividualsByCloseness($treeId, $limit),
                'pagerank' => $this->graphAnalytics->getTopIndividualsByPageRank($treeId, $limit),
                default => $this->graphAnalytics->getTopIndividualsByPageRank($treeId, $limit)
            };

            return response()->json([
                'success' => true,
                'data' => $influential,
                'meta' => [
                    'tree_id' => $treeId,
                    'metric' => $metric,
                    'limit' => $limit
                ]
            ]);

        } catch (Exception $e) {
            Log::error('Failed to get influential individuals', [
                'tree_id' => $treeId,
                'metric' => $metric ?? 'pagerank',
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Failed to get influential individuals'
            ], 500);
        }
    }

    /**
     * Trigger graph analytics computation and caching
     * POST /api/v1/analytics/trees/{treeId}/graph/compute
     */
    public function computeGraphAnalytics(Request $request, int $treeId): JsonResponse
    {
        try {
            if (!$this->canAccessTree($treeId)) {
                return response()->json(['error' => 'Unauthorized access to tree'], 403);
            }

            // Check if user has compute permissions (premium feature)
            if (!$this->canComputeAdvancedAnalytics()) {
                return response()->json([
                    'error' => 'Advanced graph analytics require premium subscription',
                    'upgrade_url' => route('subscription.upgrade')
                ], 402);
            }

            $forceRecompute = $request->boolean('force', false);
            $sections = $request->input('sections', ['centrality', 'communities', 'network']);

            // Check if computation is already running
            $lockKey = "graph:computing:tree:{$treeId}";
            if (Cache::has($lockKey) && !$forceRecompute) {
                return response()->json([
                    'success' => false,
                    'error' => 'Graph analytics computation already in progress',
                    'message' => 'Use force=true to override'
                ], 409);
            }

            // Set computation lock
            Cache::put($lockKey, true, 1800); // 30 minutes lock

            try {
                $results = $this->graphAnalytics->computeAndCacheAnalytics($treeId, $sections);
                Cache::forget($lockKey);

                return response()->json([
                    'success' => true,
                    'message' => 'Graph analytics computation completed',
                    'data' => $results,
                    'computed_at' => now()->toISOString()
                ]);

            } catch (Exception $e) {
                Cache::forget($lockKey);
                throw $e;
            }

        } catch (Exception $e) {
            Log::error('Failed to compute graph analytics', [
                'tree_id' => $treeId,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Failed to compute graph analytics',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the tree overview to include graph analytics
     */
    protected function updateTreeOverviewWithGraph(int $treeId): array
    {
        $cacheKey = "analytics:tree_overview_with_graph:{$treeId}";
        
        return Cache::remember($cacheKey, 1800, function () use ($treeId) { // 30 minutes cache
            return [
                'tree_id' => $treeId,
                'demographics' => $this->demographics->getTreeDemographics($treeId),
                'geographic' => $this->geographic->getTreeGeographics($treeId),
                'graph' => [
                    'network_metrics' => $this->graphAnalytics->getNetworkMetrics($treeId),
                    'centrality_summary' => $this->graphAnalytics->getCentralitySummary($treeId),
                    'community_count' => $this->graphAnalytics->getCommunityCount($treeId)
                ],
                'insights' => [
                    'demographic_insights' => $this->demographics->getDemographicInsights($treeId),
                    'geographic_insights' => $this->geographic->getGeographicInsights($treeId),
                    'graph_insights' => $this->graphAnalytics->getGraphInsights($treeId)
                ],
                'generated_at' => now()->toISOString()
            ];
        });
    }

    private function canComputeAdvancedAnalytics(): bool
    {
        $user = request()->user();
        return $user && ($user->hasSubscription('pro') || $user->hasRole('admin'));
    }
}
