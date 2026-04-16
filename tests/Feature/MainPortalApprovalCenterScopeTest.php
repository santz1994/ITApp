<?php

namespace Tests\Feature;

use App\AssetRequest;
use App\AssetType;
use App\Division;
use App\MeetingRoomBooking;
use App\Services\MainPortalService;
use App\Ticket;
use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class MainPortalApprovalCenterScopeTest extends TestCase
{
    use RefreshDatabase;

    public function test_director_approval_center_counts_are_scoped_to_supervised_division(): void
    {
        $divisionA = Division::factory()->create();
        $divisionB = Division::factory()->create();

        $director = User::factory()->create(['division_id' => $divisionA->id]);
        $divisionUser = User::factory()->create(['division_id' => $divisionA->id]);
        $outsideUser = User::factory()->create(['division_id' => $divisionB->id]);

        $this->assignRoleSafely($director, 'director');
        $this->assignRoleSafely($divisionUser, 'user');
        $this->assignRoleSafely($outsideUser, 'user');

        $this->seedApprovalCenterRecords($divisionUser, $outsideUser);

        $portalData = app(MainPortalService::class)->buildPortalData($director);
        $queueByKey = collect($portalData['approvalCenter']['items'] ?? [])->keyBy('key');

        $this->assertTrue((bool) ($portalData['approvalCenter']['enabled'] ?? false));
        $this->assertSame(3, (int) ($portalData['approvalCenter']['total_pending'] ?? 0));
        $this->assertSame(1, (int) ($queueByKey['tickets']['pending_count'] ?? 0));
        $this->assertSame(1, (int) ($queueByKey['meeting']['pending_count'] ?? 0));
        $this->assertSame(1, (int) ($queueByKey['purchase']['pending_count'] ?? 0));
    }

    public function test_admin_approval_center_counts_use_global_scope(): void
    {
        $divisionA = Division::factory()->create();
        $divisionB = Division::factory()->create();

        $admin = User::factory()->create(['division_id' => $divisionA->id]);
        $divisionUser = User::factory()->create(['division_id' => $divisionA->id]);
        $outsideUser = User::factory()->create(['division_id' => $divisionB->id]);

        $this->assignRoleSafely($admin, 'admin');
        $this->assignRoleSafely($divisionUser, 'user');
        $this->assignRoleSafely($outsideUser, 'user');

        $this->seedApprovalCenterRecords($divisionUser, $outsideUser);

        $portalData = app(MainPortalService::class)->buildPortalData($admin);
        $queueByKey = collect($portalData['approvalCenter']['items'] ?? [])->keyBy('key');

        $this->assertTrue((bool) ($portalData['approvalCenter']['enabled'] ?? false));
        $this->assertSame(6, (int) ($portalData['approvalCenter']['total_pending'] ?? 0));
        $this->assertSame(2, (int) ($queueByKey['tickets']['pending_count'] ?? 0));
        $this->assertSame(2, (int) ($queueByKey['meeting']['pending_count'] ?? 0));
        $this->assertSame(2, (int) ($queueByKey['purchase']['pending_count'] ?? 0));
    }

    private function seedApprovalCenterRecords(User $divisionUser, User $outsideUser): void
    {
        Ticket::factory()->create([
            'user_id' => $divisionUser->id,
            'assigned_to' => null,
        ]);

        Ticket::factory()->create([
            'user_id' => $outsideUser->id,
            'assigned_to' => null,
        ]);

        $this->createPendingMeetingBooking($divisionUser, 'Room A');
        $this->createPendingMeetingBooking($outsideUser, 'Room B');

        $assetType = AssetType::factory()->create();

        AssetRequest::factory()->create([
            'requested_by' => $divisionUser->id,
            'asset_type_id' => $assetType->id,
            'status' => 'pending',
        ]);

        AssetRequest::factory()->create([
            'requested_by' => $outsideUser->id,
            'asset_type_id' => $assetType->id,
            'status' => 'pending',
        ]);
    }

    private function createPendingMeetingBooking(User $user, string $roomName): void
    {
        MeetingRoomBooking::query()->create([
            'room_name' => $roomName,
            'user_id' => $user->id,
            'requester_name' => $user->name,
            'department' => 'IT',
            'requester_position' => 'Staff',
            'start_datetime' => now()->addDay(),
            'end_datetime' => now()->addDay()->addHour(),
            'purpose' => 'Approval center scope test',
            'meeting_description' => 'Approval center scope test',
            'meeting_needs' => 'Projector',
            'attendees_count' => 5,
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
