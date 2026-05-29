<?php

namespace Tests\Traits;

use App\User;
use App\Division;
use App\Location;
use App\Status;

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
    protected $testAssetStatus;
    

    /**
     * Setup all common test data
     * Call this in your setUp() method
     */
    protected function setupTestData()
    {
        $this->setupTestUsers();
        $this->setupMasterData();
        // Asset module removed - skip asset test data setup
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

    // Asset helpers removed (assets module deleted)
}
