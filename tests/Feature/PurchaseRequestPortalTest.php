<?php

namespace Tests\Feature;

use App\AssetRequest;
use App\AssetType;
use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PurchaseRequestPortalTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login_when_opening_purchase_request_portal(): void
    {
        $response = $this->get('/purchase-requests');

        $response->assertRedirect('/login');
    }

    public function test_authenticated_user_can_open_purchase_request_portal(): void
    {
        $user = User::factory()->create();
        $this->assignRoleSafely($user, 'user');

        $response = $this->actingAs($user)->get('/purchase-requests');

        $response->assertStatus(200);
        $response->assertSee('Purchase Request Module');
        $response->assertSee('Status Breakdown');
    }

    public function test_standard_user_only_sees_own_requests_and_no_approval_action(): void
    {
        $assetType = AssetType::factory()->create();

        $owner = User::factory()->create();
        $otherUser = User::factory()->create();

        $this->assignRoleSafely($owner, 'user');
        $this->assignRoleSafely($otherUser, 'user');

        AssetRequest::factory()->create([
            'requested_by' => $owner->id,
            'asset_type_id' => $assetType->id,
            'request_number' => 'AR-OWN-0001',
            'status' => 'pending',
        ]);

        AssetRequest::factory()->create([
            'requested_by' => $otherUser->id,
            'asset_type_id' => $assetType->id,
            'request_number' => 'AR-OTH-0001',
            'status' => 'approved',
        ]);

        $response = $this->actingAs($owner)->get('/purchase-requests');

        $response->assertStatus(200);
        $response->assertSee('AR-OWN-0001');
        $response->assertDontSee('AR-OTH-0001');
        $response->assertDontSee('Review Pending Approvals');
    }

    public function test_admin_sees_approval_action_and_all_requests(): void
    {
        $assetType = AssetType::factory()->create();

        $admin = User::factory()->create();
        $requester = User::factory()->create();

        $this->assignRoleSafely($admin, 'admin');
        $this->assignRoleSafely($requester, 'user');

        AssetRequest::factory()->create([
            'requested_by' => $requester->id,
            'asset_type_id' => $assetType->id,
            'request_number' => 'AR-REQ-0001',
            'status' => 'pending',
        ]);

        AssetRequest::factory()->create([
            'requested_by' => $admin->id,
            'asset_type_id' => $assetType->id,
            'request_number' => 'AR-ADM-0001',
            'status' => 'approved',
        ]);

        $response = $this->actingAs($admin)->get('/purchase-requests');

        $response->assertStatus(200);
        $response->assertSee('Review Pending Approvals');
        $response->assertSee('AR-REQ-0001');
        $response->assertSee('AR-ADM-0001');
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
