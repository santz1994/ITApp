<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\MeetingRoomBooking;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class MeetingRoomApiController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = MeetingRoomBooking::with(['user', 'approver']);

        $tab = $request->get('tab', 'all');
        $user = Auth::user();

        if ($tab === 'my-bookings') {
            $query->where('user_id', $user->id);
        } elseif ($tab === 'pending') {
            if ($user->hasRole(['director', 'administrator', 'developer'])) {
                $query->where('status', 'pending');
            }
        } else {
            if (!$user->hasRole(['administrator', 'developer', 'director', 'receptionist'])) {
                $query->where('user_id', $user->id);
            }
        }

        if ($request->has('room_name')) {
            $query->where('room_name', $request->room_name);
        }

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('date')) {
            $query->whereDate('start_datetime', $request->date);
        }

        $perPage = $request->get('per_page', 50);
        $bookings = $query->orderBy('start_datetime', 'desc')->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $bookings->items(),
            'meta' => [
                'current_page' => $bookings->currentPage(),
                'last_page' => $bookings->lastPage(),
                'per_page' => $bookings->perPage(),
                'total' => $bookings->total(),
            ],
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'room_name' => 'required|string|max:255',
            'subject' => 'required|string|max:255',
            'start_datetime' => 'required|date',
            'end_datetime' => 'required|date|after:start_datetime',
            'description' => 'nullable|string',
            'participants' => 'nullable|string',
            'is_blocking' => 'sometimes|boolean',
        ]);

        $validated['user_id'] = Auth::id();
        $validated['status'] = 'pending';

        $booking = MeetingRoomBooking::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Booking created successfully',
            'data' => $booking->load(['user', 'approver']),
        ], 201);
    }

    public function show($id): JsonResponse
    {
        $booking = MeetingRoomBooking::with(['user', 'approver'])->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $booking,
        ]);
    }

    public function update(Request $request, $id): JsonResponse
    {
        $booking = MeetingRoomBooking::findOrFail($id);

        $validated = $request->validate([
            'subject' => 'sometimes|string|max:255',
            'start_datetime' => 'sometimes|date',
            'end_datetime' => 'sometimes|date|after:start_datetime',
            'description' => 'nullable|string',
            'participants' => 'nullable|string',
        ]);

        $booking->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Booking updated successfully',
            'data' => $booking->fresh()->load(['user', 'approver']),
        ]);
    }

    public function destroy($id): JsonResponse
    {
        $booking = MeetingRoomBooking::findOrFail($id);
        $booking->delete();

        return response()->json([
            'success' => true,
            'message' => 'Booking deleted successfully',
        ]);
    }

    public function approve($id): JsonResponse
    {
        $booking = MeetingRoomBooking::findOrFail($id);
        $booking->update([
            'status' => 'approved',
            'approved_by' => Auth::id(),
            'approved_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Booking approved',
            'data' => $booking->fresh()->load(['user', 'approver']),
        ]);
    }

    public function reject(Request $request, $id): JsonResponse
    {
        $booking = MeetingRoomBooking::findOrFail($id);
        $booking->update([
            'status' => 'rejected',
            'rejected_reason' => $request->input('rejection_reason'),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Booking rejected',
            'data' => $booking->fresh()->load(['user', 'approver']),
        ]);
    }

    public function cancel($id): JsonResponse
    {
        $booking = MeetingRoomBooking::findOrFail($id);
        $booking->update(['status' => 'cancelled']);

        return response()->json([
            'success' => true,
            'message' => 'Booking cancelled',
            'data' => $booking->fresh()->load(['user', 'approver']),
        ]);
    }

    public function finish($id): JsonResponse
    {
        $booking = MeetingRoomBooking::findOrFail($id);
        $booking->update(['status' => 'finished']);

        return response()->json([
            'success' => true,
            'message' => 'Booking finished',
            'data' => $booking->fresh()->load(['user', 'approver']),
        ]);
    }
}
