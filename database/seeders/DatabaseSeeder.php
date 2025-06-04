<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
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
        if (! \App\Models\User::where('email', 'test@example.com')->exists()) {
            \App\Models\User::factory()->create([
                'name' => 'Test User',
                'email' => 'test@example.com',
            ]);
        }
    }
}
