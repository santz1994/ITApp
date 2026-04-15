<?php

namespace Database\Factories;

use App\Ticket;
use App\User;
use App\Location;
use App\TicketsStatus;
use App\TicketsType;
use App\TicketsPriority;
use App\Asset;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Ticket>
 */
class TicketFactory extends Factory
{
    protected $model = Ticket::class;

    public function definition(): array
    {
        // Use existing records or create if none exist (avoid UNIQUE constraint violations)
        $status = TicketsStatus::firstOr(function () {
            return TicketsStatus::factory()->create();
        });
        $type = TicketsType::firstOr(function () {
            return TicketsType::factory()->create();
        });
        $priority = TicketsPriority::firstOr(function () {
            return TicketsPriority::factory()->create();
        });
        
        return [
            'user_id' => User::factory(),
            'location_id' => Location::factory(),
            'ticket_status_id' => $status->id,
            'ticket_type_id' => $type->id,
            'ticket_priority_id' => $priority->id,
            'subject' => fake()->sentence(),
            'description' => fake()->paragraph(),
            // ticket_code is auto-generated in model boot
            'assigned_to' => null,
            'assigned_at' => null,
            // assignment_type has default 'auto' in migration, don't override it
            'first_response_at' => null,
            'resolved_at' => null,
            'asset_id' => null,
        ];
    }

    /**
     * Indicate that the ticket is assigned.
     */
    public function assigned(): static
    {
        return $this->state(fn (array $attributes) => [
            'assigned_to' => User::factory(),
            'assigned_at' => now()->subHours(fake()->numberBetween(1, 48)),
            'assignment_type' => fake()->randomElement(['manual', 'auto', 'super_admin']),  // Fixed: use valid enum values
        ]);
    }

    /**
     * Indicate that the ticket has received first response.
     */
    public function responded(): static
    {
        return $this->state(fn (array $attributes) => [
            'first_response_at' => now()->subHours(fake()->numberBetween(1, 24)),
        ]);
    }

    /**
     * Indicate that the ticket is resolved.
     */
    public function resolved(): static
    {
        return $this->state(fn (array $attributes) => [
            'resolved_at' => now()->subHours(fake()->numberBetween(1, 12)),
        ]);
    }

    /**
     * Indicate that the ticket is related to an asset.
     */
    public function withAsset(): static
    {
        return $this->state(fn (array $attributes) => [
            'asset_id' => Asset::factory(),
        ]);
    }
}
