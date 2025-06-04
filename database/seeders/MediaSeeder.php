<?php

namespace Database\Seeders;

use App\Models\Tree;
use App\Models\User;
use Illuminate\Database\Seeder;

class MediaSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::first();
        $trees = Tree::all();
        if (! $user || $trees->isEmpty()) {
            return;
        }
        $media = [
            ['file_path' => 'photos/smith_family.jpg', 'description' => 'Smith family photo, 1985'],
            ['file_path' => 'documents/johnson_migration.pdf', 'description' => 'Migration document for Johnson family'],
            ['file_path' => 'audio/doe_legend.mp3', 'description' => 'Audio recording of Doe family legend'],
        ];
        foreach ($trees as $tree) {
            foreach ($media as $item) {
                $item['user_id'] = $user->id;
                $item['tree_id'] = $tree->id;
                \App\Models\Media::create($item);
            }
        }
    }
}
