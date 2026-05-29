<?php

namespace App\Repositories\Dashboard;

use App\InventoryItem;
use App\InventoryRequest;
use App\MeetingRoomBooking;
use App\VehicleBooking;
use Illuminate\Support\Collection;

class DashboardRepository
{
    public function getDashboardStats(): array
    {
        return [
            'open_tickets' => 0,
            'overdue_tickets' => 0,
            'meeting_requests' => MeetingRoomBooking::query()->where('status', 'pending')->count(),
            'inventory_requests' => InventoryRequest::query()->where('status', 'pending')->count(),
            'vehicle_requests' => VehicleBooking::query()->where('status', 'pending')->count(),
            'total_inventory_items' => InventoryItem::query()->count(),
        ];
    }

    public function getRecentItems(int $limit = 5): Collection
    {
        return collect()
            ->merge(MeetingRoomBooking::query()->latest('start_datetime')->limit($limit)->get())
            ->merge(InventoryRequest::query()->latest()->limit($limit)->get())
            ->merge(VehicleBooking::query()->latest('start_datetime')->limit($limit)->get())
            ->sortByDesc(function ($item) {
                return $item->created_at ?? $item->start_datetime ?? now()->subYear();
            })
            ->values()
            ->take($limit);
    }
}