<?php

namespace App\Services;

use App\MeetingRoomBooking;
use App\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

/**
 * Meeting Room Booking Service
 * 
 * Handles all business logic for meeting room bookings including:
 * - Conflict detection
 * - Booking validation
 * - Booking creation and updates
 * 
 * This service is extracted from MeetingRoomBookingController to follow
 * Single Responsibility Principle and improve testability.
 */
class MeetingRoomBookingService
{
    /**
     * Check if a meeting room has conflicting bookings
     * 
     * Implements STRICT conflict detection:
     * - Checks for ANY overlap in time slots
     * - Only considers pending and approved bookings
     * - Excludes a specific booking ID (for updates)
     * 
     * @param string $roomName Name of the room
     * @param Carbon $startDateTime Start time
     * @param Carbon $endDateTime End time
     * @param int|null $excludeBookingId Booking ID to exclude from check (for updates)
     * @return MeetingRoomBooking|null Conflicting booking or null
     */
    public function checkConflict(
        string $roomName, 
        Carbon $startDateTime, 
        Carbon $endDateTime, 
        ?int $excludeBookingId = null
    ): ?MeetingRoomBooking {
        return MeetingRoomBooking::where('room_name', $roomName)
            ->when($excludeBookingId, function($query) use ($excludeBookingId) {
                $query->where('id', '!=', $excludeBookingId);
            })
            ->whereIn('status', ['pending', 'approved'])
            ->where(function($query) use ($startDateTime, $endDateTime) {
                // Case 1: New booking starts during existing booking
                $query->where(function($q) use ($startDateTime, $endDateTime) {
                    $q->where('start_datetime', '<=', $startDateTime)
                      ->where('end_datetime', '>', $startDateTime);
                })
                // Case 2: New booking ends during existing booking
                ->orWhere(function($q) use ($startDateTime, $endDateTime) {
                    $q->where('start_datetime', '<', $endDateTime)
                      ->where('end_datetime', '>=', $endDateTime);
                })
                // Case 3: New booking completely contains existing booking
                ->orWhere(function($q) use ($startDateTime, $endDateTime) {
                    $q->where('start_datetime', '>=', $startDateTime)
                      ->where('end_datetime', '<=', $endDateTime);
                })
                // Case 4: Existing booking completely contains new booking
                ->orWhere(function($q) use ($startDateTime, $endDateTime) {
                    $q->where('start_datetime', '<=', $startDateTime)
                      ->where('end_datetime', '>=', $endDateTime);
                });
            })
            ->first();
    }

    /**
     * Validate booking time meets minimum notice requirement
     * 
     * Regular users: 15 minutes notice required
     * Receptionist/Admin: Can book immediately
     * 
     * @param Carbon $startDateTime Start time to validate
     * @param User|null $user User making the booking (defaults to auth user)
     * @return array ['valid' => bool, 'message' => string, 'minimum_time' => Carbon]
     */
    public function validateMinimumNotice(Carbon $startDateTime, ?User $user = null): array
    {
        $user = $user ?? Auth::user();
        
        // Receptionist and admins can book anytime before meeting starts
        $isReceptionist = $user->hasRole(['admin', 'super-admin', 'receptionist']);
        
        $minStartTime = $isReceptionist ? now() : now()->addMinutes(15);
        
        if ($startDateTime->lt($minStartTime)) {
            return [
                'valid' => false,
                'message' => 'Pemesanan ruang meeting harus diajukan minimal 15 menit sebelum waktu mulai. Waktu paling awal: ' . $minStartTime->format('d-m-Y H:i'),
                'minimum_time' => $minStartTime,
            ];
        }
        
        return [
            'valid' => true,
            'message' => 'Booking time is valid',
            'minimum_time' => $minStartTime,
        ];
    }

    /**
     * Create a new meeting room booking
     * 
     * @param array $data Validated booking data
     * @param int $userId User ID creating the booking
     * @return MeetingRoomBooking Created booking
     */
    public function createBooking(array $data, int $userId): MeetingRoomBooking
    {
        // Set default values
        $data['user_id'] = $userId;
        $data['status'] = 'pending';
        
        // If requester_name is not provided, use authenticated user's name
        if (empty($data['requester_name'])) {
            $data['requester_name'] = User::find($userId)->name;
        }

        return MeetingRoomBooking::create($data);
    }

    /**
     * Update an existing booking
     * 
     * @param MeetingRoomBooking $booking Booking to update
     * @param array $data Updated data
     * @return MeetingRoomBooking Updated booking
     */
    public function updateBooking(MeetingRoomBooking $booking, array $data): MeetingRoomBooking
    {
        $booking->update($data);
        return $booking->fresh();
    }

