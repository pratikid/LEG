<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
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
