<?php

namespace Database\Factories;

use App\TicketsStatus;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\TicketsStatus>
 */
class TicketsStatusFactory extends Factory
{
    protected $model = TicketsStatus::class;

    public function definition(): array
    {
        static $statuses = ['Open', 'In Progress', 'Pending', 'Resolved', 'Closed'];
        static $index = 0;
        
        $status = $statuses[$index % count($statuses)];
        $index++;
        
        return [
            'status' => $status,
        ];
    }
}
