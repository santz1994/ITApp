<?php

namespace Database\Factories;

use App\Status;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Status>
 */
class StatusFactory extends Factory
{
    protected $model = Status::class;

    public function definition(): array
    {
        return [
            'name' => fake()->randomElement([
                'Ready to Deploy',
                'In Use',
                'Under Maintenance',
                'Broken',
                'Lost',
                'Retired',
                'Pending Disposal',
                'In Storage',
            ]),
        ];
    }

    /**
     * Indicate that the status is ready to deploy.
     */
    public function readyToDeploy(): static
    {
        return $this->state(['name' => 'Ready to Deploy']);
    }

    /**
     * Indicate that the status is in use.
     */
    public function inUse(): static
    {
        return $this->state(['name' => 'In Use']);
    }
}
