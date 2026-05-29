<?php

namespace App\Repositories\Portal;

use App\ApprovalInstance;
use App\InventoryItem;
use App\InventoryRequest;
use App\MeetingRoomBooking;
use App\Permission;
use App\Role;
use App\User;
use App\VehicleBooking;
use Illuminate\Support\Collection;

class MainPortalRepository
{
    public function getMetricsForUser(User $user): array
    {
        $meetingApprovals = MeetingRoomBooking::query()->where('status', 'pending');
        $inventoryRequests = InventoryRequest::query()->where('status', 'pending');
        $vehicleBookings = VehicleBooking::query()->where('status', 'pending');

        return [
            'open_tickets' => 0,
            'overdue_tickets' => 0,
            'assigned_open_tickets' => 0,
            'unassigned_open_tickets' => 0,
            'total_assets' => InventoryItem::query()->count(),
            'maintenance_due' => 0,
            'pending_meeting_approvals' => $meetingApprovals->count(),
            'pending_requests' => $inventoryRequests->count(),
            'meetings_today' => MeetingRoomBooking::query()->whereDate('start_datetime', today())->count(),
            'upcoming_meetings_7d' => MeetingRoomBooking::query()
                ->whereBetween('start_datetime', [now(), now()->addDays(7)])
                ->count(),
            'approved_requests_month' => InventoryRequest::query()
                ->where('status', 'approved')
                ->whereNotNull('approved_at')
                ->whereMonth('approved_at', now()->month)
                ->whereYear('approved_at', now()->year)
                ->count(),
            'active_users' => User::query()->count(),
            'approval_center_ticket_queue' => 0,
            'approval_center_meeting_queue' => $meetingApprovals->count(),
            'approval_center_purchase_queue' => $inventoryRequests->count(),
            'approval_center_inventory_queue' => $inventoryRequests->count(),
            'approval_center_vehicle_queue' => $vehicleBookings->count(),
            'pending_inventory_requests' => $inventoryRequests->count(),
            'pending_vehicle_bookings' => $vehicleBookings->count(),
            'approved_inventory_requests_month' => InventoryRequest::query()
                ->where('status', 'approved')
                ->whereNotNull('approved_at')
                ->whereMonth('approved_at', now()->month)
                ->whereYear('approved_at', now()->year)
                ->count(),
        ];
    }

    public function getRecentTicketsForUser(User $user): Collection
    {
        return collect();
    }

    public function getTicketStatusBreakdownForUser(User $user): array
    {
        return [
            'open' => 0,
            'in_progress' => 0,
            'resolved' => 0,
        ];
    }

    public function getMeetingStatusBreakdownForUser(User $user): array
    {
        return [
            'pending' => MeetingRoomBooking::query()->where('status', 'pending')->count(),
            'approved' => MeetingRoomBooking::query()->where('status', 'approved')->count(),
            'rejected' => MeetingRoomBooking::query()->where('status', 'rejected')->count(),
        ];
    }

    public function getRecentMeetingBookingsForUser(User $user): Collection
    {
        return MeetingRoomBooking::query()
            ->with(['user', 'approver'])
            ->orderByDesc('start_datetime')
            ->limit(5)
            ->get();
    }

    public function getRecentAssetRequestsForUser(User $user): Collection
    {
        return $this->getRecentInventoryRequestsForUser($user);
    }

    public function getRecentInventoryRequestsForUser(User $user): Collection
    {
        return InventoryRequest::query()
            ->with(['requester', 'approver'])
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();
    }

    public function getRecentVehicleBookingsForUser(User $user): Collection
    {
        return VehicleBooking::query()
            ->with(['requester', 'approver', 'vehicle'])
            ->orderByDesc('start_datetime')
            ->limit(5)
            ->get();
    }

    public function getUserWorkspaceContext(User $user): string
    {
        if (user_has_role($user, 'receptionist')) {
            return 'meeting_room';
        }

        if (user_has_any_role($user, ['administrator', 'developer'])) {
            return 'settings';
        }

        if (user_has_role($user, 'director')) {
            return 'kpi';
        }

        return 'meeting_room';
    }
}