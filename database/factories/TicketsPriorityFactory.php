<?php

namespace Database\Factories;

use App\TicketsPriority;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\TicketsPriority>
 */
class TicketsPriorityFactory extends Factory
{
    protected $model = TicketsPriority::class;

    public function definition(): array
    {
        return [
            'priority' => fake()->randomElement(['Low', 'Normal', 'High', 'Urgent', 'Critical']),
        ];
    }
}
