<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Tree;
use App\Models\User;

class TreeSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::first();
        if (!$user) return;
        $trees = [
            ['name' => 'Smith Family Tree', 'description' => 'Descendants of John Smith', 'user_id' => $user->id],
            ['name' => 'Johnson Lineage', 'description' => 'Johnson family roots and branches', 'user_id' => $user->id],
            ['name' => 'Doe Heritage', 'description' => 'The Doe family through generations', 'user_id' => $user->id],
        ];
        foreach ($trees as $tree) {
            Tree::create($tree);
        }
    }
} 