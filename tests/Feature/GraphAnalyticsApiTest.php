<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use App\Models\Tree;
use App\Models\Individual;
use App\Services\Analytics\Neo4jAnalyticsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;
use Mockery;

/**
 * Test Neo4j Graph Analytics API Endpoints
 * 
 * This test suite demonstrates the usage patterns for the graph analytics
 * API endpoints and validates their responses and behavior.
 */
class GraphAnalyticsApiTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Tree $tree;
    private Neo4jAnalyticsService $mockService;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->user = User::factory()->create([
            'subscription_type' => 'pro'  // For advanced analytics access
        ]);
        
        $this->tree = Tree::factory()->create([
            'user_id' => $this->user->id,
            'name' => 'Test Family Tree',
            'public_analytics_enabled' => true
        ]);

        // Mock the Neo4jAnalyticsService for testing
        $this->mockService = Mockery::mock(Neo4jAnalyticsService::class);
        $this->app->instance(Neo4jAnalyticsService::class, $this->mockService);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /** @test */
    public function can_get_network_metrics_for_tree()
    {
        $expectedMetrics = [
            'node_count' => 150,
            'relationship_count' => 245,
            'density' => 0.0218,
            'clustering_coefficient' => 0.342,
            'diameter' => 8,
            'connected_components' => 1,
            'average_degree' => 3.27
        ];

        $this->mockService
            ->shouldReceive('getNetworkMetrics')
            ->with($this->tree->id)
            ->once()
            ->andReturn($expectedMetrics);

        $response = $this->actingAs($this->user, 'api')
            ->getJson("/api/v1/analytics/trees/{$this->tree->id}/graph?section=network");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'network_metrics' => $expectedMetrics
                ],
                'meta' => [
                    'tree_id' => $this->tree->id,
                    'section' => 'network'
                ]
            ]);
    }

    /** @test */
    public function can_get_centrality_analysis_for_tree()
    {
        $expectedCentrality = [
            'degree_centrality' => [
                'top_individuals' => [
                    ['individual_id' => 123, 'name' => 'John Smith', 'score' => 0.85],
                    ['individual_id' => 456, 'name' => 'Mary Johnson', 'score' => 0.72]
                ],
                'average_score' => 0.23
            ],
            'betweenness_centrality' => [
                'top_individuals' => [
                    ['individual_id' => 123, 'name' => 'John Smith', 'score' => 0.45],
                    ['individual_id' => 789, 'name' => 'Robert Brown', 'score' => 0.38]
                ],
                'average_score' => 0.11
            ],
            'pagerank' => [
                'top_individuals' => [
                    ['individual_id' => 123, 'name' => 'John Smith', 'score' => 0.023],
                    ['individual_id' => 456, 'name' => 'Mary Johnson', 'score' => 0.019]
                ],
                'average_score' => 0.007
            ]
        ];

        $this->mockService
            ->shouldReceive('getCentralityAnalysis')
            ->with($this->tree->id)
            ->once()
            ->andReturn($expectedCentrality);

        $response = $this->actingAs($this->user, 'api')
            ->getJson("/api/v1/analytics/trees/{$this->tree->id}/graph/centrality");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'centrality_analysis' => $expectedCentrality
                ]
            ]);
    }

    /** @test */
    public function can_find_paths_between_individuals()
    {
        $fromId = 123;
        $toId = 456;
        $expectedPaths = [
            'paths' => [
                [
                    'length' => 3,
                    'individuals' => [123, 789, 345, 456],
                    'relationships' => ['parent', 'spouse', 'parent'],
                    'strength' => 0.82
                ],
                [
                    'length' => 4,
                    'individuals' => [123, 111, 222, 333, 456],
                    'relationships' => ['child', 'sibling', 'parent', 'spouse'],
                    'strength' => 0.67
                ]
            ],
            'shortest_path_length' => 3,
            'total_paths_found' => 2
        ];

        $this->mockService
            ->shouldReceive('findPaths')
            ->with($this->tree->id, $fromId, $toId, 6, null)
            ->once()
            ->andReturn($expectedPaths);

        $response = $this->actingAs($this->user, 'api')
            ->getJson("/api/v1/analytics/trees/{$this->tree->id}/paths" .
                "?from_individual_id={$fromId}&to_individual_id={$toId}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => $expectedPaths,
                'meta' => [
                    'tree_id' => $this->tree->id,
                    'from_individual_id' => $fromId,
                    'to_individual_id' => $toId
                ]
            ]);
    }

    /** @test */
    public function can_find_most_recent_common_ancestor()
    {
        $individualIds = [123, 456, 789];
        $expectedMRCA = [
            'mrca' => [
                'individual_id' => 999,
                'name' => 'Common Ancestor',
                'birth_year' => 1820,
                'generation_depth' => 5
            ],
            'paths_to_mrca' => [
                123 => ['path_length' => 3, 'generations_up' => 3],
                456 => ['path_length' => 4, 'generations_up' => 4],
                789 => ['path_length' => 2, 'generations_up' => 2]
            ],
            'relationship_coefficient' => 0.125
        ];

        $this->mockService
            ->shouldReceive('findMRCA')
            ->with($this->tree->id, $individualIds)
            ->once()
            ->andReturn($expectedMRCA);

        $response = $this->actingAs($this->user, 'api')
            ->getJson("/api/v1/analytics/trees/{$this->tree->id}/mrca" .
                "?individual_ids=" . implode(',', $individualIds));

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => $expectedMRCA,
                'meta' => [
                    'tree_id' => $this->tree->id,
                    'individual_ids' => $individualIds
                ]
            ]);
    }

    /** @test */
    public function can_get_influential_individuals_by_pagerank()
    {
        $expectedInfluential = [
            ['individual_id' => 123, 'name' => 'John Smith', 'pagerank_score' => 0.023, 'rank' => 1],
            ['individual_id' => 456, 'name' => 'Mary Johnson', 'pagerank_score' => 0.019, 'rank' => 2],
            ['individual_id' => 789, 'name' => 'Robert Brown', 'pagerank_score' => 0.017, 'rank' => 3]
        ];

        $this->mockService
            ->shouldReceive('getTopIndividualsByPageRank')
            ->with($this->tree->id, 20)
            ->once()
            ->andReturn($expectedInfluential);

        $response = $this->actingAs($this->user, 'api')
            ->getJson("/api/v1/analytics/trees/{$this->tree->id}/influential?metric=pagerank&limit=20");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => $expectedInfluential,
                'meta' => [
                    'tree_id' => $this->tree->id,
                    'metric' => 'pagerank',
                    'limit' => 20
                ]
            ]);
    }

    /** @test */
    public function can_compute_graph_analytics_with_proper_permissions()
    {
        $expectedResults = [
            'sections_computed' => ['centrality', 'communities', 'network'],
            'computation_time_seconds' => 12.34,
            'individuals_processed' => 150,
            'relationships_processed' => 245,
            'cache_updated' => true
        ];

        $this->mockService
            ->shouldReceive('computeAndCacheAnalytics')
            ->with($this->tree->id, ['centrality', 'communities', 'network'])
            ->once()
            ->andReturn($expectedResults);

        $response = $this->actingAs($this->user, 'api')
            ->postJson("/api/v1/analytics/trees/{$this->tree->id}/graph/compute", [
                'sections' => ['centrality', 'communities', 'network']
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Graph analytics computation completed',
                'data' => $expectedResults
            ]);
    }

    /** @test */
    public function prevents_graph_computation_without_premium_subscription()
    {
        // Change user to basic subscription
        $this->user->update(['subscription_type' => 'basic']);

        $response = $this->actingAs($this->user, 'api')
            ->postJson("/api/v1/analytics/trees/{$this->tree->id}/graph/compute", [
                'sections' => ['centrality', 'communities']
            ]);

        $response->assertStatus(402)  // Payment Required
            ->assertJson([
                'error' => 'Advanced graph analytics require premium subscription'
            ]);
    }

    /** @test */
    public function can_get_relationship_strength_between_individuals()
    {
        $individual1Id = 123;
        $individual2Id = 456;
        $expectedStrength = [
            'relationship_strength' => 0.75,
            'direct_relationship' => true,
            'relationship_type' => 'spouse',
            'common_connections' => 8,
            'path_strength' => 1.0,
            'genealogical_distance' => 1,
            'coefficient_of_relationship' => 0.0  // Not blood related
        ];

        $this->mockService
            ->shouldReceive('getRelationshipStrength')
            ->with($this->tree->id, $individual1Id, $individual2Id)
            ->once()
            ->andReturn($expectedStrength);

        $response = $this->actingAs($this->user, 'api')
            ->getJson("/api/v1/analytics/trees/{$this->tree->id}/relationship-strength" .
                "?individual1_id={$individual1Id}&individual2_id={$individual2Id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => $expectedStrength,
                'meta' => [
                    'tree_id' => $this->tree->id,
                    'individual1_id' => $individual1Id,
                    'individual2_id' => $individual2Id
                ]
            ]);
    }

    /** @test */
    public function can_get_community_analysis()
    {
        $expectedCommunities = [
            'algorithm_used' => 'louvain',
            'community_count' => 6,
            'modularity_score' => 0.67,
            'communities' => [
                [
                    'community_id' => 1,
                    'size' => 45,
                    'members' => [123, 456, 789],  // Sample members
                    'characteristics' => [
                        'primary_surname' => 'Smith',
                        'geographic_center' => 'Boston, MA',
                        'time_period' => '1850-1920',
                        'avg_generation_depth' => 3.2
                    ]
                ],
                [
                    'community_id' => 2,
                    'size' => 32,
                    'members' => [111, 222, 333],
                    'characteristics' => [
                        'primary_surname' => 'Johnson',
                        'geographic_center' => 'New York, NY',
                        'time_period' => '1870-1940',
                        'avg_generation_depth' => 2.8
                    ]
                ]
            ]
        ];

        $this->mockService
            ->shouldReceive('getCommunityAnalysis')
            ->with($this->tree->id)
            ->once()
            ->andReturn($expectedCommunities);

        $response = $this->actingAs($this->user, 'api')
            ->getJson("/api/v1/analytics/trees/{$this->tree->id}/graph/communities");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'communities' => $expectedCommunities
                ]
            ]);
    }

    /** @test */
    public function validates_required_parameters_for_relationship_analysis()
    {
        $response = $this->actingAs($this->user, 'api')
            ->getJson("/api/v1/analytics/trees/{$this->tree->id}/relationship-strength");

        $response->assertStatus(422)  // Validation error
            ->assertJsonValidationErrors(['individual1_id', 'individual2_id']);
    }

    /** @test */
    public function caches_graph_analytics_results()
    {
        $cacheKey = "analytics:graph:{$this->tree->id}:network";
        
        // Ensure cache is empty initially
        Cache::forget($cacheKey);
        
        $expectedMetrics = [
            'node_count' => 150,
            'relationship_count' => 245,
            'density' => 0.0218
        ];

        $this->mockService
            ->shouldReceive('getNetworkMetrics')
            ->with($this->tree->id)
            ->once()  // Should only be called once due to caching
            ->andReturn($expectedMetrics);

        // First request - should hit the service
        $response1 = $this->actingAs($this->user, 'api')
            ->getJson("/api/v1/analytics/trees/{$this->tree->id}/graph?section=network");

        $response1->assertStatus(200);

        // Second request - should use cached result
        $response2 = $this->actingAs($this->user, 'api')
            ->getJson("/api/v1/analytics/trees/{$this->tree->id}/graph?section=network");

        $response2->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'network_metrics' => $expectedMetrics
                ]
            ]);
    }

    /** @test */
    public function can_access_public_analytics_for_enabled_trees()
    {
        $expectedStats = [
            'network_size' => 150,
            'relationship_count' => 245,
            'generations_depth' => 8,
            'largest_family_cluster' => 45,
            'community_count' => 6
        ];

        $this->mockService
            ->shouldReceive('getNodeCount')
            ->with($this->tree->id)
            ->andReturn(150);

        $this->mockService
            ->shouldReceive('getRelationshipCount')
            ->with($this->tree->id)
            ->andReturn(245);

        $this->mockService
            ->shouldReceive('getMaxGenerationDepth')
            ->with($this->tree->id)
            ->andReturn(8);

        $this->mockService
            ->shouldReceive('getLargestFamilyClusterSize')
            ->with($this->tree->id)
            ->andReturn(45);

        $this->mockService
            ->shouldReceive('getCommunityCount')
            ->with($this->tree->id)
            ->andReturn(6);

        $response = $this->getJson("/api/v1/analytics/public/trees/{$this->tree->id}/stats");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => $expectedStats,
                'meta' => [
                    'tree_id' => $this->tree->id,
                    'public_access' => true,
                    'anonymized' => true
                ]
            ]);
    }

    /** @test */
    public function prevents_unauthorized_tree_access()
    {
        $otherUser = User::factory()->create();
        $otherTree = Tree::factory()->create(['user_id' => $otherUser->id]);

        $response = $this->actingAs($this->user, 'api')
            ->getJson("/api/v1/analytics/trees/{$otherTree->id}/graph");

        $response->assertStatus(403)
            ->assertJson([
                'error' => 'Unauthorized access to tree'
            ]);
    }

    /** @test */
    public function can_batch_analyze_relationships()
    {
        $pairs = [[123, 456], [123, 789], [456, 789]];
        
        $this->mockService
            ->shouldReceive('getRelationshipStrength')
            ->with($this->tree->id, 123, 456)
            ->andReturn(['strength' => 0.75]);
            
        $this->mockService
            ->shouldReceive('getRelationshipStrength')
            ->with($this->tree->id, 123, 789)
            ->andReturn(['strength' => 0.45]);
            
        $this->mockService
            ->shouldReceive('getRelationshipStrength')
            ->with($this->tree->id, 456, 789)
            ->andReturn(['strength' => 0.32]);

        $response = $this->actingAs($this->user, 'api')
            ->postJson('/api/v1/analytics/bulk/relationship-batch', [
                'tree_id' => $this->tree->id,
                'individual_pairs' => $pairs
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    ['individual1_id' => 123, 'individual2_id' => 456],
                    ['individual1_id' => 123, 'individual2_id' => 789],
                    ['individual1_id' => 456, 'individual2_id' => 789]
                ]
            ]);
    }
}
