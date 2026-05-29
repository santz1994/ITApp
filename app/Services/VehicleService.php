<?php

namespace App\Services;

use App\Repositories\Vehicles\VehicleRepositoryInterface;
use App\VehicleBooking;
use App\VehicleMaintenanceLog;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class VehicleService
{
    protected $vehicleRepo;

    public function __construct(VehicleRepositoryInterface $vehicleRepo)
    {
        $this->vehicleRepo = $vehicleRepo;
    }

    // ========================================
    // VEHICLE CRUD
    // ========================================

    public function getAllVehicles($filters = [])
    {
        return $this->vehicleRepo->getAll($filters);
    }

    public function getVehicle($id)
    {
        return $this->vehicleRepo->getById($id);
    }

    public function createVehicle(array $data)
    {
        return $this->vehicleRepo->create($data);
    }

    public function updateVehicle($id, array $data)
    {
        return $this->vehicleRepo->update($id, $data);
    }

    public function deleteVehicle($id)
    {
        return $this->vehicleRepo->delete($id);
    }

    // ========================================
    // VEHICLE BOOKING
    // ========================================

    public function createBooking(array $data)
    {
        // Check vehicle availability (Concurrency Control - Pessimistic Locking)
        DB::beginTransaction();

        try {
            $isAvailable = $this->vehicleRepo->checkAvailability(
                $data['vehicle_id'],
                $data['start_datetime'],
                $data['end_datetime']
            );

            if (!$isAvailable) {
                throw new \Exception('Kendaraan tidak tersedia pada waktu yang dipilih. Terdapat bentrok jadwal.');
            }

            $data['requested_by'] = Auth::id();
            $data['status'] = 'pending';

            $booking = VehicleBooking::create($data);

            DB::commit();

            return $booking;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function updateBooking($id, array $data)
    {
        $booking = VehicleBooking::findOrFail($id);

        if (!$booking->isPending()) {
            throw new \Exception('Hanya booking dengan status pending yang dapat diubah.');
        }

        // If updating time/vehicle, check availability
        if (isset($data['start_datetime']) || isset($data['end_datetime']) || isset($data['vehicle_id'])) {
            $vehicleId = $data['vehicle_id'] ?? $booking->vehicle_id;
            $startDatetime = $data['start_datetime'] ?? $booking->start_datetime;
            $endDatetime = $data['end_datetime'] ?? $booking->end_datetime;

            $isAvailable = $this->vehicleRepo->checkAvailability(
                $vehicleId,
                $startDatetime,
                $endDatetime,
                $id
            );

            if (!$isAvailable) {
                throw new \Exception('Kendaraan tidak tersedia pada waktu yang dipilih. Terdapat bentrok jadwal.');
            }
        }

        $booking->update($data);
        return $booking;
    }

    public function approveBooking($id, $approverId)
    {
        $booking = VehicleBooking::findOrFail($id);

        if (!$booking->canBeApproved()) {
            throw new \Exception('Booking tidak dapat disetujui. Status saat ini: ' . $booking->status);
        }

        $booking->update([
            'status' => 'approved',
            'approved_by' => $approverId,
            'approved_at' => now(),
        ]);

        return $booking;
    }

    public function rejectBooking($id, $approverId, $reason = null)
    {
        $booking = VehicleBooking::findOrFail($id);

        if (!$booking->isPending()) {
            throw new \Exception('Hanya booking dengan status pending yang dapat ditolak.');
        }

        $booking->update([
            'status' => 'rejected',
            'approved_by' => $approverId,
            'approved_at' => now(),
            'rejection_reason' => $reason,
        ]);

        return $booking;
    }

    public function cancelBooking($id)
    {
        $booking = VehicleBooking::findOrFail($id);

        if (!$booking->canBeCancelled()) {
            throw new \Exception('Booking tidak dapat dibatalkan. Status saat ini: ' . $booking->status);
        }

        $booking->update(['status' => 'cancelled']);

        return $booking;
    }

    public function startTrip($id)
    {
        $booking = VehicleBooking::findOrFail($id);

        if (!$booking->isApproved()) {
            throw new \Exception('Hanya booking yang sudah disetujui yang dapat dimulai.');
        }

        DB::beginTransaction();

        try {
            $booking->update(['status' => 'in_progress']);
            $booking->vehicle->update(['status' => 'in_use']);

            DB::commit();
            return $booking;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function completeTrip($id, array $actualData = [])
    {
        $booking = VehicleBooking::findOrFail($id);

        if (!$booking->isInProgress()) {
            throw new \Exception('Hanya booking yang sedang berjalan yang dapat diselesaikan.');
        }

        DB::beginTransaction();

        try {
            $booking->update(array_merge([
                'status' => 'completed',
            ], $actualData));

            // Update vehicle mileage and status
            if (isset($actualData['actual_distance'])) {
                $vehicle = $booking->vehicle;
                $vehicle->update([
                    'current_mileage' => $vehicle->current_mileage + $actualData['actual_distance'],
                    'status' => 'available',
                ]);
            } else {
                $booking->vehicle->update(['status' => 'available']);
            }

            DB::commit();
            return $booking;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function getBooking($id)
    {
        return VehicleBooking::with(['vehicle', 'requester', 'approver'])->findOrFail($id);
    }

    public function getUserBookings($userId)
    {
        return VehicleBooking::where('requested_by', $userId)
            ->with('vehicle')
            ->latest()
            ->get();
    }

    public function getAllBookings($filters = [])
    {
        $query = VehicleBooking::with(['vehicle', 'requester']);

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['vehicle_id'])) {
            $query->where('vehicle_id', $filters['vehicle_id']);
        }

        if (isset($filters['date'])) {
            $query->forDate($filters['date']);
        }

        return $query->latest()->get();
    }

    public function getAvailableVehicles($startDatetime, $endDatetime)
    {
        return $this->vehicleRepo->getAvailableVehicles($startDatetime, $endDatetime);
    }

    // ========================================
    // MAINTENANCE
    // ========================================

    public function addMaintenanceLog($vehicleId, array $data)
    {
        $data['vehicle_id'] = $vehicleId;
        $data['recorded_by'] = Auth::id();

        $log = VehicleMaintenanceLog::create($data);

        // If vehicle is sent for maintenance, update status
        if (isset($data['set_maintenance_status']) && $data['set_maintenance_status']) {
            $this->vehicleRepo->update($vehicleId, ['status' => 'maintenance']);
        }

        return $log;
    }

    public function getMaintenanceLogs($vehicleId)
    {
        return VehicleMaintenanceLog::where('vehicle_id', $vehicleId)
            ->with('recorder')
            ->latest()
            ->get();
    }

    // ========================================
    // REPORTING
    // ========================================

    public function getVehicleUsageStats($vehicleId, $startDate = null, $endDate = null)
    {
        $query = VehicleBooking::where('vehicle_id', $vehicleId)
            ->where('status', 'completed');

        if ($startDate) {
            $query->where('start_datetime', '>=', $startDate);
        }
        if ($endDate) {
            $query->where('end_datetime', '<=', $endDate);
        }

        $bookings = $query->get();

        return [
            'total_trips' => $bookings->count(),
            'total_distance' => $bookings->sum('actual_distance'),
            'total_fuel_cost' => $bookings->sum('actual_fuel_cost'),
            'total_passengers' => $bookings->sum('passengers'),
        ];
    }
}