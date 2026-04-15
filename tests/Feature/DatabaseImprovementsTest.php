<?php

namespace Tests\Feature;

use Tests\TestCase;
use Tests\Traits\TestDataHelper;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use App\Asset;
use App\AssetRequest;
use App\AssetType;
use App\Ticket;
use App\PurchaseOrder;
use App\Supplier;
use App\User;
use App\AssetModel;
use App\Status;
use App\Division;
use App\TicketsStatus;
use App\TicketsPriority;
use App\TicketsType;
use App\Location;
use App\TicketHistory;
use App\WarrantyType;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DatabaseImprovementsTest extends TestCase
{
    use RefreshDatabase, WithFaker, TestDataHelper;

    protected $admin;
    protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Setup all test data using helper trait
        $this->setupTestData();
        
        // Use test admin/user from helper
        $this->admin = $this->testAdmin;
        $this->user = $this->testUser;
    }

    /**
     * Test: Assets table has unique constraint on serial_number
     * Task #1: Add UNIQUE constraint to `assets.serial_number`
     */
    public function test_serial_number_unique_constraint_enforced()
    {
        $this->actingAs($this->admin);

        // Create first asset with serial number using helper
        $asset1 = $this->createTestAsset([
            'asset_tag' => 'TEST-001',
            'serial_number' => 'SERIAL12345',
            'purchase_date' => now(),
        ]);

        $this->assertDatabaseHas('assets', [
            'serial_number' => 'SERIAL12345'
        ]);

        // Try to create second asset with same serial number - should fail
        $this->expectException(\Illuminate\Database\QueryException::class);
        
        $this->createTestAsset([
            'asset_tag' => 'TEST-002',
            'serial_number' => 'SERIAL12345', // Duplicate serial
            'purchase_date' => now(),
        ]);
    }

    /**
     * Test: Purchase Orders table exists and relationships work
     * Task #2: Add `purchase_orders` table + `assets.purchase_order_id`
     */
    public function test_purchase_orders_table_and_relationships()
    {
        $this->actingAs($this->admin);

        // Create a supplier
        $supplier = Supplier::first() ?? Supplier::factory()->create();

        // Create a purchase order
        $po = PurchaseOrder::create([
            'po_number' => 'PO-2025-001',
            'supplier_id' => $supplier->id,
            'order_date' => now(),
            'total_cost' => 50000000, // 50 million IDR
        ]);

        $this->assertDatabaseHas('purchase_orders', [
            'po_number' => 'PO-2025-001',
            'supplier_id' => $supplier->id,
        ]);

        // Create asset linked to purchase order using helper
        $asset = $this->createTestAsset([
            'asset_tag' => 'TEST-PO-001',
            'serial_number' => 'SN-PO-12345',
            'purchase_order_id' => $po->id,
            'purchase_date' => now(),
        ]);

        // Test relationships
        $this->assertEquals($po->id, $asset->purchaseOrder->id);
        $this->assertTrue($po->assets->contains($asset));
    }

    /**
     * Test: ticket_assets pivot table (many-to-many)
     * Task #4: ticket_assets pivot + migration plan
     */
    public function test_ticket_assets_many_to_many_relationship()
    {
        $this->actingAs($this->admin);

        // Create test data
        $location = Location::first() ?? Location::factory()->create();
        $status = TicketsStatus::first() ?? TicketsStatus::factory()->create();
        $priority = TicketsPriority::first() ?? TicketsPriority::factory()->create();
        $type = TicketsType::first() ?? TicketsType::factory()->create();

        $ticket = Ticket::create([
            'user_id' => $this->user->id,
            'location_id' => $location->id,
            'ticket_status_id' => $status->id,
            'ticket_priority_id' => $priority->id,
            'ticket_type_id' => $type->id,
            'subject' => 'Test Multiple Assets',
            'description' => 'Testing many-to-many relationship',
        ]);

        // Create multiple assets using helper
        $asset1 = $this->createTestAsset([
            'asset_tag' => 'MULTI-001',
            'serial_number' => 'SN-MULTI-001',
            'purchase_date' => now(),
        ]);

        $asset2 = $this->createTestAsset([
            'asset_tag' => 'MULTI-002',
            'serial_number' => 'SN-MULTI-002',
            'purchase_date' => now(),
        ]);

        $asset3 = $this->createTestAsset([
            'asset_tag' => 'MULTI-003',
            'serial_number' => 'SN-MULTI-003',
            'purchase_date' => now(),
        ]);

        // Attach multiple assets to ticket
        $ticket->assets()->attach([$asset1->id, $asset2->id, $asset3->id]);

        // Verify pivot table
        $this->assertDatabaseHas('ticket_assets', [
            'ticket_id' => $ticket->id,
            'asset_id' => $asset1->id,
        ]);

        $this->assertDatabaseHas('ticket_assets', [
            'ticket_id' => $ticket->id,
            'asset_id' => $asset2->id,
        ]);

        // Test relationships
        $this->assertEquals(3, $ticket->assets()->count());
        $this->assertTrue($ticket->assets->contains($asset1));
        $this->assertTrue($ticket->assets->contains($asset2));
        $this->assertTrue($ticket->assets->contains($asset3));

        // Test reverse relationship
        $this->assertTrue($asset1->tickets->contains($ticket));
    }

    /**
     * Test: ticket_history immutable audit log
     * Task #5: ticket_history (immutable audit log)
     */
    public function test_ticket_history_logs_changes()
    {
        $this->actingAs($this->admin);

        // Create a ticket
        $location = Location::first() ?? Location::factory()->create();
        $status1 = TicketsStatus::first() ?? TicketsStatus::factory()->create(['status' => 'Open']);
        $status2 = TicketsStatus::skip(1)->first() ?? TicketsStatus::factory()->create(['status' => 'In Progress']);
        $priority = TicketsPriority::first() ?? TicketsPriority::factory()->create();
        $type = TicketsType::first() ?? TicketsType::factory()->create();

        $ticket = Ticket::create([
            'user_id' => $this->user->id,
            'location_id' => $location->id,
            'ticket_status_id' => $status1->id,
            'ticket_priority_id' => $priority->id,
            'ticket_type_id' => $type->id,
            'subject' => 'Test History Logging',
            'description' => 'Testing ticket history',
        ]);

        // Update ticket status
        $ticket->update([
            'ticket_status_id' => $status2->id,
        ]);

        // Check if change was logged
        $this->assertDatabaseHas('ticket_history', [
            'ticket_id' => $ticket->id,
            'field_changed' => 'ticket_status_id',
            'old_value' => (string)$status1->id,
            'new_value' => (string)$status2->id,
        ]);

        // Test relationship
        $this->assertGreaterThan(0, $ticket->history()->count());
    }

    /**
     * Test: ticket_history is immutable (cannot update or delete)
     */
    public function test_ticket_history_is_immutable()
    {
        $this->actingAs($this->admin);

        $location = Location::first() ?? Location::factory()->create();
        $status = TicketsStatus::first() ?? TicketsStatus::factory()->create();
        $priority = TicketsPriority::first() ?? TicketsPriority::factory()->create();
        $type = TicketsType::first() ?? TicketsType::factory()->create();

        $ticket = Ticket::create([
            'user_id' => $this->user->id,
            'location_id' => $location->id,
            'ticket_status_id' => $status->id,
            'ticket_priority_id' => $priority->id,
            'ticket_type_id' => $type->id,
            'subject' => 'Test Immutability',
            'description' => 'Testing immutable history',
        ]);

        // Create a history record
        $history = TicketHistory::create([
            'ticket_id' => $ticket->id,
            'field_changed' => 'test_field',
            'old_value' => 'old',
            'new_value' => 'new',
            'changed_by_user_id' => $this->admin->id,
            'changed_at' => now(),
            'change_type' => 'test',
            'event_type' => 'test_event',
        ]);

        // Try to update - should throw exception
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('TicketHistory is immutable - cannot update');
        $history->update(['old_value' => 'modified']);
    }

    /**
     * Test: asset_requests has request_number
     * Task #11: Asset Requests: add `request_number`
     */
    public function test_asset_request_has_request_number()
    {
        $this->actingAs($this->user);

        $assetType = AssetType::first() ?? AssetType::factory()->create();

        $assetRequest = AssetRequest::create([
            'user_id' => $this->user->id,
            'asset_type_id' => $assetType->id,
            'request_type' => 'New',
            'description' => 'Need new laptop',
            'justification' => 'Current laptop is broken',
            'status' => 'pending',
        ]);

        // Check that request_number was generated
        $this->assertNotNull($assetRequest->request_number);
        $this->assertStringStartsWith('AR-', $assetRequest->request_number);
        
        // Check format: AR-YYYY-NNNN
        $pattern = '/^AR-\d{4}-\d{4}$/';
        $this->assertMatchesRegularExpression($pattern, $assetRequest->request_number);
    }

    /**
     * Test: Asset request workflow - from request to fulfillment
     * Integration test for the complete workflow
     */
    public function test_asset_request_to_fulfillment_workflow()
    {
        $this->actingAs($this->admin);

        // Create a supplier and purchase order
        $supplier = Supplier::first() ?? Supplier::factory()->create();
        $po = PurchaseOrder::create([
            'po_number' => 'PO-2025-WORKFLOW',
            'supplier_id' => $supplier->id,
            'order_date' => now(),
            'total_cost' => 25000000,
        ]);

        // User creates asset request
        $assetRequest = AssetRequest::create([
            'user_id' => $this->user->id,
            'request_type' => 'New',
            'description' => 'Need new laptop for work',
            'justification' => 'Remote work requirements',
            'status' => 'Pending',
        ]);

        // Admin approves request
        $assetRequest->update([
            'status' => 'Approved',
            'approved_by' => $this->admin->id,
            'approved_at' => now(),
        ]);

        // Create asset (linked to PO and request)
        $model = AssetModel::first() ?? AssetModel::factory()->create();
        // Create asset using helper
        $asset = $this->createTestAsset([
            'asset_tag' => 'WORKFLOW-001',
            'serial_number' => 'SN-WORKFLOW-001',
            'purchase_order_id' => $po->id,
            'assigned_to' => $this->user->id,
            'purchase_date' => now(),
        ]);

        // Link fulfilled asset to request
        $assetRequest->update([
            'fulfilled_asset_id' => $asset->id,
            'status' => 'Fulfilled',
        ]);

        // Verify complete workflow
        $this->assertEquals('Fulfilled', $assetRequest->status);
        $this->assertEquals($asset->id, $assetRequest->fulfilled_asset_id);
        $this->assertEquals($po->id, $asset->purchase_order_id);
        $this->assertEquals($this->user->id, $asset->assigned_to);

        // Verify audit trail
        $this->assertNotNull($assetRequest->request_number);
        $this->assertNotNull($assetRequest->approved_by);
        $this->assertNotNull($assetRequest->approved_at);
    }

    /**
     * Test: TCO calculation - combining purchase cost and support costs
     * Demonstrates the value of linking assets to POs and tickets
     */
    public function test_total_cost_of_ownership_calculation()
    {
        $this->actingAs($this->admin);

        // Create purchase order with known cost
        $supplier = Supplier::first() ?? Supplier::factory()->create();
        $po = PurchaseOrder::create([
            'po_number' => 'PO-TCO-TEST',
            'supplier_id' => $supplier->id,
            'order_date' => now(),
            'total_cost' => 15000000, // Initial purchase: 15M IDR
        ]);

        // Create asset
        $model = AssetModel::first() ?? AssetModel::factory()->create();
        $status = Status::first() ?? Status::factory()->create();
        $division = Division::first() ?? Division::factory()->create();

        $asset = $this->createTestAsset([
            'asset_tag' => 'TCO-001',
            'serial_number' => 'SN-TCO-001',
            'purchase_order_id' => $po->id,
            'purchase_date' => now(),
        ]);

        // Create tickets for this asset (representing support costs)
        $location = Location::first() ?? Location::factory()->create();
        $ticketStatus = TicketsStatus::first() ?? TicketsStatus::factory()->create();
        $priority = TicketsPriority::first() ?? TicketsPriority::factory()->create();
        $type = TicketsType::first() ?? TicketsType::factory()->create();

        $ticket1 = Ticket::create([
            'user_id' => $this->user->id,
            'location_id' => $location->id,
            'ticket_status_id' => $ticketStatus->id,
            'ticket_priority_id' => $priority->id,
            'ticket_type_id' => $type->id,
            'subject' => 'Hardware repair',
            'description' => 'Screen replacement needed',
        ]);
        $ticket1->assets()->attach($asset->id);

        $ticket2 = Ticket::create([
            'user_id' => $this->user->id,
            'location_id' => $location->id,
            'ticket_status_id' => $ticketStatus->id,
            'ticket_priority_id' => $priority->id,
            'ticket_type_id' => $type->id,
            'subject' => 'Software issue',
            'description' => 'OS reinstallation',
        ]);
        $ticket2->assets()->attach($asset->id);

        // Calculate TCO
        $purchaseCost = $po->total_cost;
        $ticketCount = $asset->tickets()->count();
        $estimatedSupportCostPerTicket = 500000; // 500K IDR per ticket
        $totalSupportCost = $ticketCount * $estimatedSupportCostPerTicket;
        $tco = $purchaseCost + $totalSupportCost;

        // Verify TCO components
        $this->assertEquals(15000000, $purchaseCost);
        $this->assertEquals(2, $ticketCount);
        $this->assertEquals(1000000, $totalSupportCost);
        $this->assertEquals(16000000, $tco);
    }

    /**
     * Test: Detect duplicate serials before applying unique constraint
     * This simulates the detection command that should be run before migration
     */
    public function test_detect_duplicate_serials()
    {
        // This test verifies the query logic for detecting duplicates
        // In practice, the unique constraint should already be applied
        
        $model = AssetModel::first() ?? AssetModel::factory()->create();
        $status = Status::first() ?? Status::factory()->create();
        $division = Division::first() ?? Division::factory()->create();

        // Create assets with unique serials using helper
        $asset1 = $this->createTestAsset([
            'asset_tag' => 'DUP-001',
            'serial_number' => 'UNIQUE123',
            'purchase_date' => now(),
        ]);

        $asset2 = $this->createTestAsset([
            'asset_tag' => 'DUP-002',
            'serial_number' => 'UNIQUE456',
            'purchase_date' => now(),
        ]);

        // Query for duplicates (simulating detection command)
        $duplicates = DB::table('assets')
            ->select('serial_number', DB::raw('COUNT(*) as count'))
            ->whereNotNull('serial_number')
            ->where('serial_number', '!=', '')
            ->groupBy('serial_number')
            ->having('count', '>', 1)
            ->get();

        // Should find no duplicates
        $this->assertEquals(0, $duplicates->count());
    }
}
