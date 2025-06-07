<?php

namespace Tests\Feature;

use App\Models\Tree;
use App\Models\User;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
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
        $this->withoutMiddleware(VerifyCsrfToken::class);

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

    public function test_authenticated_user_can_import_gedcom_tree()
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        $gedcom = <<<'GED'
0 @I1@ INDI
1 NAME John /Doe/
1 SEX M
1 BIRT
2 DATE 1 JAN 1900
0 @I2@ INDI
1 NAME Jane /Smith/
1 SEX F
1 BIRT
2 DATE 2 FEB 1902
0 @F1@ FAM
 1 HUSB @I1@
 1 WIFE @I2@
1 CHIL @I3@
GED;
        $file = UploadedFile::fake()->createWithContent('import.ged', $gedcom);
        $response = $this->post('/trees/import', [
            'gedcom' => $file,
        ]);
        $response->assertRedirect();
        $this->assertDatabaseHas('trees', [
            'description' => 'Imported from GEDCOM',
            'user_id' => $user->id,
        ]);
        $this->assertDatabaseHas('individuals', [
            'first_name' => 'John',
            'last_name' => 'Doe',
        ]);
        $this->assertDatabaseHas('individuals', [
            'first_name' => 'Jane',
            'last_name' => 'Smith',
        ]);
        $this->assertDatabaseHas('groups', [
            'tree_id' => Tree::first()->id,
        ]);
    }
}
