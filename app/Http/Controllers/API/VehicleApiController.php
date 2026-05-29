<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Services\VehicleService;
use App\Vehicle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Vehicle Management API Controller
 * 
 * Returns JSON responses for React.js frontend
 * All endpoints require authentication via Sanctum
 */
class VehicleApiController extends Controller
{
    protected $vehicleService;

    public function __construct(VehicleService $vehicleService)
    {
        $this->vehicleService = $vehicleService;
    }

    // ========================================
    // VEHICLE CRUD
    // ========================================

    public function index(Request $request)
    {
        $filters = $request->only(['status', 'brand', 'search']);
        $vehicles = $this->vehicleService->getAllVehicles($filters);

        return response()->json([
            'success' => true,
            'data' => $vehicles,
        ]);
    }

    public function show($id)
    {
        $vehicle = $this->vehicleService->getVehicle($id);

        return response()->json([
            'success' => true,
            'data' => $vehicle,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'plate_number' => 'required|string|max:20|unique:vehicles,plate_number',
            'brand' => 'required|string|max:100',
            'model' => 'required|string|max:100',
            'year' => 'nullable|integer|min:1990|max:' . (date('Y') + 1),
            'color' => 'nullable|string|max:50',
            'capacity' => 'required|integer|min:1|max:50',
            'fuel_type' => 'nullable|string|max:50',
            'insurance_expiry' => 'nullable|date',
            'stnk_expiry' => 'nullable|date',
            'notes' => 'nullable|string|max:1000',
        ]);

        $vehicle = $this->vehicleService->createVehicle($validated);

        return response()->json([
            'success' => true,
            'message' => 'Kendaraan berhasil ditambahkan.',
            'data' => $vehicle,
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'plate_number' => 'required|string|max:20|unique:vehicles,plate_number,' . $id,
            'brand' => 'required|string|max:100',
            'model' => 'required|string|max:100',
            'year' => 'nullable|integer|min:1990|max:' . (date('Y') + 1),
            'color' => 'nullable|string|max:50',
            'capacity' => 'required|integer|min:1|max:50',
            'status' => 'required|in:available,in_use,maintenance,retired',
            'fuel_type' => 'nullable|string|max:50',
            'insurance_expiry' => 'nullable|date',
            'stnk_expiry' => 'nullable|date',
            'current_mileage' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string|max:1000',
        ]);

        $vehicle = $this->vehicleService->updateVehicle($id, $validated);

        return response()->json([
            'success' => true,
            'message' => 'Kendaraan berhasil diperbarui.',
            'data' => $vehicle,
        ]);
    }

    public function destroy($id)
    {
        $this->vehicleService->deleteVehicle($id);

        return response()->json([
            'success' => true,
            'message' => 'Kendaraan berhasil dihapus.',
        ]);
    }

    // ========================================
    // BOOKING MANAGEMENT
    // ========================================

    public function bookings(Request $request)
    {
        $filters = $request->only(['status', 'vehicle_id', 'date', 'my_requests']);

        if ($request->has('my_requests') && $request->my_requests) {
            $bookings = $this->vehicleService->getUserBookings(Auth::id());
        } else {
            $bookings = $this->vehicleService->getAllBookings($filters);
        }

        return response()->json([
            'success' => true,
            'data' => $bookings,
        ]);
    }

    public function createBooking()
    {
        $vehicles = $this->vehicleService->getAllVehicles(['status' => 'available']);

        return response()->json([
            'success' => true,
            'data' => ['vehicles' => $vehicles],
        ]);
    }

    public function storeBooking(Request $request)
    {
        $validated = $request->validate([
            'vehicle_id' => 'required|exists:vehicles,id',
            'purpose' => 'required|string|max:500',
            'destination' => 'required|string|max:255',
            'estimated_distance' => 'nullable|numeric|min:0',
            'start_datetime' => 'required|date|after:now',
            'end_datetime' => 'required|date|after:start_datetime',
            'passengers' => 'required|integer|min:1|max:50',
            'notes' => 'nullable|string|max:1000',
        ]);

        try {
            $booking = $this->vehicleService->createBooking($validated);

            return response()->json([
                'success' => true,
                'message' => 'Booking kendaraan berhasil diajukan.',
                'data' => $booking,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    public function showBooking($id)
    {
        $booking = $this->vehicleService->getBooking($id);

        return response()->json([
            'success' => true,
            'data' => $booking,
        ]);
    }

    public function approveBooking(Request $request, $id)
    {
        try {
            $booking = $this->vehicleService->approveBooking($id, Auth::id());

            return response()->json([
                'success' => true,
                'message' => 'Booking berhasil disetujui.',
                'data' => $booking,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    public function rejectBooking(Request $request, $id)
    {
        try {
            $booking = $this->vehicleService->rejectBooking($id, Auth::id(), $request->rejection_reason);

            return response()->json([
                'success' => true,
                'message' => 'Booking berhasil ditolak.',
                'data' => $booking,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    public function cancelBooking($id)
    {
        try {
            $this->vehicleService->cancelBooking($id);

            return response()->json([
                'success' => true,
                'message' => 'Booking berhasil dibatalkan.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    public function startTrip($id)
    {
        try {
            $booking = $this->vehicleService->startTrip($id);

            return response()->json([
                'success' => true,
                'message' => 'Perjalanan telah dimulai.',
                'data' => $booking,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    public function completeTrip(Request $request, $id)
    {
        try {
            $booking = $this->vehicleService->completeTrip($id, $request->only(['actual_distance', 'actual_fuel_cost', 'notes']));

            return response()->json([
                'success' => true,
                'message' => 'Perjalanan telah selesai.',
                'data' => $booking,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    // ========================================
    // MAINTENANCE
    // ========================================

    public function maintenanceLogs($vehicleId)
    {
        $logs = $this->vehicleService->getMaintenanceLogs($vehicleId);

        return response()->json([
            'success' => true,
            'data' => $logs,
        ]);
    }

    public function addMaintenance(Request $request, $vehicleId)
    {
        $validated = $request->validate([
            'maintenance_type' => 'required|string|max:100',
            'description' => 'required|string|max:1000',
            'cost' => 'nullable|numeric|min:0',
            'maintenance_date' => 'required|date',
            'next_maintenance_date' => 'nullable|date|after:maintenance_date',
            'mileage_at_service' => 'nullable|numeric|min:0',
            'service_provider' => 'nullable|string|max:255',
            'set_maintenance_status' => 'nullable|boolean',
        ]);

        $log = $this->vehicleService->addMaintenanceLog($vehicleId, $validated);

        return response()->json([
            'success' => true,
            'message' => 'Log maintenance berhasil ditambahkan.',
            'data' => $log,
        ], 201);
    }

    // ========================================
    // AVAILABILITY CHECK
    // ========================================

    public function checkAvailability(Request $request)
    {
        $request->validate([
            'vehicle_id' => 'required|exists:vehicles,id',
            'start_datetime' => 'required|date',
            'end_datetime' => 'required|date|after:start_datetime',
        ]);

        $availableVehicles = $this->vehicleService->getAvailableVehicles(
            $request->start_datetime,
            $request->end_datetime
        );

        $vehicleAvailable = $availableVehicles->contains('id', $request->vehicle_id);

        return response()->json([
            'success' => true,
            'data' => [
                'available' => $vehicleAvailable,
                'available_vehicles' => $availableVehicles,
            ],
        ]);
    }
}