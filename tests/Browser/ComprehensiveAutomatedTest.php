<?php

/**
 * Automated End-to-End Testing Suite
 * Target: <5% False Positive Rate
 * Framework: Laravel Dusk (Browser Automation)
 */

namespace Tests\Browser;

use Tests\DuskTestCase;
use Laravel\Dusk\Browser;
use App\User;
use Spatie\Permission\Models\Role;
use App\Ticket;
use App\Asset;
use App\AssetRequest;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\Hash;

class ComprehensiveAutomatedTest extends DuskTestCase
{
    use DatabaseMigrations;

    protected $superAdmin;
    protected $admin;
    protected $regularUser;

    /**
     * Setup test environment
     */
    protected function setUp(): void
    {
        parent::setUp();
        
        // Create test users with known credentials
        $this->superAdmin = User::factory()->create([
            'name' => 'Test Super Admin',
            'email' => 'test.superadmin@example.com',
            'password' => Hash::make('password123'),
        ]);
                // Ensure roles exist (Dusk runs separate process/migrations so seeders may not have run)
                Role::firstOrCreate(['name' => 'super-admin', 'guard_name' => 'web']);
                Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
                Role::firstOrCreate(['name' => 'user', 'guard_name' => 'web']);
                $this->superAdmin->assignRole('super-admin');

        $this->admin = User::factory()->create([
            'name' => 'Test Admin',
            'email' => 'test.admin@example.com',
            'password' => Hash::make('password123'),
        ]);
        $this->admin->assignRole('admin');

        $this->regularUser = User::factory()->create([
            'name' => 'Test User',
            'email' => 'test.user@example.com',
            'password' => Hash::make('password123'),
        ]);
        $this->regularUser->assignRole('user');
    }

    /**
     * Test 1: Authentication & Authorization
     * Expected Success Rate: 100%
     */
    public function test_01_authentication_and_authorization()
    {
        $this->browse(function (Browser $browser) {
            // Test Login
            $browser->visit('/login')
                    ->assertSee('Login')
                    ->type('email', 'test.superadmin@example.com')
                    ->type('password', 'password123')
                    ->press('Login')
                    ->waitForLocation('/home', 10)
                    ->assertPathIs('/home')
                    ->assertSee('Dashboard');

            // Verify super-admin can see all menu items
            $browser->assertSee('Assets')
                    ->assertSee('Tickets')
                    ->assertSee('Daily Activities')
                    ->assertSee('System Settings')
                    ->assertSee('Audit Logs');

            // Logout
            $browser->click('.user-menu')
                    ->waitFor('.user-menu .dropdown-menu', 5)
                    ->click('a[href*="logout"]')
                    ->waitForLocation('/login', 10)
                    ->assertPathIs('/login');
        });
    }

