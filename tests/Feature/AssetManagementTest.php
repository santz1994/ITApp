<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Traits\TestDataHelper;
use App\Asset;

/**
 * Asset Management Feature Tests
 * 
 * Tests comprehensive asset CRUD operations, validation,
 * relationships, and business logic.
 */
class AssetManagementTest extends TestCase
{
    use DatabaseTransactions, TestDataHelper;

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        try {
            if (class_exists(\Database\Seeders\LocationsTableSeeder::class)) {
                (new \Database\Seeders\LocationsTableSeeder())->run();
            }
        } catch (\Throwable $e) {}
        try {
            if (class_exists(\Database\Seeders\RolesTableSeeder::class)) {
                (new \Database\Seeders\RolesTableSeeder())->run();
            }
        } catch (\Throwable $e) {}
        try {
            if (class_exists(\Database\Seeders\TestUsersTableSeeder::class)) {
                (new \Database\Seeders\TestUsersTableSeeder())->run();
            }
        } catch (\Throwable $e) {}
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->setupTestData();
        
        // Setup backward compatibility aliases for existing tests
        $this->superAdmin = $this->testSuperAdmin;
        $this->admin = $this->testAdmin;
        $this->user = $this->testUser;
        $this->division = $this->testDivision;
        $this->location = $this->testLocation;
        $this->assetType = $this->testAssetType;
        $this->assetModel = $this->testAssetModel;
        $this->supplier = $this->testSupplier;
        $this->status = $this->testAssetStatus;
        $this->warrantyType = $this->testWarrantyType;
    }

    /** @test */
    public function super_admin_can_create_asset_with_all_required_fields()
    {
        $assetData = [
            'asset_tag' => 'TEST-' . time(),
            'serial_number' => 'SN-' . uniqid(),
            'model_id' => $this->assetModel->id,
            'division_id' => $this->division->id,
            'location_id' => $this->location->id,
            'status_id' => $this->status->id,
            'purchase_date' => now()->format('Y-m-d'),
            'purchase_cost' => 1000.00,
            'asset_type_id' => $this->assetType->id,
            'supplier_id' => $this->supplier->id,
            'warranty_type_id' => $this->warrantyType->id,
        ];

        $response = $this->actingAs($this->superAdmin)
            ->post(route('assets.store'), $assetData);

        $response->assertStatus(302);
        $this->assertDatabaseHas('assets', [
            'asset_tag' => $assetData['asset_tag'],
            'serial_number' => strtoupper($assetData['serial_number']),
        ]);
    }

    /** @test */
    public function admin_can_create_asset()
    {
        $assetData = [
            'asset_tag' => 'ADMIN-TEST-' . time(),
            'serial_number' => 'SN-ADMIN-' . uniqid(),
            'model_id' => $this->assetModel->id,
            'division_id' => $this->division->id,
            'location_id' => $this->location->id,
            'status_id' => $this->status->id,
            'purchase_date' => now()->format('Y-m-d'),
            'purchase_cost' => 500.00,
            'asset_type_id' => $this->assetType->id,
            'supplier_id' => $this->supplier->id,
            'warranty_type_id' => $this->warrantyType->id,
        ];

        $response = $this->actingAs($this->admin)
            ->post(route('assets.store'), $assetData);
        $response->assertStatus(302);
        $this->assertDatabaseHas('assets', [
            'asset_tag' => $assetData['asset_tag'],
        ]);
    }

    /** @test */
    public function regular_user_cannot_create_asset()
    {
        $assetData = [
            'asset_tag' => 'USER-TEST-' . time(),
            'model_id' => $this->assetModel->id,
        ];

        $response = $this->actingAs($this->user)
            ->post(route('assets.store'), $assetData);

        $response->assertStatus(403); // Forbidden
    }

    /** @test */
    public function serial_number_must_be_unique_when_provided()
    {
        // Create first asset with serial number
        $serialNumber = 'UNIQUE-' . uniqid();
        
        $this->createTestAsset([
            'asset_tag' => 'FIRST-' . time(),
            'serial_number' => $serialNumber,
            'model_id' => $this->assetModel->id,
            'division_id' => $this->division->id,
            'location_id' => $this->location->id,
            'status_id' => $this->status->id,
        ]);

        // Try to create second asset with same serial number
        $assetData = [
            'asset_tag' => 'SECOND-' . time(),
            'serial_number' => strtoupper($serialNumber), // Duplicate! (normalized to uppercase to match model mutator)
            'model_id' => $this->assetModel->id,
            'division_id' => $this->division->id,
            'location_id' => $this->location->id,
            'status_id' => $this->status->id,
            'asset_type_id' => $this->assetType->id,
            'supplier_id' => $this->supplier->id,
            'warranty_type_id' => $this->warrantyType->id,
        ];

        $response = $this->actingAs($this->superAdmin)
            ->post(route('assets.store'), $assetData);

        // Should fail validation
        $response->assertSessionHasErrors('serial_number');
    }

    /** @test */
    public function null_serial_numbers_are_allowed()
    {
        $assetData1 = [
            'asset_tag' => 'NULL-SN-1-' . time(),
            'serial_number' => null,
            'model_id' => $this->assetModel->id,
            'division_id' => $this->division->id,
            'location_id' => $this->location->id,
            'status_id' => $this->status->id,
        ];

        $assetData2 = [
            'asset_tag' => 'NULL-SN-2-' . time(),
            'serial_number' => null,
            'model_id' => $this->assetModel->id,
            'division_id' => $this->division->id,
            'location_id' => $this->location->id,
            'status_id' => $this->status->id,
        ];

        // Both should succeed
        $response1 = $this->actingAs($this->superAdmin)
            ->post(route('assets.store'), $assetData1);
        $response1->assertStatus(302);

        $response2 = $this->actingAs($this->superAdmin)
            ->post(route('assets.store'), $assetData2);
        $response2->assertStatus(302);
    }

    /** @test */
    public function can_update_asset_details()
    {
        $asset = $this->createTestAsset([
            'asset_tag' => 'UPDATE-TEST-' . time(),
            'serial_number' => 'SN-UPDATE-' . uniqid(),
            'model_id' => $this->assetModel->id,
            'division_id' => $this->division->id,
            'location_id' => $this->location->id,
            'status_id' => $this->status->id,
        ]);

        $updatedData = [
            'asset_tag' => 'UPDATED-' . time(),
            'serial_number' => 'SN-UPDATED-' . uniqid(),
            'model_id' => $this->assetModel->id,
            'division_id' => $this->division->id,
            'location_id' => $this->location->id,
            'status_id' => $this->status->id,
            'notes' => 'Updated notes for testing',
            'asset_type_id' => $this->assetType->id,
            'supplier_id' => $this->supplier->id,
            'warranty_type_id' => $this->warrantyType->id,
        ];

        $response = $this->actingAs($this->superAdmin)
            ->put(route('assets.update', $asset->id), $updatedData);

        $response->assertRedirect(route('assets.show', $asset->id));
        $this->assertDatabaseHas('assets', [
            'id' => $asset->id,
            'asset_tag' => $updatedData['asset_tag'],
            'notes' => 'Updated notes for testing',
        ]);
    }

    /** @test */
    public function can_assign_asset_to_user()
    {
        $asset = $this->createTestAsset([
            'asset_tag' => 'ASSIGN-TEST-' . time(),
            'model_id' => $this->assetModel->id,
            'division_id' => $this->division->id,
            'location_id' => $this->location->id,
            'status_id' => $this->status->id,
        ]);

        $assignData = [
            'user_id' => $this->user->id,
            'assignment_date' => now()->format('Y-m-d'),
        ];

        $response = $this->actingAs($this->admin)
            ->post(route('assets.assign', $asset->id), $assignData);

        $response->assertRedirect();
        $this->assertDatabaseHas('assets', [
            'id' => $asset->id,
            'assigned_to' => $this->user->id,
        ]);
    }

    /** @test */
    public function can_unassign_asset_from_user()
    {
        $asset = $this->createTestAsset([
            'asset_tag' => 'UNASSIGN-TEST-' . time(),
            'model_id' => $this->assetModel->id,
            'division_id' => $this->division->id,
            'location_id' => $this->location->id,
            'status_id' => $this->status->id,
            'assigned_to' => $this->user->id,
        ]);

        $response = $this->actingAs($this->admin)
            ->post(route('assets.unassign', $asset->id));

        $response->assertRedirect();
        $this->assertDatabaseHas('assets', [
            'id' => $asset->id,
            'assigned_to' => null,
        ]);
    }

    /** @test */
    public function can_soft_delete_asset()
    {
        $asset = $this->createTestAsset([
            'asset_tag' => 'DELETE-TEST-' . time(),
            'model_id' => $this->assetModel->id,
            'division_id' => $this->division->id,
            'location_id' => $this->location->id,
            'status_id' => $this->status->id,
        ]);

        $response = $this->actingAs($this->superAdmin)
            ->delete(route('assets.destroy', $asset->id));

        $response->assertStatus(302);
        
        // Asset should be soft deleted
        $this->assertSoftDeleted('assets', [
            'id' => $asset->id,
        ]);
    }

    /** @test */
    public function regular_user_cannot_delete_asset()
    {
        $asset = $this->createTestAsset([
            'asset_tag' => 'NO-DELETE-' . time(),
            'model_id' => $this->assetModel->id,
            'division_id' => $this->division->id,
            'location_id' => $this->location->id,
            'status_id' => $this->status->id,
        ]);

        $response = $this->actingAs($this->user)
            ->delete(route('assets.destroy', $asset->id));

        $response->assertStatus(403); // Forbidden
        
        // Asset should still exist
        $this->assertDatabaseHas('assets', [
            'id' => $asset->id,
        ]);
    }

    /** @test */
    public function asset_relationships_load_correctly()
    {
        $asset = $this->createTestAsset([
            'asset_tag' => 'RELATIONSHIPS-' . time(),
            'model_id' => $this->assetModel->id,
            'division_id' => $this->division->id,
            'location_id' => $this->location->id,
            'status_id' => $this->status->id,
            'supplier_id' => $this->supplier->id,
            'assigned_to' => $this->user->id,
        ]);

        // Sanity check: the DB should reflect assignment
        $this->assertDatabaseHas('assets', [
            'id' => $asset->id,
            'assigned_to' => $this->user->id,
        ]);

        $loadedAsset = Asset::with(['model', 'division', 'location', 'status', 'supplier', 'assignedUser'])
            ->find($asset->id);

        $this->assertNotNull($loadedAsset->model);
        $this->assertEquals($this->assetModel->id, $loadedAsset->model->id);
        
        $this->assertNotNull($loadedAsset->division);
        $this->assertEquals($this->division->id, $loadedAsset->division->id);
        
        $this->assertNotNull($loadedAsset->location);
        $this->assertEquals($this->location->id, $loadedAsset->location->id);
        
        $this->assertNotNull($loadedAsset->status);
        $this->assertEquals($this->status->id, $loadedAsset->status->id);
        
        $this->assertNotNull($loadedAsset->supplier);
        $this->assertEquals($this->supplier->id, $loadedAsset->supplier->id);
        
        $this->assertNotNull($loadedAsset->assignedUser);
        $this->assertEquals($this->user->id, $loadedAsset->assignedUser->id);
    }

    /** @test */
    public function can_view_asset_details()
    {
        $asset = $this->createTestAsset([
            'asset_tag' => 'VIEW-TEST-' . time(),
            'model_id' => $this->assetModel->id,
            'division_id' => $this->division->id,
            'location_id' => $this->location->id,
            'status_id' => $this->status->id,
            'notes' => 'Test notes for viewing',
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('assets.show', $asset->id));

        $response->assertStatus(200);
        $response->assertSee($asset->asset_tag);
        $response->assertSee('Test notes for viewing');
    }

    /** @test */
    public function asset_list_displays_with_filters()
    {
        // Create multiple assets
        $this->createTestAsset([
            'asset_tag' => 'FILTER-1-' . time(),
            'model_id' => $this->assetModel->id,
            'division_id' => $this->division->id,
            'location_id' => $this->location->id,
            'status_id' => $this->status->id,
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('assets.index'));

        $response->assertStatus(200);
        $response->assertSee('Assets');
        $response->assertSee('FILTER-1');
    }

    /** @test */
    public function required_fields_validation_works()
    {
        $invalidData = [
            'asset_tag' => '', // Required
            'model_id' => null, // Required
        ];

        $response = $this->actingAs($this->superAdmin)
            ->post(route('assets.store'), $invalidData);

        $response->assertSessionHasErrors(['asset_tag', 'model_id']);
    }
}
