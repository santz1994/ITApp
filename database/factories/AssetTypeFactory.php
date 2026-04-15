<?php

namespace Database\Factories;

use App\AssetType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\AssetType>
 */
class AssetTypeFactory extends Factory
{
    protected $model = AssetType::class;

    public function definition(): array
    {
        $types = [
            ['name' => 'Laptop', 'abbr' => 'LPT'],
            ['name' => 'Desktop', 'abbr' => 'DSK'],
            ['name' => 'Monitor', 'abbr' => 'MON'],
            ['name' => 'Printer', 'abbr' => 'PRT'],
            ['name' => 'Server', 'abbr' => 'SRV'],
            ['name' => 'Router', 'abbr' => 'RTR'],
            ['name' => 'Switch', 'abbr' => 'SWT'],
            ['name' => 'Mouse', 'abbr' => 'MOU'],
            ['name' => 'Keyboard', 'abbr' => 'KBD'],
            ['name' => 'UPS', 'abbr' => 'UPS'],
        ];

        $type = fake()->randomElement($types);

        return [
            'type_name' => $type['name'],
            'abbreviation' => $type['abbr'],
            'spare' => fake()->boolean(30),
        ];
    }

    /**
     * Indicate that the asset type is a spare.
     */
    public function spare(): static
    {
        return $this->state(['spare' => true]);
    }
}
