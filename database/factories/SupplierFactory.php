<?php

namespace Database\Factories;

use App\Supplier;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Supplier>
 */
class SupplierFactory extends Factory
{
    protected $model = Supplier::class;

    public function definition(): array
    {
        return [
            'name' => fake()->randomElement([
                'CDW',
                'Insight',
                'SHI International',
                'Ingram Micro',
                'Tech Data',
                'Synnex',
                'Amazon Business',
                'Newegg Business',
                'B&H Photo',
                'Connection',
            ]),
        ];
    }
}
