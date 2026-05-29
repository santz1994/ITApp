<?php

namespace App\Repositories\Vehicles;

use App\Vehicle;
use App\VehicleBooking;
use Illuminate\Support\Facades\DB;

class VehicleRepository implements VehicleRepositoryInterface
{
    protected $vehicle;
    protected $booking;

    public function __construct(Vehicle $vehicle, VehicleBooking $booking)
    {
        $this->vehicle = $vehicle;
        $this->booking = $booking;
    }

    public function getAll($filters = [])
    {
        $query = $this->vehicle->query();

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['brand'])) {
            $query->where('brand', 'like', '%' . $filters['brand'] . '%');
        }

        if (isset($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('name', 'like', '%' . $filters['search'] . '%')
                    ->orWhere('plate_number', 'like', '%' . $filters['search'] . '%')
                    ->orWhere('brand', 'like', '%' . $filters['search'] . '%')
                    ->orWhere('model', 'like', '%' . $filters['search'] . '%');
            });
        }

        return $query->orderBy('name')->get();
    }

    public function getById($id)
    {
        return $this->vehicle->with(['bookings' => function ($query) {
            $query->latest()->limit(10);
        }, 'maintenanceLogs' => function ($query) {
            $query->latest()->limit(5);
        }])->findOrFail($id);
    }

    public function create(array $data)
    {
        return $this->vehicle->create($data);
    }

    public function update($id, array $data)
    {
        $vehicle = $this->vehicle->findOrFail($id);
        $vehicle->update($data);
        return $vehicle;
    }

    public function delete($id)
    {
        $vehicle = $this->vehicle->findOrFail($id);
        return $vehicle->delete();
    }

    public function getAvailableVehicles($startDatetime, $endDatetime)
    {
        $bookedVehicleIds = $this->booking
            ->overlapping(null, $startDatetime, $endDatetime)
            ->pluck('vehicle_id')
            ->toArray();

        return $this->vehicle
            ->where('status', 'available')
            ->whereNotIn('id', $bookedVehicleIds)
            ->orderBy('name')
            ->get();
    }

    public function checkAvailability($vehicleId, $startDatetime, $endDatetime, $excludeBookingId = null)
    {
        $query = $this->booking
            ->where('vehicle_id', $vehicleId)
            ->whereIn('status', ['pending', 'approved', 'in_progress'])
            ->where(function ($q) use ($startDatetime, $endDatetime) {
                $q->whereBetween('start_datetime', [$startDatetime, $endDatetime])
                    ->orWhereBetween('end_datetime', [$startDatetime, $endDatetime])
                    ->orWhere(function ($q2) use ($startDatetime, $endDatetime) {
                        $q2->where('start_datetime', '<=', $startDatetime)
                            ->where('end_datetime', '>=', $endDatetime);
                    });
            });

        if ($excludeBookingId) {
            $query->where('id', '!=', $excludeBookingId);
        }

        return $query->count() === 0;
    }
}