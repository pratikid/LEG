<?php

namespace Tests\Feature;

use App\Models\Tree;
use App\Models\User;
use App\Services\Neo4jIndividualService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Mockery;
use Tests\TestCase;

class TreeControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected Neo4jIndividualService $neo4jService;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Mock Neo4j service
        $this->neo4jService = Mockery::mock(Neo4jIndividualService::class);
        $this->app->instance(Neo4jIndividualService::class, $this->neo4jService);
    }

    public function test_user_can_create_tree(): void
    {
        // Create a user
        $user = User::factory()->create();

        // Mock Neo4j transaction
        $transaction = Mockery::mock('transaction');
        $transaction->shouldReceive('run')->with('COMMIT')->once();
        
        $this->neo4jService->shouldReceive('beginTransaction')
            ->once()
            ->andReturn($transaction);
            
        $this->neo4jService->shouldReceive('createTreeNode')
            ->once()
            ->andReturn(true);

        // Create tree data
        $treeData = [
            'name' => 'Test Family Tree',
            'description' => 'A test family tree',
        ];

        // Act as the user and create a tree
        $response = $this->actingAs($user)
            ->post(route('trees.store'), $treeData);

        // Assert response
        $response->assertRedirect();
        $response->assertSessionHas('success');

        // Assert database
        $this->assertDatabaseHas('trees', [
            'name' => $treeData['name'],
            'description' => $treeData['description'],
            'user_id' => $user->id,
        ]);
    }

    public function test_unauthenticated_user_cannot_create_tree(): void
    {
        $treeData = [
            'name' => 'Test Family Tree',
            'description' => 'A test family tree',
        ];

        $response = $this->post(route('trees.store'), $treeData);

        $response->assertRedirect(route('login'));
    }

    public function test_tree_creation_validates_required_fields(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->post(route('trees.store'), []);

        $response->assertSessionHasErrors(['name']);
    }

    public function test_tree_creation_handles_neo4j_failure(): void
    {
        $user = User::factory()->create();

        // Mock Neo4j transaction to throw an exception
        $transaction = Mockery::mock('transaction');
        $transaction->shouldReceive('run')->with('ROLLBACK')->once();
        
        $this->neo4jService->shouldReceive('beginTransaction')
            ->once()
            ->andReturn($transaction);
            
        $this->neo4jService->shouldReceive('createTreeNode')
            ->once()
            ->andThrow(new \Exception('Neo4j error'));

        $treeData = [
            'name' => 'Test Family Tree',
            'description' => 'A test family tree',
        ];

        $response = $this->actingAs($user)
            ->post(route('trees.store'), $treeData);

        $response->assertRedirect();
        $response->assertSessionHasErrors('error');

        // Assert no tree was created in the database
        $this->assertDatabaseMissing('trees', [
            'name' => $treeData['name'],
        ]);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
} 