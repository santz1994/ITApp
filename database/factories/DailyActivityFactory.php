<?php

namespace Database\Factories;

use App\DailyActivity;
use Illuminate\Database\Eloquent\Factories\Factory;

class DailyActivityFactory extends Factory
{
    protected $model = DailyActivity::class;

    public function definition()
    {
        return [
            'user_id' => \App\User::factory(),
            'activity' => $this->faker->sentence(4),
            'date' => $this->faker->date(),
        ];
    }
}
