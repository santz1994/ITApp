<?php

namespace Database\Factories;

use App\WarrantyType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\WarrantyType>
 */
class WarrantyTypeFactory extends Factory
{
    protected $model = WarrantyType::class;

    public function definition(): array
    {
        return [
            'name' => fake()->randomElement([
                'Standard',
                'Extended',
                'Premium Support',
                'On-Site',
                'Next Business Day',
                'Limited',
                'Lifetime',
            ]),
        ];
    }
}
