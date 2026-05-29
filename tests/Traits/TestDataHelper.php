<?php

namespace Tests\Traits;

use App\User;
use App\Asset;
use App\AssetModel;
use App\AssetType;
use App\Division;
use App\Location;
use App\Supplier;
use App\Status;
use App\Manufacturer;
use App\WarrantyType;

/**
 * Test Data Helper Trait
 * 
 * Centralizes test data creation to eliminate duplication
 * and ensure consistent test data across all test files.
 * 
 * Usage:
 * ```php
 * class MyTest extends TestCase
 * {
 *     use TestDataHelper;
 *     
 *     protected function setUp(): void
 *     {
 *         parent::setUp();
 *         $this->setupTestData();
 *     }
 * }
 * ```
 */
trait TestDataHelper
{
    protected $testSuperAdmin;
    protected $testAdmin;
    protected $testTechnician;
    protected $testUser;
    protected $testDivision;
    protected $testLocation;
    protected $testAssetType;
    protected $testManufacturer;
    protected $testAssetModel;
    protected $testSupplier;
    protected $testWarrantyType;
    protected $testAssetStatus;
    

    /**
     * Setup all common test data
     * Call this in your setUp() method
     */
    protected function setupTestData()
    {
        $this->setupTestUsers();
        $this->setupMasterData();
        $this->setupAssetData();
    }

    /**
     * Setup test users with proper roles
     */
    protected function setupTestUsers()
    {
        $this->testSuperAdmin = User::where('name', 'Super Admin User')->first();
        $this->testAdmin = User::where('name', 'Admin User')->first();
        $this->testTechnician = User::where('name', 'Technician User')->first();
        $this->testUser = User::where('name', 'User User')->first();
    }

    /**
     * Setup master data (Division, Location)
     */
    protected function setupMasterData()
    {
        // Division
        $this->testDivision = Division::firstOrCreate(
            ['name' => 'Test Division'],
            ['description' => 'Division for testing']
        );

        // Location - with all required fields
        $this->testLocation = Location::firstOrCreate(
            ['location_name' => 'Test Location'],
            [
                'building' => 'Test Building',
                'office' => 'Test Office',
                'storeroom' => false
            ]
        );
    }

    /**
     * Setup asset-related data
     */
    protected function setupAssetData()
    {
        // Asset Type - with abbreviation
        $this->testAssetType = AssetType::firstOrCreate(
            ['type_name' => 'Test Hardware Type'],
            ['abbreviation' => 'THT']
        );

        // Manufacturer
        $this->testManufacturer = Manufacturer::firstOrCreate(
            ['name' => 'Test Manufacturer'],
            ['description' => 'Test manufacturer for testing']
        );

        // Asset Model - with manufacturer and asset_type_id
        $this->testAssetModel = AssetModel::firstOrCreate(
            ['asset_model' => 'Test Model'],
            [
                'asset_type_id' => $this->testAssetType->id,
                'manufacturer_id' => $this->testManufacturer->id
            ]
        );

        // Supplier
        $this->testSupplier = Supplier::firstOrCreate(
            ['name' => 'Test Supplier'],
            [
                'contact_person' => 'John Doe',
                'email' => 'supplier@test.com'
            ]
        );

        // Warranty Type
        $this->testWarrantyType = WarrantyType::firstOrCreate(
            ['name' => 'Standard Warranty'],
            ['description' => 'Standard manufacturer warranty']
        );

        // Asset Status
        $this->testAssetStatus = Status::firstOrCreate(
            ['name' => 'Available'],
            ['type' => 'asset']
        );
    }



    /**
     * Create a test asset with all required relationships
     * 
     * @param array $overrides Override default values
     * @return Asset
     */
    protected function createTestAsset(array $overrides = [])
    {
        $defaults = [
            'asset_tag' => 'TEST-' . uniqid(),
            'serial_number' => 'SN-' . uniqid(),
            'model_id' => $this->testAssetModel->id,
            'division_id' => $this->testDivision->id,
            'location_id' => $this->testLocation->id,
            'status_id' => $this->testAssetStatus->id,
            'supplier_id' => $this->testSupplier->id,
            'warranty_type_id' => $this->testWarrantyType->id,
        ];

        return Asset::create(array_merge($defaults, $overrides));
    }

    /**
     * Create a test ticket with all required relationships
     * 
     * @param array $overrides Override default values
     * @return \App\Ticket
     */
    protected function createTestTicket(array $overrides = [])
    {
        $defaults = [
            'subject' => 'Test Ticket ' . time(),
            'description' => 'Test ticket description',
            'priority_id' => $this->testTicketPriority->id,
            'type_id' => $this->testTicketType->id,
            'status_id' => $this->testTicketOpenStatus->id,
            'user_id' => $this->testUser->id,
        ];

        return \App\Ticket::create(array_merge($defaults, $overrides));
    }

    /**
     * Create multiple test assets
     * 
     * @param int $count Number of assets to create
     * @param array $overrides Override default values for all assets
     * @return \Illuminate\Support\Collection
     */
    protected function createTestAssets(int $count = 3, array $overrides = [])
    {
        $assets = collect();
        
        for ($i = 1; $i <= $count; $i++) {
            $assets->push($this->createTestAsset(array_merge([
                'asset_tag' => "TEST-BULK-{$i}-" . time(),
                'serial_number' => "SN-BULK-{$i}-" . uniqid(),
            ], $overrides)));
        }

        return $assets;
    }

    /**
     * Create multiple test tickets
     * 
     * @param int $count Number of tickets to create
     * @param array $overrides Override default values for all tickets
     * @return \Illuminate\Support\Collection
     */
    protected function createTestTickets(int $count = 3, array $overrides = [])
    {
        $tickets = collect();
        
        for ($i = 1; $i <= $count; $i++) {
            $tickets->push($this->createTestTicket(array_merge([
                'subject' => "Test Ticket {$i} " . time(),
            ], $overrides)));
        }

        return $tickets;
    }
}
