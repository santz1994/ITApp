<?php

namespace App\Http\Controllers;

use App\Services\VehicleService;
use App\Vehicle;
use App\VehicleBooking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class VehicleController extends Controller
{
    protected $vehicleService;

    public function __construct(VehicleService $vehicleService)
    {
        $this->vehicleService = $vehicleService;
        $this->middleware('auth');
    }

    // ========================================
    // VEHICLE CRUD
    // ========================================

    public function index(Request $request)
    {
        $filters = $request->only(['status', 'brand', 'search']);
        $vehicles = $this->vehicleService->getAllVehicles($filters);

        return view('vehicles.index', compact('vehicles', 'filters'));
    }

    public function create()
    {
        return view('vehicles.create');
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
            'photo' => 'nullable|image|max:2048',
        ]);

        if ($request->hasFile('photo')) {
            $validated['photo'] = $request->file('photo')->store('vehicles', 'public');
        }

        $this->vehicleService->createVehicle($validated);

        return redirect()->route('vehicles.index')
            ->with('success', 'Kendaraan berhasil ditambahkan.');
    }

    public function show($id)
    {
        $vehicle = $this->vehicleService->getVehicle($id);
        $bookings = $this->vehicleService->getAllBookings(['vehicle_id' => $id]);
        $maintenanceLogs = $this->vehicleService->getMaintenanceLogs($id);

        return view('vehicles.show', compact('vehicle', 'bookings', 'maintenanceLogs'));
    }

    public function edit($id)
    {
        $vehicle = Vehicle::findOrFail($id);
        return view('vehicles.edit', compact('vehicle'));
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
            'photo' => 'nullable|image|max:2048',
        ]);

        if ($request->hasFile('photo')) {
            $validated['photo'] = $request->file('photo')->store('vehicles', 'public');
        }

        $this->vehicleService->updateVehicle($id, $validated);

        return redirect()->route('vehicles.show', $id)
            ->with('success', 'Kendaraan berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $this->vehicleService->deleteVehicle($id);

        return redirect()->route('vehicles.index')
            ->with('success', 'Kendaraan berhasil dihapus.');
    }

    // ========================================
    // BOOKING MANAGEMENT
    // ========================================

    public function bookings(Request $request)
    {
        $filters = $request->only(['status', 'vehicle_id', 'date']);
        $bookings = $this->vehicleService->getAllBookings($filters);

        return view('vehicles.bookings', compact('bookings', 'filters'));
    }

    public function createBooking(Request $request)
    {
        $vehicles = $this->vehicleService->getAllVehicles(['status' => 'available']);
        return view('vehicles.create-booking', compact('vehicles'));
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

            return redirect()->route('vehicles.booking.show', $booking->id)
                ->with('success', 'Booking kendaraan berhasil diajukan. Menunggu persetujuan.');
        } catch (\Exception $e) {
            return back()->withErrors(['vehicle_id' => $e->getMessage()])->withInput();
        }
    }

    public function showBooking($id)
    {
        $booking = $this->vehicleService->getBooking($id);
        return view('vehicles.booking-detail', compact('booking'));
    }

    public function approveBooking(Request $request, $id)
    {
        try {
            $booking = $this->vehicleService->approveBooking($id, Auth::id());

            return redirect()->route('vehicles.booking.show', $id)
                ->with('success', 'Booking kendaraan berhasil disetujui.');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function rejectBooking(Request $request, $id)
    {
        $request->validate([
            'rejection_reason' => 'nullable|string|max:500',
        ]);

        try {
            $booking = $this->vehicleService->rejectBooking($id, Auth::id(), $request->rejection_reason);

            return redirect()->route('vehicles.booking.show', $id)
                ->with('success', 'Booking kendaraan berhasil ditolak.');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function cancelBooking($id)
    {
        try {
            $this->vehicleService->cancelBooking($id);

            return redirect()->route('vehicles.booking.show', $id)
                ->with('success', 'Booking kendaraan berhasil dibatalkan.');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function startTrip($id)
    {
        try {
            $booking = $this->vehicleService->startTrip($id);

            return redirect()->route('vehicles.booking.show', $id)
                ->with('success', 'Perjalanan kendaraan telah dimulai.');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function completeTrip(Request $request, $id)
    {
        $validated = $request->validate([
            'actual_distance' => 'nullable|numeric|min:0',
            'actual_fuel_cost' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string|max:1000',
        ]);

        try {
            $booking = $this->vehicleService->completeTrip($id, $validated);

            return redirect()->route('vehicles.booking.show', $id)
                ->with('success', 'Perjalanan kendaraan telah selesai.');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    // ========================================
    // MAINTENANCE
    // ========================================

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

        $this->vehicleService->addMaintenanceLog($vehicleId, $validated);

        return redirect()->route('vehicles.show', $vehicleId)
            ->with('success', 'Log maintenance berhasil ditambahkan.');
    }

    // ========================================
    // API / AJAX ENDPOINTS
    // ========================================

    public function checkAvailability(Request $request)
    {
        $request->validate([
            'vehicle_id' => 'required|exists:vehicles,id',
            'start_datetime' => 'required|date',
            'end_datetime' => 'required|date|after:start_datetime',
        ]);

        $isAvailable = $this->vehicleService->getAvailableVehicles(
            $request->start_datetime,
            $request->end_datetime
        );

        $vehicleAvailable = $isAvailable->contains('id', $request->vehicle_id);

        return response()->json([
            'available' => $vehicleAvailable,
            'available_vehicles' => $isAvailable,
        ]);
    }

    public function myBookings()
    {
        $bookings = $this->vehicleService->getUserBookings(Auth::id());
        return view('vehicles.my-bookings', compact('bookings'));
    }
}