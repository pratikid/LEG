<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Group;
use App\Models\Tree;
use Illuminate\Database\Seeder;

final class GroupSeeder extends Seeder
{
    public function run(): void
    {
        $trees = Tree::all();
        if ($trees->isEmpty()) {
            return;
        }
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
