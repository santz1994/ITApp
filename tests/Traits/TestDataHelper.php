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
        /**
         * Create a test ticket with all required relationships
         * 
         * @param array $overrides Override default values
         * @return \App\Ticket
         */

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

    // Asset helpers removed (assets module deleted)

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
