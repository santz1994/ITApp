<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Asset;

class AssetSerialTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function cannot_create_assets_with_duplicate_serial_number()
    {
        // Create all required dependencies for assets
        
        // 1. Manufacturer
        $manufacturer = \App\Manufacturer::firstOrCreate(
            ['name' => 'Test Manufacturer']
        );

        // 2. Asset Type
        $assetType = \App\AssetType::firstOrCreate(
            ['type_name' => 'Test Asset Type', 'abbreviation' => 'TST']
        );

        // 3. Asset Model (requires manufacturer and asset type)
        $assetModel = \App\AssetModel::firstOrCreate(
            ['asset_model' => 'Test Model'],
            [
                'manufacturer_id' => $manufacturer->id,
                'asset_type_id' => $assetType->id,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        // 4. Supplier
        $supplier = \App\Supplier::firstOrCreate(
            ['name' => 'Test Supplier']
        );

        // 5. Division
        $division = \App\Division::firstOrCreate(
            ['name' => 'Test Division']
        );

        // 6. Status
        $status = \App\Status::firstOrCreate(
            ['name' => 'Test Status']
        );

        // Create an existing asset with serial number and all required fields
        Asset::create([
            'asset_tag' => 'TEST1',
            'name' => 'Test Asset 1',
            'serial_number' => 'SN-12345',
            'model_id' => $assetModel->id,
            'division_id' => $division->id,
            'status_id' => $status->id,
            'supplier_id' => $supplier->id,
        ]);

        // Attempt to create another asset with the same serial number via API
        $this->actingAs($this->createAdminUser())
             ->postJson('/api/assets', [
                 'asset_tag' => 'TEST2',
                 'name' => 'Test Asset 2',
                 'serial_number' => 'SN-12345',
                 'model_id' => $assetModel->id,
                 'division_id' => $division->id,
                 'status_id' => $status->id,
                 'supplier_id' => $supplier->id,
             ])
             ->assertStatus(422)
             ->assertJsonFragment(['success' => false]);
    }

    protected function createAdminUser()
    {
        // Create a user with all required fields including api_token
        $email = 'admin@example.test';
        
        // Check if user already exists
        $user = \App\User::where('email', $email)->first();
        
        if (!$user) {
            // Create new user with DB insert to bypass fillable restrictions
            \Illuminate\Support\Facades\DB::table('users')->insert([
                'name' => 'Test Admin',
                'email' => $email,
                'password' => bcrypt('secret'),
                'is_active' => 1,
                'api_token' => \Illuminate\Support\Str::random(60),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            
            $user = \App\User::where('email', $email)->first();
        }
        
        // Try to assign admin role if roles package present
        if (method_exists($user, 'assignRole')) {
            try { $user->assignRole('admin'); } catch (\Throwable $e) {}
        }
        
        return $user;
    }
}
