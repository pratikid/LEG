<?php

namespace Tests\Feature;

use App\Models\Tree;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class TreeTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_create_tree()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->post('/trees', [
            'name' => 'Test Tree',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('trees', [
            'name' => 'Test Tree',
        ]);
    }

    public function test_authenticated_user_can_update_tree()
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        $tree = Tree::factory()->create(['user_id' => $user->id]);

        $response = $this->put("/trees/{$tree->id}", [
            'name' => 'Updated Tree',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('trees', [
            'id' => $tree->id,
            'name' => 'Updated Tree',
        ]);
    }

    public function test_authenticated_user_can_delete_tree()
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        $tree = Tree::factory()->create(['user_id' => $user->id]);

        $response = $this->delete("/trees/{$tree->id}");
        $response->assertRedirect();
        $this->assertDatabaseMissing('trees', [
            'id' => $tree->id,
        ]);
    }

    public function test_authenticated_user_can_import_tree()
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        $file = UploadedFile::fake()->create('import.csv', 100);

        $response = $this->post('/trees/import', [
            'file' => $file,
        ]);

        $response->assertRedirect();
        // Further assertions depend on import implementation
    }
}
