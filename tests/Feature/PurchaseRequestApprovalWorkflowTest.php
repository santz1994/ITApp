<?php

namespace Tests\Feature;

use App\AssetRequest;
use App\AssetType;
use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PurchaseRequestApprovalWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_approve_pending_request_with_notes(): void
    {
        $admin = User::factory()->create();
        $this->assignRoleSafely($admin, 'admin');

        $requestItem = AssetRequest::factory()->create([
            'asset_type_id' => AssetType::factory()->create()->id,
            'requested_by' => User::factory()->create()->id,
            'status' => 'pending',
        ]);

        $response = $this->actingAs($admin)->post(route('asset-requests.approve', $requestItem->id), [
            'admin_notes' => 'Approved for procurement execution',
        ]);

        $response->assertStatus(302);
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('asset_requests', [
            'id' => $requestItem->id,
            'status' => 'approved',
            'approved_by' => $admin->id,
            'approval_notes' => 'Approved for procurement execution',
        ]);
    }

    public function test_admin_can_reject_pending_request_with_mandatory_notes(): void
    {
        $admin = User::factory()->create();
        $this->assignRoleSafely($admin, 'admin');

        $requestItem = AssetRequest::factory()->create([
            'asset_type_id' => AssetType::factory()->create()->id,
            'requested_by' => User::factory()->create()->id,
            'status' => 'pending',
        ]);

        $response = $this->actingAs($admin)->post(route('asset-requests.reject', $requestItem->id), [
            'admin_notes' => 'Rejected due to duplicate request',
        ]);

        $response->assertStatus(302);
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('asset_requests', [
            'id' => $requestItem->id,
            'status' => 'rejected',
            'approved_by' => $admin->id,
            'approval_notes' => 'Rejected due to duplicate request',
        ]);
    }

    public function test_admin_can_fulfill_approved_request_and_append_fulfillment_notes(): void
    {
        $admin = User::factory()->create();
        $this->assignRoleSafely($admin, 'admin');

        $requestItem = AssetRequest::factory()->create([
            'asset_type_id' => AssetType::factory()->create()->id,
            'requested_by' => User::factory()->create()->id,
            'status' => 'approved',
            'approved_by' => $admin->id,
            'approved_at' => now()->subHour(),
            'approval_notes' => 'Initial approval note',
        ]);

        $response = $this->actingAs($admin)->post(route('asset-requests.fulfill', $requestItem->id), [
            'fulfillment_notes' => 'Asset has been delivered to requester',
        ]);

        $response->assertStatus(302);
        $response->assertSessionHas('success');

        $requestItem->refresh();

        $this->assertSame('fulfilled', $requestItem->status);
        $this->assertNotNull($requestItem->fulfilled_at);
        $this->assertStringContainsString('Initial approval note', (string) $requestItem->approval_notes);
        $this->assertStringContainsString('Asset has been delivered to requester', (string) $requestItem->approval_notes);
    }

    public function test_admin_cannot_approve_already_fulfilled_request(): void
    {
        $admin = User::factory()->create();
        $this->assignRoleSafely($admin, 'admin');

        $requestItem = AssetRequest::factory()->create([
            'asset_type_id' => AssetType::factory()->create()->id,
            'requested_by' => User::factory()->create()->id,
            'status' => 'fulfilled',
            'fulfilled_at' => now(),
        ]);

        $response = $this->actingAs($admin)->post(route('asset-requests.approve', $requestItem->id), [
            'admin_notes' => 'Approve attempt should be blocked',
        ]);

        $response->assertStatus(302);
        $response->assertSessionHas('error');

        $this->assertDatabaseHas('asset_requests', [
            'id' => $requestItem->id,
            'status' => 'fulfilled',
        ]);
    }

    public function test_admin_cannot_reject_already_fulfilled_request(): void
    {
        $admin = User::factory()->create();
        $this->assignRoleSafely($admin, 'admin');

        $requestItem = AssetRequest::factory()->create([
            'asset_type_id' => AssetType::factory()->create()->id,
            'requested_by' => User::factory()->create()->id,
            'status' => 'fulfilled',
            'fulfilled_at' => now(),
        ]);

        $response = $this->actingAs($admin)->post(route('asset-requests.reject', $requestItem->id), [
            'admin_notes' => 'Reject attempt should be blocked',
        ]);

        $response->assertStatus(302);
        $response->assertSessionHas('error');

        $this->assertDatabaseHas('asset_requests', [
            'id' => $requestItem->id,
            'status' => 'fulfilled',
        ]);
    }

    public function test_admin_cannot_fulfill_rejected_request(): void
    {
        $admin = User::factory()->create();
        $this->assignRoleSafely($admin, 'admin');

        $requestItem = AssetRequest::factory()->create([
            'asset_type_id' => AssetType::factory()->create()->id,
            'requested_by' => User::factory()->create()->id,
            'status' => 'rejected',
            'approved_at' => now(),
            'approved_by' => $admin->id,
        ]);

        $response = $this->actingAs($admin)->post(route('asset-requests.fulfill', $requestItem->id), [
            'fulfillment_notes' => 'Fulfill attempt should be blocked',
        ]);

        $response->assertStatus(302);
        $response->assertSessionHas('error');

        $this->assertDatabaseHas('asset_requests', [
            'id' => $requestItem->id,
            'status' => 'rejected',
        ]);
    }

    public function test_reject_action_requires_admin_notes(): void
    {
        $admin = User::factory()->create();
        $this->assignRoleSafely($admin, 'admin');

        $requestItem = AssetRequest::factory()->create([
            'asset_type_id' => AssetType::factory()->create()->id,
            'requested_by' => User::factory()->create()->id,
            'status' => 'pending',
        ]);

        $response = $this->actingAs($admin)->post(route('asset-requests.reject', $requestItem->id), [
            'admin_notes' => '',
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors('admin_notes');

        $this->assertDatabaseHas('asset_requests', [
            'id' => $requestItem->id,
            'status' => 'pending',
        ]);
    }

    private function assignRoleSafely(User $user, string $roleName): void
    {
        try {
            if (!class_exists(Role::class) || !method_exists($user, 'assignRole')) {
                return;
            }

            Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'web']);
            $user->assignRole($roleName);
        } catch (\Throwable $exception) {
            // Keep tests resilient in case role tables are unavailable.
        }
    }
}
