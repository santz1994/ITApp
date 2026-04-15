<?php

namespace Database\Factories;

use App\Asset;
use App\AssetModel;
use App\Division;
use App\Supplier;
use App\WarrantyType;
use App\Status;
use App\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Asset>
 */
class AssetFactory extends Factory
{
    protected $model = Asset::class;

    public function definition(): array
    {
        return [
            'asset_tag' => strtoupper(fake()->bothify('AST-####-???')),
            'serial_number' => strtoupper(fake()->bothify('SN-##########')),
            'model_id' => AssetModel::factory(),
            'division_id' => Division::factory(),
            'supplier_id' => Supplier::factory(),
            'purchase_date' => fake()->dateTimeBetween('-3 years', 'now'),
            'warranty_months' => fake()->randomElement([12, 24, 36, 48, 60]),
            'warranty_type_id' => WarrantyType::factory(),
            'invoice_id' => null,
            'ip_address' => fake()->boolean(60) ? fake()->localIpv4() : null,
            'mac_address' => fake()->boolean(60) ? fake()->macAddress() : null,
            // qr_code is auto-generated in model boot
            'status_id' => Status::factory(),
            'assigned_to' => null,
            'notes' => fake()->boolean(40) ? fake()->sentence() : null,
        ];
    }

    /**
     * Indicate that the asset is assigned to a user.
     */
    public function assigned(): static
    {
        return $this->state(fn (array $attributes) => [
            'assigned_to' => User::factory(),
        ]);
    }

    /**
     * Indicate that the asset is in storage/ready to deploy.
     */
    public function readyToDeploy(): static
    {
        return $this->state([
            'assigned_to' => null,
            'ip_address' => null,
            'mac_address' => null,
        ]);
    }
}
