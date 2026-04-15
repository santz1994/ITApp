<?php

namespace App\Services;

use App\MeetingRoomBooking;
use App\User;
use App\Notifications\MeetingRoomApproved;
use App\Notifications\MeetingRoomRejected;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

/**
 * Booking Approval Service
 * 
 * Handles all approval workflow for meeting room bookings:
 * - Director approval/rejection
 * - User cancellation
 * - Force cancellation (emergency)
 * - Mark as finished
 * - Manager acknowledgment
 * 
 * Extracted from MeetingRoomBookingController for better separation of concerns.
 */
class BookingApprovalService
{
    /**
     * Approve a booking (Director/Admin only)
     * 
     * @param MeetingRoomBooking $booking
     * @param User|null $approver
     * @param string|null $notes
     * @return array ['success' => bool, 'message' => string, 'booking' => MeetingRoomBooking]
     */
    public function approveBooking(
        MeetingRoomBooking $booking, 
        ?User $approver = null,
        ?string $notes = null
    ): array {
        $approver = $approver ?? Auth::user();
        
        // Validation: Can only approve pending bookings
        if ($booking->status !== 'pending') {
            return [
                'success' => false,
                'message' => 'Only pending bookings can be approved',
                'booking' => $booking,
            ];
        }
        
        // Validation: Must have appropriate role
        if (!$approver->hasRole(['director', 'admin', 'super-admin'])) {
            return [
                'success' => false,
                'message' => 'Only directors and admins can approve bookings',
                'booking' => $booking,
            ];
        }
        
        // Update booking
        $booking->update([
            'status' => 'approved',
            'approved_by' => $approver->id,
            'approved_at' => now(),
            'director_notes' => $notes,
        ]);
        
        // Log the approval
        Log::info('Meeting room booking approved', [
            'booking_id' => $booking->id,
            'room' => $booking->room_name,
            'approved_by' => $approver->name,
            'approved_at' => now(),
        ]);
        
        // Send notification to requester
        try {
            $booking->user->notify(new MeetingRoomApproved($booking, $notes));
        } catch (\Exception $e) {
            Log::error('Failed to send approval notification', [
                'booking_id' => $booking->id,
                'error' => $e->getMessage()
            ]);
        }
        
        return [
            'success' => true,
            'message' => 'Booking approved successfully',
            'booking' => $booking->fresh(),
        ];
    }

    /**
     * Reject a booking (Director/Admin only)
     * 
     * @param MeetingRoomBooking $booking
     * @param string $reason Rejection reason
     * @param User|null $rejector
     * @return array ['success' => bool, 'message' => string, 'booking' => MeetingRoomBooking]
     */
    public function rejectBooking(
        MeetingRoomBooking $booking,
        string $reason,
        ?User $rejector = null
    ): array {
        $rejector = $rejector ?? Auth::user();
        
        // Validation: Can only reject pending bookings
        if ($booking->status !== 'pending') {
            return [
                'success' => false,
                'message' => 'Only pending bookings can be rejected',
                'booking' => $booking,
            ];
        }
        
        // Validation: Must have appropriate role
        if (!$rejector->hasRole(['director', 'admin', 'super-admin'])) {
            return [
                'success' => false,
                'message' => 'Only directors and admins can reject bookings',
                'booking' => $booking,
            ];
        }
        
        // Validation: Reason is required
        if (empty($reason)) {
            return [
                'success' => false,
                'message' => 'Rejection reason is required',
                'booking' => $booking,
            ];
        }
        
        // Update booking
        $booking->update([
            'status' => 'rejected',
            'approved_by' => $rejector->id,
            'approved_at' => now(),
            'director_notes' => $reason,
        ]);
        
        // Log the rejection
        Log::info('Meeting room booking rejected', [
            'booking_id' => $booking->id,
            'room' => $booking->room_name,
            'rejected_by' => $rejector->name,
            'reason' => $reason,
        ]);
        
        // Send notification to requester
        try {
            $booking->user->notify(new MeetingRoomRejected($booking, $reason));
        } catch (\Exception $e) {
            Log::error('Failed to send rejection notification', [
                'booking_id' => $booking->id,
                'error' => $e->getMessage()
            ]);
        }
        
        return [
            'success' => true,
            'message' => 'Booking rejected',
            'booking' => $booking->fresh(),
        ];
    }

