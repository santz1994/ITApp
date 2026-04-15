<?php

namespace Database\Factories;

use App\TicketsType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\TicketsType>
 */
class TicketsTypeFactory extends Factory
{
    protected $model = TicketsType::class;

    public function definition(): array
    {
        return [
            'type' => fake()->randomElement([
                'Hardware Issue',
                'Software Issue',
                'Network Problem',
                'Access Request',
                'General Inquiry',
                'Maintenance',
                'Installation',
                'Bug Report'
            ]),
        ];
    }
}
