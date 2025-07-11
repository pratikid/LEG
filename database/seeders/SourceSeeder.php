<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Tree;
use App\Models\User;
use Illuminate\Database\Seeder;

final class SourceSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::first();
        $trees = Tree::all();
        if (! $user || $trees->isEmpty()) {
            return;
        }
        $sources = [
            ['title' => 'Birth Certificate', 'citation' => 'State of NY, 1950, John Smith'],
            ['title' => 'Marriage License', 'citation' => 'County of LA, 1975, Michael Johnson'],
            ['title' => 'Family Bible', 'citation' => 'Doe Family, 1900'],
        ];
        foreach ($trees as $tree) {
            foreach ($sources as $source) {
                $source['user_id'] = $user->id;
                $source['tree_id'] = $tree->id;
                \App\Models\Source::create($source);
            }
        }
    }
}
