<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

final class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = [
            [
                'name' => 'Administrator',
                'slug' => 'admin',
                'description' => 'Full system access and control',
            ],
            [
                'name' => 'User',
                'slug' => 'user',
                'description' => 'Regular user with basic access',
            ],
            [
                'name' => 'Editor',
                'slug' => 'editor',
                'description' => 'Can edit content but not manage users',
            ],
        ];

        foreach ($roles as $role) {
            Role::create($role);
        }
    }
}
