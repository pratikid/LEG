<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Tree;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class TreeFactory extends Factory
{
    protected $model = Tree::class;

    #[\Override]
    public function definition(): array
    {
        return [
            'name' => $this->faker->words(3, true),
            'description' => $this->faker->optional()->sentence(),
            'user_id' => User::factory(),
        ];
    }
}
