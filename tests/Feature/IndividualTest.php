<?php

namespace Tests\Feature;

use App\Models\Individual;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IndividualTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_create_individual()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->post('/individuals', [
            'name' => 'Test Individual',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('individuals', [
            'name' => 'Test Individual',
        ]);
    }

    public function test_authenticated_user_can_update_individual()
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        $individual = Individual::factory()->create(['user_id' => $user->id]);

        $response = $this->put("/individuals/{$individual->id}", [
            'name' => 'Updated Individual',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('individuals', [
            'id' => $individual->id,
            'name' => 'Updated Individual',
        ]);
    }

    public function test_authenticated_user_can_delete_individual()
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        $individual = Individual::factory()->create(['user_id' => $user->id]);

        $response = $this->delete("/individuals/{$individual->id}");
        $response->assertRedirect();
        $this->assertDatabaseMissing('individuals', [
            'id' => $individual->id,
        ]);
    }
} 