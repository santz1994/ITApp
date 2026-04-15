<?php

namespace Database\Factories;

use App\Division;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Division>
 */
class DivisionFactory extends Factory
{
    protected $model = Division::class;

    public function definition(): array
    {
        return [
            'name' => fake()->randomElement([
                'IT Department',
                'Human Resources',
                'Finance',
                'Operations',
                'Sales',
                'Marketing',
                'Customer Support',
                'Engineering',
                'Research & Development',
                'Administration'
            ]),
        ];
    }
}
