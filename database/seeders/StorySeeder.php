<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Story;
use App\Models\Tree;
use App\Models\User;

class StorySeeder extends Seeder
{
    public function run(): void
    {
        $user = User::first();
        $trees = Tree::all();
        if (!$user || $trees->isEmpty()) return;
        $stories = [
            ['title' => 'The Smith Reunion', 'content' => 'A story about the Smith family reunion in 1990.'],
            ['title' => 'Johnson Migration', 'content' => 'How the Johnsons moved from Europe to America.'],
            ['title' => 'Doe Family Legend', 'content' => 'A legend passed down in the Doe family.'],
        ];
        foreach ($trees as $tree) {
            foreach ($stories as $story) {
                $story['user_id'] = $user->id;
                $story['tree_id'] = $tree->id;
                \App\Models\Story::create($story);
            }
        }
    }
} 