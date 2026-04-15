<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Division;

class CompanyDivisionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Create all company divisions based on organizational structure
     *
     * @return void
     */
    public function run()
    {
        $divisions = [
            ['id' => 1, 'name' => 'IT Department'],
            ['id' => 2, 'name' => 'Human Resources & GA'],
            ['id' => 3, 'name' => 'Finance & Accounting'],
            ['id' => 4, 'name' => 'EXIM'],
            ['id' => 5, 'name' => 'Business Development'],
            ['id' => 6, 'name' => 'Quality & Marketing'],
            ['id' => 7, 'name' => 'Sustainability'],
            ['id' => 8, 'name' => 'Logistic'],
            ['id' => 9, 'name' => 'PPIC'],
            ['id' => 10, 'name' => 'Purchasing'],
            ['id' => 11, 'name' => 'Lean Manufacturing'],
            ['id' => 12, 'name' => 'Quality Assurance'],
            ['id' => 13, 'name' => 'Quality Control'],
            ['id' => 14, 'name' => 'Warehouse'],
            ['id' => 15, 'name' => 'Cutting Gerber'],
            ['id' => 16, 'name' => 'Cutting Laser'],
            ['id' => 17, 'name' => 'Embo'],
            ['id' => 18, 'name' => 'Sewing'],
            ['id' => 19, 'name' => 'Finishing'],
            ['id' => 20, 'name' => 'Packing'],
            ['id' => 21, 'name' => 'Finished Goods'],
            ['id' => 22, 'name' => 'Maintenance'],
            ['id' => 23, 'name' => 'Korea Team'],
            ['id' => 24, 'name' => 'R & D'],
            ['id' => 25, 'name' => 'KTM/QTM (Training)'],
        ];

        foreach ($divisions as $division) {
            Division::firstOrCreate(
                ['id' => $division['id']],
                ['name' => $division['name']]
            );
            $this->command->info("✓ Created division: {$division['name']}");
        }

        $this->command->info("\n✅ All " . count($divisions) . " divisions created successfully!");
    }
}