    /**
     * Cancel a booking (User/Receptionist/Admin)
     * 
     * @param MeetingRoomBooking $booking
     * @param User|null $canceller
     * @return array ['success' => bool, 'message' => string, 'booking' => MeetingRoomBooking]
     */
    public function cancelBooking(
        MeetingRoomBooking $booking,
        ?User $canceller = null
    ): array {
        $canceller = $canceller ?? Auth::user();
        
        // Validation: Check if cancellable
        if (!$booking->canBeCancelled()) {
            return [
                'success' => false,
                'message' => 'This booking cannot be cancelled (already started, finished, or already cancelled)',
                'booking' => $booking,
            ];
        }
        
        // Authorization: Owner or admin can cancel
        $isOwner = $booking->user_id === $canceller->id;
        $isAdmin = $canceller->hasRole(['admin', 'super-admin', 'receptionist']);
        
        if (!$isOwner && !$isAdmin) {
            return [
                'success' => false,
                'message' => 'You are not authorized to cancel this booking',
                'booking' => $booking,
            ];
        }
        
        // Update booking
        $booking->update([
            'status' => 'cancelled',
        ]);
        
        // Log the cancellation
        Log::info('Meeting room booking cancelled', [
            'booking_id' => $booking->id,
            'room' => $booking->room_name,
            'cancelled_by' => $canceller->name,
            'cancelled_at' => now(),
            'original_status' => $booking->getOriginal('status'),
        ]);
        
        return [
            'success' => true,
            'message' => 'Booking cancelled successfully',
            'booking' => $booking->fresh(),
        ];
    }

    /**
     * Force cancel a booking (Emergency - Receptionist/Super-Admin only)
     * 
     * This can cancel even ongoing meetings in emergency situations
     * (e.g., visitor accident, urgent building maintenance)
     * 
     * @param MeetingRoomBooking $booking
     * @param string $reason Emergency reason
     * @param User|null $canceller
     * @return array ['success' => bool, 'message' => string, 'booking' => MeetingRoomBooking]
     */
    public function forceCancelBooking(
        MeetingRoomBooking $booking,
        string $reason,
        ?User $canceller = null
    ): array {
        $canceller = $canceller ?? Auth::user();
        
        // Validation: Only super-admin and receptionist can force cancel
        if (!$canceller->hasRole(['super-admin', 'receptionist'])) {
            return [
                'success' => false,
                'message' => 'Only receptionist and super-admin can force cancel bookings',
                'booking' => $booking,
            ];
        }
        
        // Check if already cancelled or finished
        if (in_array($booking->status, ['cancelled', 'finished'])) {
            return [
                'success' => false,
                'message' => 'This booking is already ' . $booking->status,
                'booking' => $booking,
            ];
        }
        
        // Validation: Reason is required
        if (empty($reason)) {
            return [
                'success' => false,
                'message' => 'Emergency cancellation reason is required',
                'booking' => $booking,
            ];
        }
        
        // Store original status for logging
        $originalStatus = $booking->status;
        
        // Force cancel with reason
        $notes = ($booking->director_notes ? $booking->director_notes . ' | ' : '') 
            . 'FORCE CANCELLED by ' . $canceller->name . ' at ' . now()->format('Y-m-d H:i:s') 
            . ' - Reason: ' . $reason;
        
        $booking->update([
            'status' => 'cancelled',
            'director_notes' => $notes,
        ]);
        
        // Log the emergency cancellation
        Log::warning('Meeting room booking FORCE CANCELLED', [
            'booking_id' => $booking->id,
            'room' => $booking->room_name,
            'original_status' => $originalStatus,
            'force_cancelled_by' => $canceller->name,
            'cancelled_at' => now(),
            'reason' => $reason,
            'was_ongoing' => $originalStatus === 'approved' && 
                            $booking->start_datetime->isPast() && 
                            $booking->end_datetime->isFuture(),
        ]);
        
        // TODO: Send urgent notification to affected parties
        // $booking->user->notify(new BookingForceCancelledNotification($booking, $reason));
        
        return [
            'success' => true,
            'message' => 'Meeting has been FORCE CANCELLED (Emergency override)',
            'booking' => $booking->fresh(),
        ];
    }

