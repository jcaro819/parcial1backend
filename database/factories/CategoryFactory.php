<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Category>
 */
class CategoryFactory extends Factory
{
    public function definition(): array
    {
        $name = ucfirst(fake()->unique()->word()).' '.fake()->unique()->numberBetween(1, 99999);

        return ['name' => $name, 'slug' => Str::slug($name), 'description' => fake()->sentence()];
    }
}
