<?php

namespace Database\Factories;

use App\Location;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Location>
 */
class LocationFactory extends Factory
{
    protected $model = Location::class;

    public function definition(): array
    {
        return [
            'building' => fake()->randomElement(['Main Building', 'Building A', 'Building B', 'Building C', 'Warehouse']),
            'office' => fake()->randomElement(['Floor 1', 'Floor 2', 'Floor 3', 'Basement', 'Ground Floor']),
            'location_name' => fake()->company() . ' ' . fake()->randomElement(['Office', 'Lab', 'Storage', 'Meeting Room']),
            'storeroom' => fake()->boolean(20), // 20% chance of being a storeroom
        ];
    }

    /**
     * Indicate that the location is a storeroom.
     */
    public function storeroom(): static
    {
        return $this->state(fn (array $attributes) => [
            'storeroom' => true,
        ]);
    }
}
