<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Group;
use App\Models\Tree;

class GroupSeeder extends Seeder
{
    public function run(): void
    {
        $trees = Tree::all();
        if ($trees->isEmpty()) return;
        $groups = [
            ['name' => 'Immediate Family', 'description' => 'Parents and children'],
            ['name' => 'Cousins', 'description' => 'All cousins in the tree'],
            ['name' => 'Ancestors', 'description' => 'Direct ancestors'],
        ];
        foreach ($trees as $tree) {
            foreach ($groups as $group) {
                $group['tree_id'] = $tree->id;
                Group::create($group);
            }
        }
    }
} 