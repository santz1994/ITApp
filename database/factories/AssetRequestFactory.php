<?php

namespace Database\Factories;

use App\AssetRequest;
use App\User;
use App\AssetType;
use App\Asset;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\AssetRequest>
 */
class AssetRequestFactory extends Factory
{
    protected $model = AssetRequest::class;

    public function definition(): array
    {
        return [
            'requested_by' => User::factory(),
            'asset_type_id' => AssetType::factory(),
            'justification' => fake()->paragraph(),
            'status' => 'pending',
            'approved_by' => null,
            'approved_at' => null,
            'approval_notes' => null,
            'fulfilled_asset_id' => null,
            'fulfilled_at' => null,
        ];
    }

    /**
     * Indicate that the request is approved.
     */
    public function approved(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'approved',
            'approved_by' => User::factory(),
            'approved_at' => now()->subDays(fake()->numberBetween(1, 7)),
            'approval_notes' => fake()->boolean(70) ? fake()->sentence() : null,
        ]);
    }

    /**
     * Indicate that the request is rejected.
     */
    public function rejected(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'rejected',
            'approved_by' => User::factory(),
            'approved_at' => now()->subDays(fake()->numberBetween(1, 7)),
            'approval_notes' => fake()->sentence(),
        ]);
    }

    /**
     * Indicate that the request is fulfilled.
     */
    public function fulfilled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'fulfilled',
            'approved_by' => User::factory(),
            'approved_at' => now()->subDays(fake()->numberBetween(7, 14)),
            'approval_notes' => fake()->boolean(70) ? fake()->sentence() : null,
            'fulfilled_asset_id' => Asset::factory(),
            'fulfilled_at' => now()->subDays(fake()->numberBetween(1, 7)),
        ]);
    }
}
