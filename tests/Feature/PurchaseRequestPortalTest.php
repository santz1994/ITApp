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
        $response->assertSee('EN');
        $response->assertSee('ID');
        $response->assertSee('id="prLanguageEnglish"', false);
        $response->assertSee('id="prLanguageIndonesian"', false);
        $response->assertSee('data-i18n="pr.title"', false);
        $response->assertSee('data-i18n="pr.quick_actions.title"', false);
        $response->assertSee('data-i18n="pr.table.title"', false);
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

    public function test_asset_request_create_page_shows_bilingual_toggle_and_runtime_markers(): void
    {
        $user = User::factory()->create();
        $this->assignRoleSafely($user, 'user');

        $response = $this->actingAs($user)->get(route('asset-requests.create'));

        $response->assertStatus(200);
        $response->assertSee('EN');
        $response->assertSee('ID');
        $response->assertSee('id="assetRequestCreateLanguageEnglish"', false);
        $response->assertSee('id="assetRequestCreateLanguageIndonesian"', false);
        $response->assertSee('data-i18n="asset_request.create.form.title"', false);
        $response->assertSee('data-i18n="asset_request.create.section.requester"', false);
        $response->assertSee('data-i18n="asset_request.create.action.submit"', false);
        $response->assertSee("'asset_request.create.runtime.asset_name_required'", false);
        $response->assertSee("'asset_request.create.runtime.justification_required'", false);
    }

    public function test_asset_request_edit_page_shows_bilingual_toggle_and_runtime_markers(): void
    {
        $assetType = AssetType::factory()->create();
        $user = User::factory()->create();
        $this->assignRoleSafely($user, 'user');

        $assetRequest = AssetRequest::factory()->create([
            'requested_by' => $user->id,
            'asset_type_id' => $assetType->id,
            'status' => 'pending',
            'request_number' => 'AR-EDT-0001',
        ]);

        $response = $this->actingAs($user)->get(route('asset-requests.edit', $assetRequest->id));

        $response->assertStatus(200);
        $response->assertSee('EN');
        $response->assertSee('ID');
        $response->assertSee('id="assetRequestEditLanguageEnglish"', false);
        $response->assertSee('id="assetRequestEditLanguageIndonesian"', false);
        $response->assertSee('data-i18n="asset_request.edit.form.title"', false);
        $response->assertSee('data-i18n="asset_request.edit.section.asset"', false);
        $response->assertSee('data-i18n="asset_request.edit.action.submit"', false);
        $response->assertSee("'asset_request.edit.runtime.asset_name_required'", false);
        $response->assertSee("'asset_request.edit.runtime.justification_required'", false);
    }

    public function test_asset_request_show_page_shows_bilingual_toggle_and_runtime_markers(): void
    {
        $assetType = AssetType::factory()->create();
        $user = User::factory()->create();
        $this->assignRoleSafely($user, 'user');

        $assetRequest = AssetRequest::factory()->create([
            'requested_by' => $user->id,
            'asset_type_id' => $assetType->id,
            'status' => 'pending',
            'request_number' => 'AR-SHW-0001',
        ]);

        $response = $this->actingAs($user)->get(route('asset-requests.show', $assetRequest->id));

        $response->assertStatus(200);
        $response->assertSee('EN');
        $response->assertSee('ID');
        $response->assertSee('id="assetRequestShowLanguageEnglish"', false);
        $response->assertSee('id="assetRequestShowLanguageIndonesian"', false);
        $response->assertSee('data-i18n="asset_request.show.form.title_prefix"', false);
        $response->assertSee('data-i18n="asset_request.show.section.admin_actions"', false);
        $response->assertSee("'asset_request.show.runtime.confirm.approve'", false);
        $response->assertSee("'asset_request.show.runtime.confirm.reject'", false);
        $response->assertSee('window.assetRequestShowLabel = getLabel;', false);
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