    /**
     * Check if user is authorized to edit a booking
     * 
     * @param MeetingRoomBooking $booking
     * @param User|null $user
     * @return array ['authorized' => bool, 'reason' => string]
     */
    public function canUserEditBooking(MeetingRoomBooking $booking, ?User $user = null): array
    {
        $user = $user ?? Auth::user();
        
        $isOwner = $booking->user_id === $user->id;
        $isReceptionist = $user->hasRole(['admin', 'super-admin', 'receptionist']);
        
        // Owner can edit if pending and future
        if ($isOwner && $booking->canBeEdited()) {
            return ['authorized' => true, 'reason' => 'owner'];
        }
        
        // Receptionist can edit if pending/approved and future
        if ($isReceptionist && $booking->canBeEditedByReceptionist()) {
            return ['authorized' => true, 'reason' => 'receptionist'];
        }
        
        // Determine specific reason for denial
        if ($isOwner && !$booking->canBeEdited()) {
            return [
                'authorized' => false, 
                'reason' => 'Booking cannot be edited (must be pending and not started yet)'
            ];
        }
        
        if (!$isOwner && !$isReceptionist) {
            return [
                'authorized' => false,
                'reason' => 'You are not authorized to edit this booking'
            ];
        }
        
        return [
            'authorized' => false,
            'reason' => 'Booking cannot be edited (already started or rejected/cancelled)'
        ];
    }

    /**
     * Get bookings with filters
     * 
     * @param array $filters
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function getBookingsQuery(array $filters = [])
    {
        $query = MeetingRoomBooking::with(['user', 'approver', 'manager']);

        // Filter by user (for regular users)
        if (isset($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }

        // Filter by status
        if (isset($filters['status']) && $filters['status'] !== 'all') {
            $query->where('status', $filters['status']);
        }

        // Filter by room
        if (isset($filters['room_name'])) {
            $query->where('room_name', $filters['room_name']);
        }

        // Filter by date range
        if (isset($filters['start_date'])) {
            $query->whereDate('start_datetime', '>=', $filters['start_date']);
        }

        if (isset($filters['end_date'])) {
            $query->whereDate('start_datetime', '<=', $filters['end_date']);
        }

        // Default ordering
        $query->orderBy('start_datetime', 'desc');

        return $query;
    }

    /**
     * Get booking statistics
     * 
     * @return array
     */
    public function getBookingStats(): array
    {
        return [
            'total' => MeetingRoomBooking::count(),
            'pending' => MeetingRoomBooking::where('status', 'pending')->count(),
            'approved' => MeetingRoomBooking::where('status', 'approved')->count(),
            'rejected' => MeetingRoomBooking::where('status', 'rejected')->count(),
            'finished' => MeetingRoomBooking::where('status', 'finished')->count(),
            'cancelled' => MeetingRoomBooking::where('status', 'cancelled')->count(),
            'today' => MeetingRoomBooking::whereDate('start_datetime', today())
                ->whereIn('status', ['pending', 'approved'])
                ->count(),
        ];
    }

    /**
     * Get upcoming bookings for a room
     * 
     * @param string $roomName
     * @param int $limit
     * @return \Illuminate\Support\Collection
     */
    public function getUpcomingBookings(string $roomName, int $limit = 5)
    {
        return MeetingRoomBooking::where('room_name', $roomName)
            ->whereIn('status', ['pending', 'approved'])
            ->where('start_datetime', '>=', now())
            ->orderBy('start_datetime')
            ->limit($limit)
            ->get();
    }

    /**
     * Get current booking for a room (happening now)
     * 
     * @param string $roomName
     * @return MeetingRoomBooking|null
     */
    public function getCurrentBooking(string $roomName): ?MeetingRoomBooking
    {
        $now = now();
        
        return MeetingRoomBooking::where('room_name', $roomName)
            ->where('status', 'approved')
            ->where('start_datetime', '<=', $now)
            ->where('end_datetime', '>=', $now)
            ->first();
    }

    /**
     * Check if room is currently occupied
     * 
     * @param string $roomName
     * @return bool
     */
    public function isRoomOccupied(string $roomName): bool
    {
        return $this->getCurrentBooking($roomName) !== null;
    }

    /**
     * Get available time slots for a room on a specific date
     * 
     * @param string $roomName
     * @param Carbon $date
     * @param int $slotDuration Minutes per slot (default 30)
     * @return array Array of available time slots
     */
    public function getAvailableTimeSlots(string $roomName, Carbon $date, int $slotDuration = 30): array
    {
        // Operating hours: 07:00 - 22:00
        $startHour = 7;
        $endHour = 22;
        
        $availableSlots = [];
        $currentTime = $date->copy()->setTime($startHour, 0);
        $endTime = $date->copy()->setTime($endHour, 0);
        
        // Get all bookings for this room on this date
        $bookings = MeetingRoomBooking::where('room_name', $roomName)
            ->whereIn('status', ['pending', 'approved'])
            ->whereDate('start_datetime', $date)
            ->orderBy('start_datetime')
            ->get();
        
        while ($currentTime->lt($endTime)) {
            $slotEnd = $currentTime->copy()->addMinutes($slotDuration);
            
            // Check if this slot conflicts with any booking
            $hasConflict = false;
            foreach ($bookings as $booking) {
                if (
                    ($currentTime->lt($booking->end_datetime) && $slotEnd->gt($booking->start_datetime))
                ) {
                    $hasConflict = true;
                    break;
                }
            }
            
            if (!$hasConflict) {
                $availableSlots[] = [
                    'start' => $currentTime->format('H:i'),
                    'end' => $slotEnd->format('H:i'),
                    'available' => true,
                ];
            }
            
            $currentTime->addMinutes($slotDuration);
        }
        
        return $availableSlots;
    }
}