    /**
     * Mark booking as finished (Receptionist only)
     * 
     * @param MeetingRoomBooking $booking
     * @param User|null $finisher
     * @return array ['success' => bool, 'message' => string, 'booking' => MeetingRoomBooking]
     */
    public function finishBooking(
        MeetingRoomBooking $booking,
        ?User $finisher = null
    ): array {
        $finisher = $finisher ?? Auth::user();
        
        // Validation: Only receptionist/admin can mark as finished
        if (!$finisher->hasRole(['admin', 'super-admin', 'receptionist'])) {
            return [
                'success' => false,
                'message' => 'Only receptionist can mark bookings as finished',
                'booking' => $booking,
            ];
        }
        
        // Validation: Can only finish approved bookings that have ended
        if (!$booking->canBeFinished()) {
            return [
                'success' => false,
                'message' => 'This booking cannot be marked as finished (must be approved and meeting must have ended)',
                'booking' => $booking,
            ];
        }
        
        // Update booking
        $booking->update([
            'status' => 'finished',
            'finished_at' => now(),
        ]);
        
        // Log
        Log::info('Meeting room booking marked as finished', [
            'booking_id' => $booking->id,
            'room' => $booking->room_name,
            'finished_by' => $finisher->name,
            'finished_at' => now(),
        ]);
        
        return [
            'success' => true,
            'message' => 'Booking marked as finished',
            'booking' => $booking->fresh(),
        ];
    }

    /**
     * Set manager acknowledgment (Mengetahui)
     * 
     * @param MeetingRoomBooking $booking
     * @param int $managerId
     * @return array ['success' => bool, 'message' => string, 'booking' => MeetingRoomBooking]
     */
    public function setManagerAcknowledgment(
        MeetingRoomBooking $booking,
        int $managerId
    ): array {
        // Validation: Manager must exist
        $manager = User::find($managerId);
        if (!$manager) {
            return [
                'success' => false,
                'message' => 'Manager not found',
                'booking' => $booking,
            ];
        }
        
        // Update booking
        $booking->update([
            'manager_id' => $managerId,
            'manager_approved_at' => now(),
        ]);
        
        // Log
        Log::info('Manager acknowledgment set for booking', [
            'booking_id' => $booking->id,
            'manager' => $manager->name,
            'acknowledged_at' => now(),
        ]);
        
        return [
            'success' => true,
            'message' => 'Manager acknowledgment recorded',
            'booking' => $booking->fresh(),
        ];
    }

    /**
     * Get bookings requiring approval (Director Dashboard)
     * 
     * @return \Illuminate\Support\Collection
     */
    public function getBookingsRequiringApproval()
    {
        return MeetingRoomBooking::with(['user', 'approver'])
            ->where('status', 'pending')
            ->where('start_datetime', '>=', now()) // Only future bookings
            ->orderBy('start_datetime')
            ->get();
    }

    /**
     * Get recently approved bookings
     * 
     * @param int $days Number of days to look back
     * @return \Illuminate\Support\Collection
     */
    public function getRecentlyApprovedBookings(int $days = 7)
    {
        return MeetingRoomBooking::with(['user', 'approver'])
            ->where('status', 'approved')
            ->where('approved_at', '>=', now()->subDays($days))
            ->orderBy('approved_at', 'desc')
            ->get();
    }

