<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

final class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,
            TreeSeeder::class,
            IndividualSeeder::class,
            GroupSeeder::class,
            StorySeeder::class,
            SourceSeeder::class,
            MediaSeeder::class,
        ]);
        // User::factory(10)->create();
        // Optionally create a test user if not present
        if (! User::where('email', 'test@example.com')->exists()) {
            User::factory()->create([
                'name' => 'Test User',
                'email' => 'test@example.com',
            ]);
        }
    }
}
