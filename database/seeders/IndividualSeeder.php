<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Individual;
use App\Models\Tree;
use Illuminate\Database\Seeder;

final class IndividualSeeder extends Seeder
{
    public function run(): void
    {
        $trees = Tree::all();
        if ($trees->isEmpty()) {
            return;
        }
        $individuals = [
            ['first_name' => 'John', 'last_name' => 'Smith', 'birth_date' => '1950-01-01', 'death_date' => null],
            ['first_name' => 'Jane', 'last_name' => 'Smith', 'birth_date' => '1952-05-10', 'death_date' => null],
            ['first_name' => 'Michael', 'last_name' => 'Johnson', 'birth_date' => '1975-03-15', 'death_date' => null],
            ['first_name' => 'Emily', 'last_name' => 'Doe', 'birth_date' => '1980-07-20', 'death_date' => null],
        ];
        foreach ($trees as $tree) {
            foreach ($individuals as $ind) {
                $ind['tree_id'] = $tree->id;
                Individual::create($ind);
            }
        }
    }
}