    /**
     * Get approval statistics
     * 
     * @param Carbon|null $startDate
     * @param Carbon|null $endDate
     * @return array
     */
    public function getApprovalStats(?Carbon $startDate = null, ?Carbon $endDate = null): array
    {
        $query = MeetingRoomBooking::query();
        
        if ($startDate) {
            $query->where('created_at', '>=', $startDate);
        }
        
        if ($endDate) {
            $query->where('created_at', '<=', $endDate);
        }
        
        $total = $query->count();
        $approved = (clone $query)->where('status', 'approved')->count();
        $rejected = (clone $query)->where('status', 'rejected')->count();
        $cancelled = (clone $query)->where('status', 'cancelled')->count();
        $pending = (clone $query)->where('status', 'pending')->count();
        
        // Calculate average approval time (from created to approved)
        $avgApprovalTime = MeetingRoomBooking::whereNotNull('approved_at')
            ->when($startDate, fn($q) => $q->where('created_at', '>=', $startDate))
            ->when($endDate, fn($q) => $q->where('created_at', '<=', $endDate))
            ->get()
            ->avg(function($booking) {
                return $booking->created_at->diffInHours($booking->approved_at);
            });
        
        $approvalRate = $total > 0 ? round(($approved / $total) * 100, 2) : 0;
        $rejectionRate = $total > 0 ? round(($rejected / $total) * 100, 2) : 0;
        
        return [
            'total_requests' => $total,
            'approved' => $approved,
            'rejected' => $rejected,
            'cancelled' => $cancelled,
            'pending' => $pending,
            'approval_rate' => $approvalRate,
            'rejection_rate' => $rejectionRate,
            'avg_approval_time_hours' => round($avgApprovalTime, 2),
        ];
    }

    /**
     * Bulk approve bookings
     * 
     * @param array $bookingIds
     * @param User|null $approver
     * @param string|null $notes
     * @return array ['success_count' => int, 'failed_count' => int, 'results' => array]
     */
    public function bulkApproveBookings(
        array $bookingIds,
        ?User $approver = null,
        ?string $notes = null
    ): array {
        $results = [
            'success_count' => 0,
            'failed_count' => 0,
            'results' => [],
        ];
        
        foreach ($bookingIds as $bookingId) {
            $booking = MeetingRoomBooking::find($bookingId);
            
            if (!$booking) {
                $results['failed_count']++;
                $results['results'][] = [
                    'booking_id' => $bookingId,
                    'success' => false,
                    'message' => 'Booking not found',
                ];
                continue;
            }
            
            $result = $this->approveBooking($booking, $approver, $notes);
            
            if ($result['success']) {
                $results['success_count']++;
            } else {
                $results['failed_count']++;
            }
            
            $results['results'][] = array_merge(['booking_id' => $bookingId], $result);
        }
        
        return $results;
    }

    /**
     * Auto-finish past bookings (for cron job)
     * 
     * Automatically marks approved bookings as finished if their end time has passed
     * 
     * @param int $hoursBuffer Hours after end time to wait before auto-finishing
     * @return array ['finished_count' => int, 'booking_ids' => array]
     */
    public function autoFinishPastBookings(int $hoursBuffer = 1): array
    {
        $cutoffTime = now()->subHours($hoursBuffer);
        
        $bookings = MeetingRoomBooking::where('status', 'approved')
            ->where('end_datetime', '<=', $cutoffTime)
            ->get();
        
        $finishedIds = [];
        
        foreach ($bookings as $booking) {
            $booking->update([
                'status' => 'finished',
                'finished_at' => now(),
            ]);
            
            $finishedIds[] = $booking->id;
            
            Log::info('Auto-finished booking', [
                'booking_id' => $booking->id,
                'room' => $booking->room_name,
                'end_datetime' => $booking->end_datetime,
            ]);
        }
        
        return [
            'finished_count' => count($finishedIds),
            'booking_ids' => $finishedIds,
        ];
    }
}
