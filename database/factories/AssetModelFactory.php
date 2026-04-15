<?php

namespace Database\Factories;

use App\AssetModel;
use App\Manufacturer;
use App\AssetType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\AssetModel>
 */
class AssetModelFactory extends Factory
{
    protected $model = AssetModel::class;

    public function definition(): array
    {
        $models = [
            'Latitude 5420', 'Latitude 7420', 'OptiPlex 7090', 
            'EliteBook 840', 'ProBook 450', 'ProDesk 600',
            'ThinkPad X1 Carbon', 'ThinkPad T14', 'ThinkCentre M90',
            'MacBook Pro', 'MacBook Air', 'iMac',
            'Aspire 5', 'Aspire 7', 'Veriton M200',
            'VivoBook 15', 'ROG Strix', 'ZenBook 14',
        ];

        return [
            'manufacturer_id' => Manufacturer::factory(),
            'asset_type_id' => AssetType::factory(),
            'pcspec_id' => null,
            'asset_model' => fake()->randomElement($models),
            'part_number' => strtoupper(fake()->bothify('??-####-???')),
        ];
    }
}
