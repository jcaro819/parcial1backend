<?php

namespace Database\Factories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
    public function definition(): array
    {
        return [
            'category_id' => Category::query()->inRandomOrder()->value('id') ?? Category::factory(),
            'name' => ucfirst(fake()->words(3, true)),
            'description' => fake()->paragraph(),
            'price' => fake()->numberBetween(5, 150) * 1000,
            'stock' => fake()->numberBetween(1, 20),
            'franchise' => fake()->randomElement(['Star Wars', 'Dune', 'The Legend of Zelda', 'Marvel']),
        ];
    }

    /** Producto con stock > 20 (en promoción). */
    public function onPromotion(): static
    {
        return $this->state(fn () => ['stock' => fake()->numberBetween(21, 100)]);
    }

    public function outOfStock(): static
    {
        return $this->state(fn () => ['stock' => 0]);
    }
}
