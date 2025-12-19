<?php

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        return [
            'name' => fake()->words(3, true),
            'description' => fake()->sentence(),
            'material' => fake()->randomElement(['Wood', 'Metal', 'Plastic', 'Glass', 'Teak']),
            'is_featured' => fake()->boolean(20), // 20% chance of being featured
            'master_category_id' => null,
            'dimension_id' => null,
            'create_by' => null,
        ];
    }

    /**
     * Indicate that the product is featured.
     */
    public function featured(): static
    {
        return $this->state(fn(array $attributes) => [
            'is_featured' => true,
        ]);
    }

    /**
     * Indicate that the product is not featured.
     */
    public function notFeatured(): static
    {
        return $this->state(fn(array $attributes) => [
            'is_featured' => false,
        ]);
    }
}
