<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Group;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class GroupTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_create_group()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->post('/groups', [
            'name' => 'Test Group',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('groups', [
            'name' => 'Test Group',
        ]);
    }

    public function test_authenticated_user_can_update_group()
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        $group = Group::factory()->create(['user_id' => $user->id]);

        $response = $this->put("/groups/{$group->id}", [
            'name' => 'Updated Group',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('groups', [
            'id' => $group->id,
            'name' => 'Updated Group',
        ]);
    }

    public function test_authenticated_user_can_delete_group()
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        $group = Group::factory()->create(['user_id' => $user->id]);

        $response = $this->delete("/groups/{$group->id}");
        $response->assertRedirect();
        $this->assertDatabaseMissing('groups', [
            'id' => $group->id,
        ]);
    }
}
