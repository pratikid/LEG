<?php

declare(strict_types=1);

use App\Http\Controllers\Api\AnalyticsController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Graph Analytics API Routes
|--------------------------------------------------------------------------
|
| These routes handle Neo4j graph analytics API endpoints for genealogy
| relationship analysis, centrality metrics, community detection, and 
| advanced graph algorithms.
|
*/

Route::middleware(['auth:api', 'throttle:analytics'])->group(function () {
    
    // Graph analytics endpoints
    Route::prefix('analytics/trees/{treeId}')->group(function () {
        
        // General graph analytics
        Route::get('/graph', [AnalyticsController::class, 'getGraphAnalytics']);
        
        // Relationship analysis
        Route::get('/relationship-strength', [AnalyticsController::class, 'getRelationshipStrength']);
        Route::get('/paths', [AnalyticsController::class, 'findPaths']);
        Route::get('/mrca', [AnalyticsController::class, 'findMRCA']);
        
        // Individual rankings and influence
        Route::get('/influential', [AnalyticsController::class, 'getInfluentialIndividuals']);
        
        // Computation and caching
        Route::post('/graph/compute', [AnalyticsController::class, 'computeGraphAnalytics']);
    });

    // Graph analytics sections with specific endpoints
    Route::prefix('analytics/trees/{treeId}/graph')->group(function () {
        
        // Network metrics
        Route::get('/network', function (int $treeId) {
            return app(AnalyticsController::class)->getGraphAnalytics(
                request()->merge(['section' => 'network']), 
                $treeId
            );
        });
        
        // Centrality analysis
        Route::get('/centrality', function (int $treeId) {
            return app(AnalyticsController::class)->getGraphAnalytics(
                request()->merge(['section' => 'centrality']), 
                $treeId
            );
        });
        
        // Community detection
        Route::get('/communities', function (int $treeId) {
            return app(AnalyticsController::class)->getGraphAnalytics(
                request()->merge(['section' => 'communities']), 
                $treeId
            );
        });
        
        // Relationship patterns
        Route::get('/relationships', function (int $treeId) {
            return app(AnalyticsController::class)->getGraphAnalytics(
                request()->merge(['section' => 'relationships']), 
                $treeId
            );
        });
        
        // Ancestry path analysis
        Route::get('/ancestry', function (int $treeId) {
            return app(AnalyticsController::class)->getGraphAnalytics(
                request()->merge(['section' => 'ancestry']), 
                $treeId
            );
        });
        
        // Family clusters
        Route::get('/clusters', function (int $treeId) {
            return app(AnalyticsController::class)->getGraphAnalytics(
                request()->merge(['section' => 'clusters']), 
                $treeId
            );
        });
    });

    // Individual-specific graph analytics
    Route::prefix('analytics/individuals/{individualId}')->group(function () {
        
        // Individual centrality scores
        Route::get('/centrality', function (int $individualId) {
            $treeId = request()->query('tree_id');
            if (!$treeId) {
                return response()->json(['error' => 'tree_id parameter required'], 400);
            }
            
            return app(Neo4jAnalyticsService::class)->getIndividualCentrality($treeId, $individualId);
        });
        
        // Individual's direct connections
        Route::get('/connections', function (int $individualId) {
            $treeId = request()->query('tree_id');
            if (!$treeId) {
                return response()->json(['error' => 'tree_id parameter required'], 400);
            }
            
            return app(Neo4jAnalyticsService::class)->getIndividualConnections($treeId, $individualId);
        });
        
        // Individual's position in family clusters
        Route::get('/clusters', function (int $individualId) {
            $treeId = request()->query('tree_id');
            if (!$treeId) {
                return response()->json(['error' => 'tree_id parameter required'], 400);
            }
            
            return app(Neo4jAnalyticsService::class)->getIndividualClusters($treeId, $individualId);
        });
    });

    // Bulk analytics endpoints
    Route::prefix('analytics/bulk')->group(function () {
        
        // Multi-tree comparison
        Route::post('/compare-trees', function () {
            $treeIds = request()->json('tree_ids', []);
            if (count($treeIds) < 2) {
                return response()->json(['error' => 'At least 2 tree IDs required'], 400);
            }
            
            $results = [];
            foreach ($treeIds as $treeId) {
                $results[$treeId] = app(Neo4jAnalyticsService::class)->getNetworkMetrics($treeId);
            }
            
            return response()->json([
                'success' => true,
                'data' => $results,
                'comparison_summary' => app(Neo4jAnalyticsService::class)->compareNetworks($treeIds)
            ]);
        });
        
        // Batch relationship analysis
        Route::post('/relationship-batch', function () {
            $pairs = request()->json('individual_pairs', []);
            $treeId = request()->json('tree_id');
            
            if (!$treeId) {
                return response()->json(['error' => 'tree_id required'], 400);
            }
            
            if (empty($pairs)) {
                return response()->json(['error' => 'individual_pairs required'], 400);
            }
            
            $results = [];
            $neo4jService = app(Neo4jAnalyticsService::class);
            
            foreach ($pairs as $pair) {
                if (count($pair) >= 2) {
                    $results[] = [
                        'individual1_id' => $pair[0],
                        'individual2_id' => $pair[1],
                        'relationship_strength' => $neo4jService->getRelationshipStrength($treeId, $pair[0], $pair[1])
                    ];
                }
            }
            
            return response()->json([
                'success' => true,
                'data' => $results
            ]);
        });
    });
    
    // Analytics insights specific to graph analysis
    Route::get('analytics/trees/{treeId}/graph/insights', function (int $treeId) {
        $service = app(Neo4jAnalyticsService::class);
        
        $insights = [
            'network_insights' => $service->getNetworkInsights($treeId),
            'relationship_insights' => $service->getRelationshipInsights($treeId),
            'community_insights' => $service->getCommunityInsights($treeId),
            'centrality_insights' => $service->getCentralityInsights($treeId)
        ];
        
        return response()->json([
            'success' => true,
            'data' => $insights,
            'meta' => [
                'tree_id' => $treeId,
                'generated_at' => now()->toISOString()
            ]
        ]);
    });
});

// Public analytics endpoints (limited access)
Route::middleware(['throttle:public-analytics'])->group(function () {
    
    // Public tree statistics (anonymized)
    Route::get('analytics/public/trees/{treeId}/stats', function (int $treeId) {
        // Check if tree allows public analytics
        $tree = App\Models\Tree::find($treeId);
        if (!$tree || !$tree->public_analytics_enabled) {
            return response()->json(['error' => 'Public analytics not available'], 403);
        }
        
        $service = app(Neo4jAnalyticsService::class);
        
        return response()->json([
            'success' => true,
            'data' => [
                'network_size' => $service->getNodeCount($treeId),
                'relationship_count' => $service->getRelationshipCount($treeId),
                'generations_depth' => $service->getMaxGenerationDepth($treeId),
                'largest_family_cluster' => $service->getLargestFamilyClusterSize($treeId),
                'community_count' => $service->getCommunityCount($treeId)
            ],
            'meta' => [
                'tree_id' => $treeId,
                'public_access' => true,
                'anonymized' => true
            ]
        ]);
    });
});