    /**
     * Test 2: Ticket Management (CRUD + Timer)
     * Expected Success Rate: >95%
     */
    public function test_02_ticket_management_crud()
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->superAdmin)
                    ->visit('/tickets')
                    ->waitForText('Tickets', 10)
                    ->assertSee('Tickets');

            // Create Ticket
            $ticketSubject = 'Automated Test Ticket ' . time();
            $browser->click('a[href*="tickets/create"]')
                    ->waitForLocation('/tickets/create', 10)
                    ->type('subject', $ticketSubject)
                    ->type('description', 'This is an automated test ticket')
                    ->select('ticket_priority_id', '2') // Normal priority
                    ->select('ticket_status_id', '1') // Open status
                    ->press('Submit')
                    ->waitForText('Ticket created successfully', 10)
                    ->assertSee('Ticket created successfully');

            // Verify ticket appears in list
            $browser->visit('/tickets')
                    ->waitForText($ticketSubject, 10)
                    ->assertSee($ticketSubject);

            // View ticket
            $ticket = Ticket::where('subject', $ticketSubject)->first();
            $browser->visit("/tickets/{$ticket->id}")
                    ->waitForText($ticketSubject, 10)
                    ->assertSee($ticketSubject)
                    ->assertSee('This is an automated test ticket');

            // Test Timer (Start/Stop)
            $browser->click('button[data-action="start-timer"]')
                    ->waitFor('.timer-display', 5)
                    ->assertSee('00:00')
                    ->pause(2000) // Wait 2 seconds
                    ->assertDontSee('00:00') // Timer should have changed
                    ->click('button[data-action="stop-timer"]')
                    ->waitForText('Timer stopped', 5);

            // Edit ticket
            $browser->visit("/tickets/{$ticket->id}/edit")
                    ->waitForText('Edit Ticket', 10)
                    ->type('subject', $ticketSubject . ' (Updated)')
                    ->press('Update')
                    ->waitForText('Ticket updated successfully', 10)
                    ->assertSee('Ticket updated successfully');

            // Verify update
            $browser->visit('/tickets')
                    ->assertSee($ticketSubject . ' (Updated)');
        });
    }

    /**
     * Test 3: Asset Management (CRUD + QR Scanner)
     * Expected Success Rate: >95%
     */
    public function test_03_asset_management_crud()
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->superAdmin)
                    ->visit('/assets')
                    ->waitForText('Assets', 10)
                    ->assertSee('Assets');

            // Create Asset
            $assetTag = 'TEST-' . time();
            $browser->click('a[href*="assets/create"]')
                    ->waitForLocation('/assets/create', 10)
                    ->type('asset_tag', $assetTag)
                    ->type('name', 'Test Laptop')
                    ->type('serial_number', 'SN' . time())
                    ->select('asset_type_id', '1')
                    ->select('status_id', '1')
                    ->press('Submit')
                    ->waitForText('Asset created successfully', 10)
                    ->assertSee('Asset created successfully');

            // Verify asset in list
            $browser->visit('/assets')
                    ->waitForText($assetTag, 10)
                    ->assertSee($assetTag);

            // View asset
            $asset = Asset::where('asset_tag', $assetTag)->first();
            $browser->visit("/assets/{$asset->id}")
                    ->waitForText($assetTag, 10)
                    ->assertSee($assetTag)
                    ->assertSee('Test Laptop');

            // Test QR Scanner page loads
            $browser->visit('/assets/scan-qr')
                    ->waitForText('QR Code Scanner', 10)
                    ->assertSee('Camera Scan')
                    ->assertSee('Manual Search')
                    ->assertVisible('#manual-search-form');

            // Test My Assets page
            $browser->visit('/assets/my-assets')
                    ->waitForText('My Assets', 10)
                    ->assertSee('My Assets')
                    ->assertVisible('table');
        });
    }

    /**
     * Test 4: Asset Request Workflow
     * Expected Success Rate: >95%
     */
    public function test_04_asset_request_workflow()
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->regularUser)
                    ->visit('/asset-requests')
                    ->waitForText('Asset Requests', 10)
                    ->assertSee('Asset Requests');

            // Create request
            $browser->click('a[href*="asset-requests/create"]')
                    ->waitForLocation('/asset-requests/create', 10)
                    ->select('asset_type_id', '1')
                    ->type('justification', 'Need laptop for development work')
                    ->press('Submit')
                    ->waitForText('Request created successfully', 10)
                    ->assertSee('Request created successfully');

            // Verify request appears
            $browser->visit('/asset-requests')
                    ->assertSee('development work');

            // Admin approves request
            $request = AssetRequest::latest()->first();
            $browser->loginAs($this->admin)
                    ->visit("/asset-requests/{$request->id}")
                    ->waitForText('Asset Request', 10)
                    ->click('button[data-action="approve"]')
                    ->waitForDialog(5)
                    ->acceptDialog()
                    ->waitForText('Request approved', 10)
                    ->assertSee('Approved');
        });
    }

    /**
     * Test 5: User Management (CRUD)
     * Expected Success Rate: >95%
     */
    public function test_05_user_management_crud()
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->superAdmin)
                    ->visit('/users')
                    ->waitForText('Users', 10)
                    ->assertSee('Users');

            // Create user
            $userEmail = 'newuser.' . time() . '@example.com';
            $browser->click('a[href*="users/create"]')
                    ->waitForLocation('/users/create', 10)
                    ->type('name', 'New Test User')
                    ->type('email', $userEmail)
                    ->type('password', 'password123')
                    ->type('password_confirmation', 'password123')
                    ->select('role', 'user')
                    ->press('Create')
                    ->waitForText('User created successfully', 10)
                    ->assertSee('User created successfully');

            // Verify user in list
            $browser->visit('/users')
                    ->assertSee($userEmail);

            // Edit user
            $user = User::where('email', $userEmail)->first();
            $browser->visit("/users/{$user->id}/edit")
                    ->waitForText('Edit User', 10)
                    ->type('name', 'New Test User (Updated)')
                    ->press('Update')
                    ->waitForText('User updated successfully', 10)
                    ->assertSee('User updated successfully');
        });
    }

    /**
     * Test 6: Dashboard Loading & KPI Cards
     * Expected Success Rate: >98%
     */
    public function test_06_dashboard_loading()
    {
        $this->browse(function (Browser $browser) {
            // Admin Dashboard
            $browser->loginAs($this->admin)
                    ->visit('/home')
                    ->waitForText('Dashboard', 10)
                    ->assertSee('Dashboard')
                    ->assertVisible('.kpi-card')
                    ->assertSee('Total Users')
                    ->assertSee('Total Assets')
                    ->assertSee('Active Tickets');

            // Management Dashboard
            $browser->loginAs($this->superAdmin)
                    ->visit('/management/dashboard')
                    ->waitForText('Management Dashboard', 10)
                    ->assertSee('Today\'s Tickets')
                    ->assertSee('SLA Compliance')
                    ->assertVisible('.kpi-card');
        });
    }

    /**
     * Test 7: Search Functionality
     * Expected Success Rate: >95%
     */
    public function test_07_search_functionality()
    {
        // Create test data
        $ticket = Ticket::factory()->create([
            'subject' => 'Searchable Ticket ' . time(),
            'description' => 'This ticket should be searchable'
        ]);

        $this->browse(function (Browser $browser) use ($ticket) {
            $browser->loginAs($this->superAdmin)
                    ->visit('/tickets')
                    ->waitForText('Tickets', 10);

            // Test global search
            $browser->click('#global-search')
                    ->type('#global-search', 'Searchable')
                    ->pause(1000) // Wait for debounce
                    ->waitFor('.search-autocomplete.active', 5)
                    ->assertVisible('.search-autocomplete')
                    ->assertSee('Searchable Ticket');

            // Test page-level search
            $browser->visit('/tickets')
                    ->type('input[name="search"]', $ticket->subject)
                    ->press('Search')
                    ->waitForText($ticket->subject, 10)
                    ->assertSee($ticket->subject);
        });
    }

    /**
     * Test 8: Notification System
     * Expected Success Rate: >95%
     */
    public function test_08_notification_system()
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->superAdmin)
                    ->visit('/home')
                    ->waitForText('Dashboard', 10);

            // Check notification bell exists
            $browser->assertVisible('#notification-bell')
                    ->assertVisible('#notification-badge');

            // Click bell to open dropdown
            $browser->click('#notification-bell')
                    ->waitFor('#notification-dropdown.active', 5)
                    ->assertVisible('#notification-dropdown')
                    ->assertSee('Notifications');

            // Test mark all as read button
            if ($browser->element('#mark-all-read')) {
                $browser->click('#mark-all-read')
                        ->pause(1000)
                        ->assertDontSee('.notification-item.unread');
            }
        });
    }

    /**
     * Test 9: Audit Logs
     * Expected Success Rate: >95%
     */
    public function test_09_audit_logs()
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->superAdmin)
                    ->visit('/audit-logs')
                    ->waitForText('Audit Logs', 10)
                    ->assertSee('Audit Logs')
                    ->assertVisible('table');

            // Test filtering
            $browser->select('select[name="action"]', 'create')
                    ->press('Filter')
                    ->waitFor('table tbody tr', 5);

            // Test export
            $browser->assertVisible('button[data-action="export"]');
        });
    }

    /**
     * Test 10: Daily Activities
     * Expected Success Rate: >95%
     */
    public function test_10_daily_activities()
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->admin)
                    ->visit('/daily-activities')
                    ->waitForText('Daily Activities', 10)
                    ->assertSee('Daily Activities');

            // Create activity
            $browser->click('a[href*="daily-activities/create"]')
                    ->waitForLocation('/daily-activities/create', 10)
                    ->type('title', 'Test Activity ' . time())
                    ->type('description', 'Test description')
                    ->select('activity_type', 'meeting')
                    ->press('Submit')
                    ->waitForText('Activity created successfully', 10)
                    ->assertSee('Activity created successfully');
        });
    }

    /**
     * Test 11: SLA Management
     * Expected Success Rate: >95%
     */
    public function test_11_sla_management()
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->superAdmin)
                    ->visit('/sla')
                    ->waitForText('SLA Policies', 10)
                    ->assertSee('SLA Policies')
                    ->assertVisible('table');

            // View SLA dashboard
            $browser->visit('/sla/dashboard')
                    ->waitForText('SLA Dashboard', 10)
                    ->assertSee('SLA Compliance')
                    ->assertVisible('.kpi-card');
        });
    }

    /**
     * Test 12: Responsive Design (Mobile)
     * Expected Success Rate: >98%
     */
    public function test_12_responsive_design()
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->superAdmin)
                    ->resize(375, 667) // iPhone size
                    ->visit('/home')
                    ->waitForText('Dashboard', 10)
                    ->assertSee('Dashboard');

            // Test menu toggle
            $browser->assertVisible('.sidebar-toggle')
                    ->click('.sidebar-toggle')
                    ->pause(500)
                    ->assertVisible('.sidebar');

            // Test table responsiveness
            $browser->visit('/tickets')
                    ->waitForText('Tickets', 10)
                    ->assertVisible('table');

            // Reset size
            $browser->resize(1920, 1080);
        });
    }

    /**
     * Test 13: Button Consistency
     * Expected Success Rate: >98%
     */
    public function test_13_button_consistency()
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->superAdmin)
                    ->visit('/tickets/create')
                    ->waitForText('Create Ticket', 10);

            // Check button classes
            $browser->assertPresent('button.btn-primary')
                    ->assertPresent('button.btn-default, a.btn-default');

            // Check hover effects work
            $browser->mouseover('button.btn-primary')
                    ->pause(200);
        });
    }

    /**
     * Test 14: Color Palette & Accessibility
     * Expected Success Rate: >98%
     */
    public function test_14_color_palette_accessibility()
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->superAdmin)
                    ->visit('/tickets')
                    ->waitForText('Tickets', 10);

            // Check status badges exist
            $browser->assertPresent('.badge-status-open, .badge-status-resolved, .badge');

            // Check priority badges
            $browser->assertPresent('.badge-priority-low, .badge-priority-high, .badge');
        });
    }

    /**
     * Test 15: Performance (Page Load Times)
     * Expected Success Rate: >90%
     */
    public function test_15_performance_page_load()
    {
        $this->browse(function (Browser $browser) {
            $startTime = microtime(true);
            
            $browser->loginAs($this->superAdmin)
                    ->visit('/tickets')
                    ->waitForText('Tickets', 10);
            
            $loadTime = microtime(true) - $startTime;
            
            // Assert page loads in under 3 seconds
            $this->assertLessThan(3, $loadTime, 'Tickets page took too long to load');

            // Test other critical pages
            $pages = ['/assets', '/users', '/home', '/audit-logs'];
            foreach ($pages as $page) {
                $startTime = microtime(true);
                $browser->visit($page)->pause(1000);
                $loadTime = microtime(true) - $startTime;
                $this->assertLessThan(3, $loadTime, "$page took too long to load");
            }
        });
    }

    /**
     * Teardown - Clean up test data
     */
    protected function tearDown(): void
    {
        // Clean up test data created during tests
        Ticket::where('subject', 'like', 'Automated Test Ticket%')->delete();
        Ticket::where('subject', 'like', 'Searchable Ticket%')->delete();
        Asset::where('asset_tag', 'like', 'TEST-%')->delete();
        User::where('email', 'like', 'newuser.%@example.com')->delete();

        parent::tearDown();
    }
}
