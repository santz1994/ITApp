<?php

namespace Database\Factories;

use App\Manufacturer;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Manufacturer>
 */
class ManufacturerFactory extends Factory
{
    protected $model = Manufacturer::class;

    public function definition(): array
    {
        return [
            'name' => fake()->randomElement([
                'Dell',
                'HP',
                'Lenovo',
                'Apple',
                'Acer',
                'Asus',
                'Microsoft',
                'Samsung',
                'Cisco',
                'Logitech',
                'Canon',
                'Epson',
            ]),
        ];
    }
}
