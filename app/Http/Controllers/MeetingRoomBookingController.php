<?php

namespace App\Http\Controllers;

use App\MeetingRoomBooking;
use App\MeetingRoomDisplaySetting;
use App\MeetingRoomLcdSetting;
use App\Exports\MeetingRoomMonthlyExport;
use App\Events\MeetingRoomBookingCreated;
use App\Events\MeetingRoomBookingStatusChanged;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use Carbon\Carbon;
use Throwable;

class MeetingRoomBookingController extends Controller
{
    /**
     * Meeting room list
     */
    private $defaultRooms = [
        'Ruang Meeting 3',
        'Ruang Meeting 2',
        'Ruang Meeting 1',
    ];

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Auto-finish blocking bookings that have passed their end time
        $this->autoFinishExpiredBlockings();

        $query = MeetingRoomBooking::with(['user', 'approver']);

        // Get current tab
        $tab = $request->get('tab', 'all');

        // Filter by tab
        if ($tab === 'my-bookings') {
            // Show only current user's bookings
            $query->where('user_id', Auth::id());
        } elseif ($tab === 'pending') {
            // Show only pending bookings (for directors/admins)
            if (Auth::user()->hasRole(['director', 'administrator', 'developer'])) {
                $query->where('status', 'pending');
            }
        } elseif ($tab === 'all') {
            // Show all bookings based on user role
            if (!Auth::user()->hasRole(['administrator', 'developer', 'director', 'receptionist'])) {
                // Regular users only see their own bookings
                $query->where('user_id', Auth::id());
            }
            // Admins, directors, and receptionists see all bookings
        }

        // Additional filter by status (from query parameter)
        if ($request->has('status') && $request->status != 'all') {
            $query->where('status', $request->status);
        }

        // Get all bookings for DataTable client-side processing
        $bookings = $query->orderBy('start_datetime', 'desc')->get();

        // Stats for cards
        $stats = [
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

        return view('Meeting.index', compact('bookings', 'stats'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('Meeting.create', [
            'rooms' => $this->getDisplayRooms(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Rule 2: Permohonan pemesanan ruang meeting harus diajukan minimal 15 menit sebelumnya
        $minStartTime = now()->addMinutes(15);
        
        $validated = $request->validate([
            'room_name' => 'required|string',
            'requester_name' => 'nullable|string|max:255', // Optional: Name of person requesting (defaults to user name)
            'department' => 'required|string|max:255', // Bagian/Departemen
            'requester_position' => 'required|string|max:255', // Jabatan Pemohon
            'start_datetime' => [
                'required',
                'date',
                'after_or_equal:' . $minStartTime->format('Y-m-d H:i:s'),
            ],
            'end_datetime' => 'required|date|after:start_datetime',
            'purpose' => 'required|string|min:10', // Keperluan Rapat
            'meeting_description' => 'required|string|min:10', // Deskripsi/Keterangan Rapat
            'meeting_needs' => 'nullable|string', // Keperluan Rapat (optional facilities)
            'attendees_count' => 'required|integer|min:1|max:100', // Estimasi Peserta
        ], [
            'start_datetime.after_or_equal' => 'Pemesanan ruang meeting harus diajukan minimal 15 menit sebelum waktu mulai. Waktu paling awal: ' . $minStartTime->format('d-m-Y H:i'),
        ]);

        // Check if user can bypass BLOCKED rooms (Receptionist, Super-admin, or daniel@quty.co.id)
        $canBypassBlocked = Auth::user()->hasRole(['receptionist', 'developer']) 
                            || Auth::user()->email === 'daniel@quty.co.id';

        // Rule 1: STRICT conflict detection - Room cannot be booked if ANY overlap exists
        // Check for ANY booking that overlaps with the requested time slot
        $conflictQuery = MeetingRoomBooking::where('room_name', $validated['room_name'])
            ->whereIn('status', ['pending', 'approved']); // Only check active bookings
        
        // Receptionist/Super-admin can bypass BLOCKED rooms (for VIP/special bookings)
        if ($canBypassBlocked) {
            $conflictQuery->where(function($q) {
                $q->where('purpose', 'NOT LIKE', 'BLOCKED:%')
                  ->orWhereNull('purpose');
            });
        }
        
        $conflict = $conflictQuery->where(function($query) use ($validated) {
                // Case 1: New booking starts during existing booking
                $query->where(function($q) use ($validated) {
                    $q->where('start_datetime', '<=', $validated['start_datetime'])
                      ->where('end_datetime', '>', $validated['start_datetime']);
                })
                // Case 2: New booking ends during existing booking
                ->orWhere(function($q) use ($validated) {
                    $q->where('start_datetime', '<', $validated['end_datetime'])
                      ->where('end_datetime', '>=', $validated['end_datetime']);
                })
                // Case 3: New booking completely contains existing booking
                ->orWhere(function($q) use ($validated) {
                    $q->where('start_datetime', '>=', $validated['start_datetime'])
                      ->where('end_datetime', '<=', $validated['end_datetime']);
                })
                // Case 4: Existing booking completely contains new booking
                ->orWhere(function($q) use ($validated) {
                    $q->where('start_datetime', '<=', $validated['start_datetime'])
                      ->where('end_datetime', '>=', $validated['end_datetime']);
                });
            })
            ->first();

        if ($conflict) {
            return back()->withErrors([
                'start_datetime' => 'Room is already booked from ' . 
                    $conflict->start_datetime->format('d-m-Y H:i') . ' to ' . 
                    $conflict->end_datetime->format('d-m-Y H:i') . '. Please choose a different time or room.'
            ])->withInput();
        }

        $validated['user_id'] = Auth::id();
        $validated['status'] = 'pending';
        
        // If requester_name is not provided or empty, use the authenticated user's name
        if (empty($validated['requester_name'])) {
            $validated['requester_name'] = Auth::user()->name;
        }

        $booking = MeetingRoomBooking::create($validated);

        // Fire event for new booking
        event(new MeetingRoomBookingCreated($booking));

        return redirect()->route('meeting-room-bookings.index')
            ->with('success', 'Meeting room booking request submitted successfully!');
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $booking = MeetingRoomBooking::with(['user', 'approver'])->findOrFail($id);

        // Authorization check
        if (!Auth::user()->hasRole(['administrator', 'developer', 'director', 'receptionist']) 
            && $booking->user_id != Auth::id()) {
            abort(403, 'Unauthorized access.');
        }

        return view('Meeting.show', compact('booking'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $booking = MeetingRoomBooking::findOrFail($id);

        // Check authorization:
        // 1. Receptionist can edit (any status: pending or approved)
        // 2. Super-admin can edit (any status: pending or approved)
        // 3. daniel@quty.co.id can edit (any status: pending or approved)
        // 4. Owner can edit ONLY if status is pending
        $isOwner = $booking->user_id == Auth::id();
        $isReceptionist = Auth::user()->hasRole(['receptionist']);
        $isSuperAdmin = Auth::user()->hasRole('developer');
        $isDaniel = Auth::user()->email === 'daniel@quty.co.id';
        
        // Special users can edit approved bookings
        $canEditApproved = $isReceptionist || $isSuperAdmin || $isDaniel;
        
        // Check authorization
        if (!$isOwner && !$canEditApproved) {
            return redirect()->route('meeting-room-bookings.index')
                ->with('error', 'You are not authorized to edit this booking.');
        }
        
        // Check if booking can be edited based on status
        if (!in_array($booking->status, ['pending', 'approved'])) {
            return redirect()->route('meeting-room-bookings.index')
                ->with('error', 'This booking cannot be edited (status: ' . $booking->status . ').');
        }
        
        // Owner can only edit if status is pending
        if ($isOwner && !$canEditApproved && $booking->status !== 'pending') {
            return redirect()->route('meeting-room-bookings.index')
                ->with('error', 'You cannot edit this booking. Only Receptionist, Super Admin, or daniel@quty.co.id can edit approved bookings.');
        }
        
        // Check if meeting has already started
        if ($booking->start_datetime->isPast()) {
            return redirect()->route('meeting-room-bookings.index')
                ->with('error', 'This booking cannot be edited (meeting has already started).');
        }

        return view('Meeting.edit', [
            'booking' => $booking,
            'rooms' => $this->getDisplayRooms(),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $booking = MeetingRoomBooking::findOrFail($id);

        // Check authorization - same as edit method
        // 1. Receptionist can edit (any status: pending or approved)
        // 2. Super-admin can edit (any status: pending or approved)
        // 3. daniel@quty.co.id can edit (any status: pending or approved)
        // 4. Owner can edit ONLY if status is pending
        $isOwner = $booking->user_id == Auth::id();
        $isReceptionist = Auth::user()->hasRole(['receptionist']);
        $isSuperAdmin = Auth::user()->hasRole('developer');
        $isDaniel = Auth::user()->email === 'daniel@quty.co.id';
        
        // Special users can edit approved bookings
        $canEditApproved = $isReceptionist || $isSuperAdmin || $isDaniel;
        
        // Check authorization
        if (!$isOwner && !$canEditApproved) {
            return redirect()->route('meeting-room-bookings.index')
                ->with('error', 'You are not authorized to edit this booking.');
        }
        
        // Owner can only edit if status is pending
        if ($isOwner && !$canEditApproved && $booking->status !== 'pending') {
            return redirect()->route('meeting-room-bookings.index')
                ->with('error', 'You cannot edit this booking. Only Receptionist, Super Admin, or daniel@quty.co.id can edit approved bookings.');
        }

        // For regular users: minimum 15 minutes notice
        // For receptionist/superadmin/daniel: can edit anytime before meeting starts
        $minStartTime = $canEditApproved ? now() : now()->addMinutes(15);

        $validated = $request->validate([
            'room_name' => 'required|string',
            'department' => 'required|string|max:255',
            'requester_position' => 'required|string|max:255',
            'start_datetime' => [
                'required',
                'date',
                'after_or_equal:' . $minStartTime->format('Y-m-d H:i:s'),
            ],
            'end_datetime' => 'required|date|after:start_datetime',
            'purpose' => 'required|string|min:10',
            'meeting_description' => 'required|string|min:10',
            'meeting_needs' => 'nullable|string',
            'attendees_count' => 'required|integer|min:1|max:100',
        ], [
            'start_datetime.after_or_equal' => 'Pemesanan harus minimal 15 menit sebelum waktu mulai. Waktu paling awal: ' . $minStartTime->format('d-m-Y H:i'),
        ]);

        // Check if user can bypass BLOCKED rooms
        $canBypassBlocked = $canEditApproved; // Receptionist/Super-admin/daniel can bypass

        // STRICT conflict detection (excluding current booking)
        $conflictQuery = MeetingRoomBooking::where('id', '!=', $id)
            ->where('room_name', $validated['room_name'])
            ->whereIn('status', ['pending', 'approved']);
        
        // Receptionist/Super-admin can bypass BLOCKED rooms
        if ($canBypassBlocked) {
            $conflictQuery->where(function($q) {
                $q->where('purpose', 'NOT LIKE', 'BLOCKED:%')
                  ->orWhereNull('purpose');
            });
        }
        
        $conflict = $conflictQuery->where(function($query) use ($validated) {
                $query->where(function($q) use ($validated) {
                    $q->where('start_datetime', '<=', $validated['start_datetime'])
                      ->where('end_datetime', '>', $validated['start_datetime']);
                })
                ->orWhere(function($q) use ($validated) {
                    $q->where('start_datetime', '<', $validated['end_datetime'])
                      ->where('end_datetime', '>=', $validated['end_datetime']);
                })
                ->orWhere(function($q) use ($validated) {
                    $q->where('start_datetime', '>=', $validated['start_datetime'])
                      ->where('end_datetime', '<=', $validated['end_datetime']);
                })
                ->orWhere(function($q) use ($validated) {
                    $q->where('start_datetime', '<=', $validated['start_datetime'])
                      ->where('end_datetime', '>=', $validated['end_datetime']);
                });
            })
            ->first();

        if ($conflict) {
            return back()->withErrors([
                'start_datetime' => 'Room is already booked from ' . 
                    $conflict->start_datetime->format('d-m-Y H:i') . ' to ' . 
                    $conflict->end_datetime->format('d-m-Y H:i') . '. Please choose a different time.'
            ])->withInput();
        }

        $booking->update($validated);

        return redirect()->route('meeting-room-bookings.show', $booking->id)
            ->with('success', 'Booking updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $booking = MeetingRoomBooking::findOrFail($id);

        // Super-admin can delete any booking
        $isSuperAdmin = Auth::user()->hasRole('developer');
        
        // Owner can delete only if pending and future
        $isOwnerAndCanEdit = ($booking->user_id == Auth::id() && $booking->canBeEdited());
        
        if (!$isSuperAdmin && !$isOwnerAndCanEdit) {
            return redirect()->route('meeting-room-bookings.index')
                ->with('error', 'You cannot delete this booking.');
        }

        $booking->delete();

        return redirect()->route('meeting-room-bookings.index')
            ->with('success', 'Booking cancelled successfully!');
    }

    /**
     * Approve a booking (Director only)
     */
    public function approve(Request $request, $id)
    {
        if (!Auth::user()->hasRole(['administrator', 'developer', 'director'])) {
            abort(403, 'Unauthorized');
        }

        $booking = MeetingRoomBooking::findOrFail($id);

        if (!$booking->canBeApproved()) {
            return back()->with('error', 'This booking cannot be approved.');
        }

        $validated = $request->validate([
            'director_notes' => 'nullable|string',
        ]);

        $oldStatus = $booking->status;

        $booking->update([
            'status' => 'approved',
            'director_notes' => $validated['director_notes'] ?? 'Approved',
            'approved_by' => Auth::id(),
            'approved_at' => now(),
        ]);

        // Fire event for status change
        event(new MeetingRoomBookingStatusChanged($booking, $oldStatus, 'approved'));

        // Redirect back to director dashboard if came from there
        if ($request->input('from') === 'director-dashboard') {
            return redirect()->route('meeting-room-bookings.director-dashboard')
                ->with('success', 'Booking approved successfully!');
        }

        return redirect()->route('meeting-room-bookings.show', $booking->id)
            ->with('success', 'Booking approved successfully!');
    }

    /**
     * Reject a booking (Director only)
     */
    public function reject(Request $request, $id)
    {
        if (!Auth::user()->hasRole(['administrator', 'developer', 'director'])) {
            abort(403, 'Unauthorized');
        }

        $booking = MeetingRoomBooking::findOrFail($id);

        if (!$booking->canBeApproved()) {
            return back()->with('error', 'This booking cannot be rejected.');
        }

        $validated = $request->validate([
            'director_notes' => 'required|string|min:10',
        ]);

        $oldStatus = $booking->status;

        $booking->update([
            'status' => 'rejected',
            'director_notes' => $validated['director_notes'],
            'approved_by' => Auth::id(),
            'approved_at' => now(),
        ]);

        // Fire event for status change
        event(new MeetingRoomBookingStatusChanged($booking, $oldStatus, 'rejected'));

        // Redirect back to director dashboard if came from there
        if ($request->input('from') === 'director-dashboard') {
            return redirect()->route('meeting-room-bookings.director-dashboard')
                ->with('success', 'Booking rejected.');
        }

        return redirect()->route('meeting-room-bookings.show', $booking->id)
            ->with('success', 'Booking rejected.');
    }

    /**
     * Cancel a booking (Receptionist only)
     */
    public function cancel($id)
    {
        if (!Auth::user()->hasRole(['administrator', 'developer', 'receptionist'])) {
            abort(403, 'Only receptionist can cancel bookings');
        }

        $booking = MeetingRoomBooking::findOrFail($id);

        if (!$booking->canBeCancelled()) {
            return back()->with('error', 'This booking cannot be cancelled (already started, finished, or cancelled).');
        }

        $oldStatus = $booking->status;

        $booking->update([
            'status' => 'cancelled',
        ]);

        // Fire event for status change
        event(new MeetingRoomBookingStatusChanged($booking, $oldStatus, 'cancelled'));

        return redirect()->route('meeting-room-bookings.show', $booking->id)
            ->with('success', 'Booking cancelled successfully!');
    }

    /**
     * Mark booking as finished (Receptionist/Superadmin/Director/Management only)
     */
    public function finish($id)
    {
        if (!Auth::user()->hasRole(['developer', 'receptionist', 'director'])) {
            abort(403, 'Only receptionist, superadmin, director, or management can mark bookings as finished');
        }

        $booking = MeetingRoomBooking::findOrFail($id);

        if (!$booking->canBeFinished()) {
            return back()->with('error', 'This booking cannot be marked as finished (must be approved and meeting must have ended).');
        }

        $oldStatus = $booking->status;

        $booking->update([
            'status' => 'finished',
            'finished_at' => now(),
        ]);

        // Fire event for status change
        event(new MeetingRoomBookingStatusChanged($booking, $oldStatus, 'finished'));

        return redirect()->route('meeting-room-bookings.show', $booking->id)
            ->with('success', 'Booking marked as finished!');
    }

    /**
     * Override/Force cancel meeting even when in progress
     * (Receptionist & Super-admin only - for emergency situations)
     */
    public function forceCancel($id)
    {
        if (!Auth::user()->hasRole(['developer', 'receptionist'])) {
            abort(403, 'Only receptionist and super-admin can force cancel bookings');
        }

        $booking = MeetingRoomBooking::findOrFail($id);

        // Check if booking is already cancelled or finished
        if (in_array($booking->status, ['cancelled', 'finished'])) {
            return back()->with('error', 'This booking is already ' . $booking->status . '.');
        }

        // Force cancel with reason
        $booking->update([
            'status' => 'cancelled',
            'director_notes' => ($booking->director_notes ? $booking->director_notes . ' | ' : '') 
                . 'FORCE CANCELLED by ' . Auth::user()->name . ' at ' . now()->format('Y-m-d H:i:s') 
                . ' (Emergency override)',
        ]);

        // Log the activity
        \Log::info('Force cancel booking', [
            'booking_id' => $booking->id,
            'room' => $booking->room_name,
            'original_status' => $booking->getOriginal('status'),
            'cancelled_by' => Auth::user()->name,
            'cancelled_at' => now(),
            'reason' => 'Emergency override - visitor accident',
        ]);

        return redirect()->route('meeting-room-bookings.show', $booking->id)
            ->with('success', 'Meeting has been FORCE CANCELLED successfully! (Emergency override)');
    }

    /**
     * Extend meeting time (for ongoing meetings)
     * Owner, Receptionist, Super-admin, or daniel@quty.co.id can extend
     * Accepts either extend_minutes OR new_end_time parameter
     */
    public function extendTime(Request $request, $id)
    {
        $booking = MeetingRoomBooking::findOrFail($id);
        
        // Authorization check
        $isOwner = $booking->user_id == Auth::id();
        $isReceptionist = Auth::user()->hasRole(['receptionist']);
        $isSuperAdmin = Auth::user()->hasRole('developer');
        $isDaniel = Auth::user()->email === 'daniel@quty.co.id';
        
        if (!$isOwner && !$isReceptionist && !$isSuperAdmin && !$isDaniel) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }
        
        // Check if meeting is approved
        if ($booking->status !== 'approved') {
            return response()->json(['success' => false, 'message' => 'Only approved meetings can be extended'], 400);
        }
        
        // Support both extend_minutes and new_end_time parameters
        if ($request->has('new_end_time')) {
            // New format: receptionist provides exact new end time
            $validated = $request->validate([
                'new_end_time' => 'required|date_format:H:i',
                'extend_reason' => 'nullable|string|max:500',
            ]);
            
            $date = $booking->end_datetime->format('Y-m-d');
            $newEndTime = Carbon::parse($date . ' ' . $validated['new_end_time']);
            
            // Validate new end time is after current end time
            if ($newEndTime->lte($booking->end_datetime)) {
                return response()->json([
                    'success' => false,
                    'message' => 'New end time must be after current end time (' . $booking->end_datetime->format('H:i') . ')'
                ], 400);
            }
        } else {
            // Old format: extend by minutes
            $validated = $request->validate([
                'extend_minutes' => 'required|integer|min:15|max:120', // 15 min to 2 hours
                'extend_reason' => 'nullable|string|max:500',
            ]);
            
            $extendMinutes = $validated['extend_minutes'];
            $newEndTime = $booking->end_datetime->copy()->addMinutes($extendMinutes);
        }
        
        // Check for conflicts with the extended time
        $conflict = MeetingRoomBooking::where('id', '!=', $id)
            ->where('room_name', $booking->room_name)
            ->whereIn('status', ['pending', 'approved'])
            ->where('start_datetime', '<', $newEndTime)
            ->where('end_datetime', '>', $booking->end_datetime)
            ->first();
        
        if ($conflict) {
            return response()->json([
                'success' => false, 
                'message' => 'Cannot extend: Room is booked from ' . $conflict->start_datetime->format('H:i') . ' - ' . $conflict->end_datetime->format('H:i')
            ], 409);
        }
        
        // Update end time
        $oldEndTime = $booking->end_datetime->format('H:i');
        $booking->end_datetime = $newEndTime;
        
        // Add reason to director notes if provided
        if (!empty($validated['extend_reason'])) {
            $reasonNote = "\n[Extended by " . Auth::user()->name . " at " . now()->format('Y-m-d H:i') . "]: " . $validated['extend_reason'];
            $booking->director_notes = ($booking->director_notes ?? '') . $reasonNote;
        }
        
        $booking->save();
        
        \Log::info('Meeting time extended', [
            'booking_id' => $booking->id,
            'room' => $booking->room_name,
            'extended_by' => Auth::user()->name,
            'old_end_time' => $oldEndTime,
            'new_end_time' => $newEndTime->format('H:i'),
            'reason' => $validated['extend_reason'] ?? null,
        ]);
        
        return response()->json([
            'success' => true,
            'message' => 'Meeting extended successfully until ' . $newEndTime->format('H:i'),
            'new_end_time' => $newEndTime->format('Y-m-d H:i:s'),
        ]);
    }

    /**
     * Quick edit meeting subject (purpose and description)
     * Receptionist, Super-admin, or daniel@quty.co.id only
     */
    public function quickEditSubject(Request $request, $id)
    {
        $booking = MeetingRoomBooking::findOrFail($id);
        
        // Authorization check - Receptionist, Super-admin, or daniel@quty.co.id
        $isReceptionist = Auth::user()->hasRole(['receptionist']);
        $isSuperAdmin = Auth::user()->hasRole('developer');
        $isDaniel = Auth::user()->email === 'daniel@quty.co.id';
        
        if (!$isReceptionist && !$isSuperAdmin && !$isDaniel) {
            return response()->json(['success' => false, 'message' => 'Unauthorized. Only receptionist or admin can edit meeting subject.'], 403);
        }
        
        // Can only edit pending or approved bookings that haven't finished
        if (!in_array($booking->status, ['pending', 'approved'])) {
            return response()->json([
                'success' => false,
                'message' => 'Can only edit pending or approved meetings. Current status: ' . $booking->status
            ], 400);
        }
        
        $validated = $request->validate([
            'purpose' => 'required|string|min:10|max:500',
            'meeting_description' => 'required|string|min:10|max:1000',
        ]);
        
        // Store old values for logging
        $oldPurpose = $booking->purpose;
        $oldDescription = $booking->meeting_description;
        
        // Update booking
        $booking->update([
            'purpose' => $validated['purpose'],
            'meeting_description' => $validated['meeting_description'],
        ]);
        
        \Log::info('Meeting subject edited by receptionist', [
            'booking_id' => $booking->id,
            'room' => $booking->room_name,
            'edited_by' => Auth::user()->name,
            'old_purpose' => $oldPurpose,
            'new_purpose' => $validated['purpose'],
            'changed_at' => now()->format('Y-m-d H:i:s'),
        ]);
        
        return response()->json([
            'success' => true,
            'message' => 'Meeting subject updated successfully!',
            'data' => [
                'purpose' => $booking->purpose,
                'meeting_description' => $booking->meeting_description,
            ],
        ]);
    }

    /**
     * Quick edit meeting time (start and end time)
     * Receptionist, Super-admin, or daniel@quty.co.id only
     */
    public function quickEditTime(Request $request, $id)
    {
        $booking = MeetingRoomBooking::findOrFail($id);
        
        // Authorization check - Receptionist, Super-admin, or daniel@quty.co.id
        $isReceptionist = Auth::user()->hasRole(['receptionist']);
        $isSuperAdmin = Auth::user()->hasRole('developer');
        $isDaniel = Auth::user()->email === 'daniel@quty.co.id';
        
        if (!$isReceptionist && !$isSuperAdmin && !$isDaniel) {
            return response()->json(['success' => false, 'message' => 'Unauthorized. Only receptionist or admin can edit meeting time.'], 403);
        }
        
        // Can only edit pending or approved bookings that haven't started yet
        if (!in_array($booking->status, ['pending', 'approved'])) {
            return response()->json([
                'success' => false,
                'message' => 'Can only edit pending or approved meetings. Current status: ' . $booking->status
            ], 400);
        }
        
        $validated = $request->validate([
            'meeting_date' => 'required|date|after_or_equal:today',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i',
        ]);
        
        // Combine date and time to create datetime
        $startDatetime = Carbon::parse($validated['meeting_date'] . ' ' . $validated['start_time']);
        $endDatetime = Carbon::parse($validated['meeting_date'] . ' ' . $validated['end_time']);
        
        // Validate end time is after start time
        if ($endDatetime->lte($startDatetime)) {
            return response()->json([
                'success' => false,
                'message' => 'End time must be after start time'
            ], 400);
        }
        
        // Receptionist/Super-admin can bypass BLOCKED rooms (already authorized)
        $conflictQuery = MeetingRoomBooking::where('id', '!=', $id)
            ->where('room_name', $booking->room_name)
            ->whereIn('status', ['pending', 'approved'])
            ->where(function($q) {
                // Exclude BLOCKED bookings for receptionist
                $q->where('purpose', 'NOT LIKE', 'BLOCKED:%')
                  ->orWhereNull('purpose');
            });
        
        // Check for conflicts (excluding current booking)
        $conflict = $conflictQuery->where(function($query) use ($startDatetime, $endDatetime) {
                // Case 1: New booking starts during existing booking
                $query->where(function($q) use ($startDatetime, $endDatetime) {
                    $q->where('start_datetime', '<=', $startDatetime)
                      ->where('end_datetime', '>', $startDatetime);
                })
                // Case 2: New booking ends during existing booking
                ->orWhere(function($q) use ($startDatetime, $endDatetime) {
                    $q->where('start_datetime', '<', $endDatetime)
                      ->where('end_datetime', '>=', $endDatetime);
                })
                // Case 3: New booking completely contains existing booking
                ->orWhere(function($q) use ($startDatetime, $endDatetime) {
                    $q->where('start_datetime', '>=', $startDatetime)
                      ->where('end_datetime', '<=', $endDatetime);
                })
                // Case 4: Existing booking completely contains new booking
                ->orWhere(function($q) use ($startDatetime, $endDatetime) {
                    $q->where('start_datetime', '<=', $startDatetime)
                      ->where('end_datetime', '>=', $endDatetime);
                });
            })
            ->first();
        
        if ($conflict) {
            return response()->json([
                'success' => false,
                'message' => 'Time conflict! Room is already booked from ' . 
                    $conflict->start_datetime->format('d-m-Y H:i') . ' to ' . 
                    $conflict->end_datetime->format('d-m-Y H:i')
            ], 409);
        }
        
        // Store old values for logging
        $oldStartTime = $booking->start_datetime->format('Y-m-d H:i');
        $oldEndTime = $booking->end_datetime->format('Y-m-d H:i');
        
        // Update booking time
        $booking->update([
            'start_datetime' => $startDatetime,
            'end_datetime' => $endDatetime,
        ]);
        
        \Log::info('Meeting time edited by receptionist', [
            'booking_id' => $booking->id,
            'room' => $booking->room_name,
            'edited_by' => Auth::user()->name,
            'old_start' => $oldStartTime,
            'old_end' => $oldEndTime,
            'new_start' => $startDatetime->format('Y-m-d H:i'),
            'new_end' => $endDatetime->format('Y-m-d H:i'),
            'changed_at' => now()->format('Y-m-d H:i:s'),
        ]);
        
        return response()->json([
            'success' => true,
            'message' => 'Meeting time updated successfully!',
            'data' => [
                'start_datetime' => $startDatetime->format('Y-m-d H:i:s'),
                'end_datetime' => $endDatetime->format('Y-m-d H:i:s'),
                'start_time_display' => $startDatetime->format('d M Y H:i'),
                'end_time_display' => $endDatetime->format('d M Y H:i'),
            ],
        ]);
    }

    /**
     * Display receptionist dashboard
     */
    public function receptionistDashboard()
    {
        // Authorization check
        if (!Auth::user()->hasRole(['developer', 'administrator', 'receptionist'])) {
            abort(403, 'Access denied. Receptionist only.');
        }

        // Auto-finish expired bookings
        $this->autoFinishExpiredBlockings();

        // Get all rooms with current booking status
        $rooms = [];
        $displayRooms = $this->getDisplayRooms();
        $now = now();
        $today = now()->startOfDay();
        $endOfDay = now()->endOfDay();
        
        foreach ($displayRooms as $roomName) {
            // Current booking (happening right now)
            $currentBooking = \App\MeetingRoomBooking::where('room_name', $roomName)
                ->where('status', 'approved')
                ->where('start_datetime', '<=', $now)
                ->where('end_datetime', '>=', $now)
                ->first();

            // Check if it's a blocked room (purpose starts with "BLOCKED:")
            $isBlocked = $currentBooking && str_starts_with($currentBooking->purpose, 'BLOCKED:');
            
            $status = 'available';
            if ($currentBooking) {
                $status = $isBlocked ? 'unavailable' : 'occupied';
            }

            // Today's bookings (all approved bookings for today, excluding completed ones)
            $todayBookings = \App\MeetingRoomBooking::where('room_name', $roomName)
                ->where('status', 'approved')
                ->whereBetween('start_datetime', [$today, $endOfDay])
                ->where('end_datetime', '>=', $now) // Exclude completed meetings
                ->orderBy('start_datetime')
                ->get();

            // Pending bookings (all pending bookings)
            $pendingBookings = \App\MeetingRoomBooking::where('room_name', $roomName)
                ->where('status', 'pending')
                ->where('start_datetime', '>=', $today)
                ->orderBy('start_datetime')
                ->get();

            $rooms[] = [
                'name' => $roomName,
                'status' => $status,
                'current_booking' => $currentBooking,
                'today_bookings' => $todayBookings,
                'pending_bookings' => $pendingBookings,
            ];
        }

        return view('Meeting.r-dashboard', [
            'rooms' => $rooms,
            'roomNames' => $displayRooms,
        ]);
    }

    /**
     * Director Dashboard
     * Shows pending requests for approval/rejection
     */
    public function directorDashboard()
    {
        // Authorization check - Allow director, management, admin, and super-admin
        if (!Auth::user()->hasRole(['developer', 'administrator', 'director'])) {
            abort(403, 'Access denied. Director or Management only.');
        }

        $displayRooms = $this->getDisplayRooms();

        // Auto-finish expired bookings
        $this->autoFinishExpiredBlockings();

        // Get pending requests
        $pendingRequests = MeetingRoomBooking::with(['user'])
            ->where('status', 'pending')
            ->orderBy('created_at', 'asc')
            ->get();

        // Get recently approved requests (today)
        $recentApproved = MeetingRoomBooking::with(['user'])
            ->where('status', 'approved')
            ->whereDate('updated_at', today())
            ->orderBy('updated_at', 'desc')
            ->limit(5)
            ->get();

        // Get recently rejected requests (today)
        $recentRejected = MeetingRoomBooking::with(['user'])
            ->where('status', 'rejected')
            ->whereDate('updated_at', today())
            ->orderBy('updated_at', 'desc')
            ->limit(5)
            ->get();

        // Statistics
        $stats = [
            'pending' => MeetingRoomBooking::where('status', 'pending')->count(),
            'approved_today' => MeetingRoomBooking::where('status', 'approved')
                ->whereDate('updated_at', today())
                ->count(),
            'rejected_today' => MeetingRoomBooking::where('status', 'rejected')
                ->whereDate('updated_at', today())
                ->count(),
            'month_total' => MeetingRoomBooking::whereBetween('created_at', [
                    now()->startOfMonth(),
                    now()->endOfMonth()
                ])
                ->count(),
        ];

        return view('Meeting.d-dashboard', [
            'pendingRequests' => $pendingRequests,
            'recentApproved' => $recentApproved,
            'recentRejected' => $recentRejected,
            'stats' => $stats,
            'roomNames' => $displayRooms,
        ]);
    }

    /**
     * Toggle room availability (mark as unavailable for VIP/special guests)
     */
    public function toggleRoomAvailability(Request $request)
    {
        // Authorization check
        if (!Auth::user()->hasRole(['developer', 'receptionist'])) {
            return response()->json(['success' => false, 'message' => 'Access denied'], 403);
        }

        $validated = $request->validate([
            'room_name' => 'required|string',
            'status' => 'required|in:available,unavailable',
            'reason' => 'nullable|string|max:255',
        ]);

        $roomName = $validated['room_name'];
        $newStatus = $validated['status'];
        $reason = $validated['reason'] ?? 'VIP/Special guest arrival';

        // If marking as unavailable, create a blocking booking
        if ($newStatus === 'unavailable') {
            $startDatetime = now();
            $endDatetime = now()->endOfDay(); // 23:59:59 today
            
            $blocking = \App\MeetingRoomBooking::create([
                'room_name' => $roomName,
                'user_id' => Auth::id(),
                'start_datetime' => $startDatetime,
                'end_datetime' => $endDatetime,
                'purpose' => 'BLOCKED: ' . $reason,
                'attendees_count' => 0,
                'status' => 'approved',
                'approved_by' => Auth::id(),
                'approved_at' => now(),
                'director_notes' => $reason,
            ]);

            \Log::info('Room marked unavailable by receptionist', [
                'room' => $roomName,
                'by' => Auth::user()->name,
                'reason' => $reason,
                'blocking_id' => $blocking->id,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Room marked as UNAVAILABLE successfully',
                'status' => 'unavailable',
            ]);
        }

        // If marking as available, finish all active blocking bookings
        if ($newStatus === 'available') {
            $now = now();
            
            // Find ALL blocked bookings that are active (not yet finished)
            // Include future blocks too, not just current ones
            $blockedBookings = \App\MeetingRoomBooking::where('room_name', $roomName)
                ->where('purpose', 'LIKE', 'BLOCKED:%')
                ->whereIn('status', ['pending', 'approved'])
                ->where('end_datetime', '>=', $now) // Get all future/current blocks
                ->get();

            $updated = 0;
            $bookingIds = [];
            foreach ($blockedBookings as $booking) {
                // Force update using individual property assignment to ensure it saves
                $booking->end_datetime = $now;
                $booking->status = 'finished';
                $booking->finished_at = $now;
                $booking->save(); // Explicitly save
                
                $updated++;
                $bookingIds[] = $booking->id;
                
                \Log::debug('Unblocked booking', [
                    'id' => $booking->id,
                    'old_end' => $booking->getOriginal('end_datetime'),
                    'new_end' => $now->format('Y-m-d H:i:s'),
                ]);
            }

            \Log::info('Room marked available by receptionist', [
                'room' => $roomName,
                'by' => Auth::user()->name,
                'updated_blockings' => $updated,
                'booking_ids' => $bookingIds,
                'unblocked_at' => $now->format('Y-m-d H:i:s'),
            ]);

            return response()->json([
                'success' => true,
                'message' => "Room marked as AVAILABLE. $updated blocking(s) finished.",
                'status' => 'available',
                'updated_count' => $updated,
            ]);
        }

        return response()->json(['success' => false, 'message' => 'Invalid status'], 400);
    }

    /**
     * Quick booking from receptionist dashboard
     */
    public function quickBooking(Request $request)
    {
        // Authorization check
        if (!Auth::user()->hasRole(['developer', 'administrator', 'receptionist'])) {
            return response()->json(['success' => false, 'message' => 'Access denied'], 403);
        }

        $validated = $request->validate([
            'room_name' => 'required|string',
            'meeting_date' => 'required|date',
            'start_time' => 'required',
            'end_time' => 'required',
            'purpose' => 'required|string|min:10',
            'meeting_description' => 'required|string|min:10',
            'requester_name' => 'required|string|max:255',
            'requester_position' => 'required|string|max:255',
            'department' => 'required|string|max:255',
            'attendees_count' => 'required|integer|min:1|max:100',
            'meeting_needs' => 'nullable|string',
        ]);

        // Combine date and time to create datetime
        $startDatetime = \Carbon\Carbon::parse($validated['meeting_date'] . ' ' . $validated['start_time']);
        $endDatetime = \Carbon\Carbon::parse($validated['meeting_date'] . ' ' . $validated['end_time']);

        // Validate end time is after start time
        if ($endDatetime->lte($startDatetime)) {
            return response()->json([
                'success' => false,
                'message' => 'Waktu selesai harus lebih besar dari waktu mulai / End time must be after start time',
            ], 422);
        }

        // Receptionist/Admin can bypass BLOCKED rooms (they're already authorized)
        $conflictQuery = \App\MeetingRoomBooking::where('room_name', $validated['room_name'])
            ->whereIn('status', ['pending', 'approved'])
            ->where(function($q) {
                // Exclude BLOCKED bookings for receptionist (they can override blocks)
                $q->where('purpose', 'NOT LIKE', 'BLOCKED:%')
                  ->orWhereNull('purpose');
            });
        
        // Check for conflicts with proper overlap detection
        $conflict = $conflictQuery->where(function ($query) use ($startDatetime, $endDatetime) {
                $query->where(function($q) use ($startDatetime, $endDatetime) {
                    $q->where('start_datetime', '<=', $startDatetime)
                      ->where('end_datetime', '>', $startDatetime);
                })
                ->orWhere(function($q) use ($startDatetime, $endDatetime) {
                    $q->where('start_datetime', '<', $endDatetime)
                      ->where('end_datetime', '>=', $endDatetime);
                })
                ->orWhere(function($q) use ($startDatetime, $endDatetime) {
                    $q->where('start_datetime', '>=', $startDatetime)
                      ->where('end_datetime', '<=', $endDatetime);
                })
                ->orWhere(function($q) use ($startDatetime, $endDatetime) {
                    $q->where('start_datetime', '<=', $startDatetime)
                      ->where('end_datetime', '>=', $endDatetime);
                });
            })
            ->exists();

        if ($conflict) {
            return response()->json([
                'success' => false,
                'message' => 'Room is already booked at that time. Please choose another time slot.',
            ], 409);
        }

        // Create booking (pending approval - not auto-approved)
        $booking = \App\MeetingRoomBooking::create([
            'room_name' => $validated['room_name'],
            'user_id' => Auth::id(),
            'start_datetime' => $startDatetime,
            'end_datetime' => $endDatetime,
            'purpose' => $validated['purpose'],
            'meeting_description' => $validated['meeting_description'],
            'meeting_needs' => $validated['meeting_needs'] ?? null,
            'attendees_count' => $validated['attendees_count'],
            'department' => $validated['department'],
            'requester_position' => $validated['requester_position'],
            'status' => 'pending',
            'director_notes' => 'Quick booking created by receptionist: ' . Auth::user()->name . 
                               ' | On behalf of: ' . $validated['requester_name'] . 
                               ' (' . $validated['requester_position'] . ' - ' . $validated['department'] . ')',
        ]);

        \Log::info('Quick booking created by receptionist (pending approval)', [
            'booking_id' => $booking->id,
            'room' => $booking->room_name,
            'by_receptionist' => Auth::user()->name,
            'requester_name' => $validated['requester_name'],
            'requester_position' => $validated['requester_position'],
            'department' => $validated['department'],
            'start' => $startDatetime->format('Y-m-d H:i'),
            'end' => $endDatetime->format('Y-m-d H:i'),
            'status' => 'pending',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Booking request submitted successfully! Waiting for director approval.',
            'booking_id' => $booking->id,
        ]);
    }

    /**
     * Update booking time via drag & drop
     */
    public function updateBookingTime(Request $request, $id)
    {
        // Authorization check
        if (!Auth::user()->hasRole(['developer', 'administrator', 'receptionist'])) {
            return response()->json(['success' => false, 'message' => 'Access denied'], 403);
        }

        $booking = \App\MeetingRoomBooking::findOrFail($id);

        $validated = $request->validate([
            'start_datetime' => 'required|date',
            'end_datetime' => 'required|date|after:start_datetime',
            'room_name' => 'nullable|string', // Allow room change
        ]);

        $startDatetime = \Carbon\Carbon::parse($validated['start_datetime']);
        $endDatetime = \Carbon\Carbon::parse($validated['end_datetime']);
        
        // Determine target room (use new room if provided, otherwise keep current)
        $targetRoom = $validated['room_name'] ?? $booking->room_name;

        // Check for conflicts (excluding current booking and cancelled/finished bookings)
        // Overlap logic: Two bookings overlap if:
        // (StartA < EndB) AND (EndA > StartB)
        // Only check approved and pending bookings for conflicts
        $conflictingBookings = \App\MeetingRoomBooking::where('room_name', $targetRoom)
            ->where('id', '!=', $id)
            ->whereIn('status', ['pending', 'approved'])
            ->where('start_datetime', '<', $endDatetime)
            ->where('end_datetime', '>', $startDatetime)
            ->get();

        if ($conflictingBookings->count() > 0) {
            // Build detailed error message
            $conflictingBooking = $conflictingBookings->first();
            $conflictTime = $conflictingBooking->start_datetime->format('H:i') . '-' . 
                           $conflictingBooking->end_datetime->format('H:i');
            $conflictPurpose = \Illuminate\Support\Str::limit($conflictingBooking->purpose, 30);
            
            \Log::warning('Drag & drop conflict detected', [
                'target_room' => $targetRoom,
                'requested_time' => $startDatetime->format('H:i') . '-' . $endDatetime->format('H:i'),
                'conflicting_booking_id' => $conflictingBooking->id,
                'conflicting_time' => $conflictTime,
                'conflicting_status' => $conflictingBooking->status,
            ]);
            
            return response()->json([
                'success' => false,
                'message' => "Time slot conflict! Bentrok dengan booking '{$conflictPurpose}' ({$conflictTime}). Silakan pilih waktu lain.",
            ], 409);
        }

        // Store old room for logging
        $oldRoom = $booking->room_name;
        
        // Update booking time and room
        $booking->update([
            'room_name' => $targetRoom,
            'start_datetime' => $startDatetime,
            'end_datetime' => $endDatetime,
        ]);

        \Log::info('Booking time updated via drag & drop', [
            'booking_id' => $booking->id,
            'by' => Auth::user()->name,
            'old_room' => $oldRoom,
            'new_room' => $targetRoom,
            'new_start' => $startDatetime->format('Y-m-d H:i'),
            'new_end' => $endDatetime->format('Y-m-d H:i'),
        ]);

        // Build success message
        $message = 'Booking updated successfully!';
        if ($oldRoom !== $targetRoom) {
            $message = "Booking moved from {$oldRoom} to {$targetRoom} at " . $startDatetime->format('H:i');
        } else {
            $message = "Booking time updated to " . $startDatetime->format('H:i');
        }

        return response()->json([
            'success' => true,
            'message' => $message,
        ]);
    }

    /**
     * Display calendar view of all bookings
     */
    public function calendar()
    {
        return view('Meeting.calendar', [
            'rooms' => $this->getDisplayRooms(),
        ]);
    }

    /**
     * LCD dashboard room settings page.
     */
    public function lcdSettings()
    {
        if (!Auth::user()->hasRole(['developer', 'administrator', 'receptionist'])) {
            abort(403, 'Access denied. Only receptionist/admin can manage LCD settings.');
        }

        $roomSettings = $this->getLcdRoomSettings();
        $lcdGlobalSettings = $this->getLcdGlobalSettings();

        return view('Meeting.lcd-settings', [
            'roomSettings' => $roomSettings,
            'lcdGlobalSettings' => $lcdGlobalSettings,
            'activeRoomCount' => count(array_filter($roomSettings, function ($room) {
                return !empty($room['is_active']);
            })),
        ]);
    }

    /**
     * Save LCD dashboard room settings.
     */
    public function saveLcdSettings(Request $request)
    {
        if (!Auth::user()->hasRole(['developer', 'administrator', 'receptionist'])) {
            abort(403, 'Access denied. Only receptionist/admin can manage LCD settings.');
        }

        $validated = $request->validate([
            'rooms' => 'required|array|min:1|max:20',
            'rooms.*.room_name' => 'required|string|max:255',
            'rooms.*.display_order' => 'required|integer|min:1|max:200',
            'rooms.*.is_active' => 'nullable|in:0,1',
            'carousel_rooms_per_slide' => 'required|integer|min:1|max:4',
            'carousel_interval_seconds' => 'required|integer|min:5|max:120',
        ]);

        $preparedRooms = collect($validated['rooms'])
            ->map(function ($room) {
                return [
                    'room_name' => trim((string) ($room['room_name'] ?? '')),
                    'display_order' => (int) ($room['display_order'] ?? 0),
                    'is_active' => ((int) ($room['is_active'] ?? 0)) === 1,
                ];
            })
            ->filter(function ($room) {
                return $room['room_name'] !== '';
            })
            ->sortBy('display_order')
            ->values();

        if ($preparedRooms->isEmpty()) {
            return back()->withErrors([
                'rooms' => 'Minimal harus ada 1 ruang meeting.',
            ])->withInput();
        }

        $normalizedNames = $preparedRooms
            ->pluck('room_name')
            ->map(function ($roomName) {
                return mb_strtolower($roomName);
            })
            ->toArray();

        if (count($normalizedNames) !== count(array_unique($normalizedNames))) {
            return back()->withErrors([
                'rooms' => 'Nama ruang meeting tidak boleh duplikat.',
            ])->withInput();
        }

        if (!$preparedRooms->contains(function ($room) {
            return $room['is_active'];
        })) {
            return back()->withErrors([
                'rooms' => 'Minimal harus ada 1 ruang yang aktif untuk LCD.',
            ])->withInput();
        }

        try {
            DB::transaction(function () use ($preparedRooms, $validated) {
                MeetingRoomDisplaySetting::query()->delete();

                foreach ($preparedRooms as $index => $room) {
                    MeetingRoomDisplaySetting::create([
                        'room_name' => $room['room_name'],
                        'display_order' => $index + 1,
                        'is_active' => $room['is_active'],
                    ]);
                }

                MeetingRoomLcdSetting::query()->updateOrCreate(
                    ['id' => 1],
                    [
                        'rooms_per_slide' => (int) $validated['carousel_rooms_per_slide'],
                        'slide_interval_seconds' => (int) $validated['carousel_interval_seconds'],
                    ]
                );
            });
        } catch (Throwable $exception) {
            Log::error('Failed to save LCD room settings.', [
                'message' => $exception->getMessage(),
            ]);

            return back()->withErrors([
                'rooms' => 'Gagal menyimpan pengaturan LCD. Pastikan migrasi terbaru sudah dijalankan.',
            ])->withInput();
        }

        return redirect()
            ->route('meeting-room-bookings.lcd-settings')
            ->with('success', 'Pengaturan LCD dashboard berhasil diperbarui.');
    }

    /**
     * Get calendar events data (JSON for FullCalendar)
     */
    public function calendarData(Request $request)
    {
        $query = MeetingRoomBooking::with(['user'])
            ->whereIn('status', ['pending', 'approved', 'finished']);

        // Filter by room if specified
        if ($request->has('room') && $request->room != 'all') {
            $query->where('room_name', $request->room);
        }

        // Filter by date range (FullCalendar sends start and end params)
        if ($request->has('start') && $request->has('end')) {
            $query->whereBetween('start_datetime', [
                $request->start,
                $request->end
            ]);
        }

        $bookings = $query->get();

        // Format for FullCalendar
        $events = $bookings->map(function ($booking) {
            $color = [
                'pending' => '#f39c12',  // yellow
                'approved' => '#00a65a', // green
                'rejected' => '#dd4b39', // red
                'cancelled' => '#999999', // gray
                'finished' => '#3c8dbc', // blue
            ][$booking->status] ?? '#999999';

            return [
                'id' => $booking->id,
                'title' => $booking->room_name . ' - ' . $booking->user->name,
                'start' => $booking->start_datetime->toIso8601String(),
                'end' => $booking->end_datetime->toIso8601String(),
                'backgroundColor' => $color,
                'borderColor' => $color,
                'textColor' => '#ffffff',
                'extendedProps' => [
                    'status' => $booking->status,
                    'room' => $booking->room_name,
                    'requester' => $booking->user->name,
                    'department' => $booking->department,
                    'purpose' => $booking->purpose,
                    'attendees' => $booking->attendees_count,
                    'booking_id' => $booking->id,
                ],
            ];
        });

        return response()->json($events);
    }

    /**
     * LCD Dashboard Display (Public - No Auth Required)
     * Shows real-time booking schedule for all rooms
     * Auto-refresh every 10 minutes
     */
    public function lcdDashboard()
    {
        try {
            // Auto-finish expired bookings
            $this->autoFinishExpiredBlockings();

            $rooms = $this->getDisplayRooms();
            $lcdGlobalSettings = $this->getLcdGlobalSettings();

            // Get today's bookings (approved and pending)
            $bookings = MeetingRoomBooking::with(['user'])
                ->whereIn('room_name', $rooms)
                ->whereDate('start_datetime', '>=', today())
                ->whereDate('start_datetime', '<=', today()->addDay())
                ->whereIn('status', ['pending', 'approved'])
                ->orderBy('start_datetime', 'asc')
                ->get();

            return view('Meeting.lcd-dashboard', [
                'rooms' => $rooms,
                'bookings' => $bookings,
                'lcdGlobalSettings' => $lcdGlobalSettings,
            ]);
        } catch (Throwable $exception) {
            return $this->renderLcdMaintenanceView('LCD Dashboard', $exception);
        }
    }

    /**
     * LCD Dashboard 2 - Display for 5 meeting rooms
     * Auto-refresh every 10 minutes
     */
    public function lcdDashboard2()
    {
        try {
            // Auto-finish expired bookings
            $this->autoFinishExpiredBlockings();

            $rooms = $this->getDisplayRooms();

            // Get today's bookings (approved and pending)
            $bookings = MeetingRoomBooking::with(['user'])
                ->whereIn('room_name', $rooms)
                ->whereDate('start_datetime', '>=', today())
                ->whereDate('start_datetime', '<=', today()->addDay())
                ->whereIn('status', ['pending', 'approved'])
                ->orderBy('start_datetime', 'asc')
                ->get();

            return view('Meeting.lcd-dashboard2', [
                'rooms' => $rooms,
                'bookings' => $bookings,
                'lcdGlobalSettings' => $this->getLcdGlobalSettings(),
            ]);
        } catch (Throwable $exception) {
            return $this->renderLcdMaintenanceView('LCD Dashboard 2', $exception);
        }
    }

    /**
     * Get active display rooms from settings, fallback to default rooms.
     */
    private function getDisplayRooms(): array
    {
        try {
            $rooms = MeetingRoomDisplaySetting::query()
                ->where('is_active', true)
                ->orderBy('display_order')
                ->pluck('room_name')
                ->map(function ($roomName) {
                    return trim((string) $roomName);
                })
                ->filter()
                ->unique()
                ->values()
                ->toArray();

            // Also include any room names that appear in bookings (ensures rooms used previously are selectable)
            $bookingRooms = MeetingRoomBooking::query()
                ->distinct()
                ->pluck('room_name')
                ->map(function ($roomName) { return trim((string) $roomName); })
                ->filter()
                ->unique()
                ->values()
                ->toArray();

            // Merge preserving display order, then booking rooms, then defaults
            $combined = array_values(array_unique(array_merge($rooms, $bookingRooms, $this->defaultRooms)));

            if (!empty($combined)) {
                return $combined;
            }
        } catch (Throwable $exception) {
            Log::warning('Failed to load meeting room display settings. Using default rooms.', [
                'message' => $exception->getMessage(),
            ]);
        }

        // Final fallback
        return $this->defaultRooms;
    }

    /**
     * API: Return meeting rooms list for frontend components.
     * Uses same display rooms logic as LCD; returns [{id, name}]
     */
    public function apiRooms()
    {
        $rooms = $this->getDisplayRooms();
        $out = [];
        foreach ($rooms as $i => $r) {
            $out[] = [
                'id' => $i + 1,
                'name' => $r,
            ];
        }

        return response()->json($out);
    }

    /**
     * Get all LCD room settings for settings page.
     */
    private function getLcdRoomSettings(): array
    {
        try {
            $settings = MeetingRoomDisplaySetting::query()
                ->orderBy('display_order')
                ->get(['room_name', 'display_order', 'is_active'])
                ->map(function ($setting) {
                    return [
                        'room_name' => $setting->room_name,
                        'display_order' => (int) $setting->display_order,
                        'is_active' => (bool) $setting->is_active,
                    ];
                })
                ->values()
                ->toArray();

            if (!empty($settings)) {
                return $settings;
            }
        } catch (Throwable $exception) {
            Log::warning('Failed to load LCD room settings data. Using default rooms.', [
                'message' => $exception->getMessage(),
            ]);
        }

        return collect($this->defaultRooms)
            ->values()
            ->map(function ($roomName, $index) {
                return [
                    'room_name' => $roomName,
                    'display_order' => $index + 1,
                    'is_active' => true,
                ];
            })
            ->toArray();
    }

    /**
     * Get global LCD settings for carousel behavior.
     */
    private function getLcdGlobalSettings(): array
    {
        $defaults = [
            'rooms_per_slide' => 2,
            'slide_interval_seconds' => 10,
        ];

        try {
            $settings = MeetingRoomLcdSetting::query()->first();

            if ($settings) {
                return [
                    'rooms_per_slide' => max((int) $settings->rooms_per_slide, 1),
                    'slide_interval_seconds' => max((int) $settings->slide_interval_seconds, 5),
                ];
            }
        } catch (Throwable $exception) {
            Log::warning('Failed to load LCD global settings. Using defaults.', [
                'message' => $exception->getMessage(),
            ]);
        }

        return $defaults;
    }

    /**
     * Render LCD maintenance page when dashboard cannot be generated.
     */
    private function renderLcdMaintenanceView(string $dashboardName, Throwable $exception)
    {
        Log::error('LCD dashboard failed and switched to maintenance mode.', [
            'dashboard' => $dashboardName,
            'message' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString(),
        ]);

        return response()->view('Meeting.lcd-maintenance', [
            'dashboardName' => $dashboardName,
            'errorAt' => now(),
        ], 503);
    }

    /**
     * Print booking details (Receptionist/Owner)
     */
    public function print($id)
    {
        $booking = MeetingRoomBooking::with(['user', 'approver'])->findOrFail($id);

        // Authorization: receptionist, admin, or owner
        if (!Auth::user()->hasRole(['administrator', 'developer', 'receptionist']) 
            && $booking->user_id != Auth::id()) {
            abort(403, 'Unauthorized');
        }

        return view('Meeting.print', compact('booking'));
    }

    /**
     * Generate monthly Excel report (Receptionist/Admin only)
     */
    public function monthlyExcelReport(Request $request)
    {
        // Authorization
        if (!Auth::user()->hasRole(['administrator', 'developer', 'receptionist'])) {
            abort(403, 'Unauthorized');
        }

        // Get month and year from request, default to current month
        $month = $request->get('month', now()->month);
        $year = $request->get('year', now()->year);

        // Validate month and year
        if ($month < 1 || $month > 12) {
            return back()->with('error', 'Invalid month');
        }

        // Generate filename with month name
        $monthName = Carbon::create($year, $month, 1)->format('F_Y');
        $filename = 'Laporan_Meeting_Room_' . $monthName . '.xlsx';

        // Export to Excel
        return Excel::download(
            new MeetingRoomMonthlyExport($month, $year), 
            $filename
        );
    }

    /**
     * Auto-finish blocking bookings that have passed their end_datetime
     * This ensures old "unavailable" markings don't persist beyond 23:59
     */
    private function autoFinishExpiredBlockings()
    {
        $now = now();
        $gracePeriod = 15; // 15 minutes grace period after meeting ends
        
        // 1. Auto-finish ALL approved bookings (regular + blocking) that have passed end_datetime + 15 minutes
        $expiredBookings = \App\MeetingRoomBooking::where('status', 'approved')
            ->where('end_datetime', '<', $now->copy()->subMinutes($gracePeriod))
            ->get();

        if ($expiredBookings->count() > 0) {
            foreach ($expiredBookings as $booking) {
                $booking->status = 'finished';
                $booking->finished_at = $booking->end_datetime; // Use original end time, not now
                $booking->save();
            }

            \Log::info('Auto-finished expired bookings', [
                'count' => $expiredBookings->count(),
                'booking_ids' => $expiredBookings->pluck('id')->toArray(),
                'grace_period_minutes' => $gracePeriod,
                'processed_at' => $now->format('Y-m-d H:i:s'),
            ]);
        }
        
        // 2. Clean up finished bookings that still have future end_datetime
        // This happens when receptionist unblocks a room but the update didn't save properly
        $inconsistentFinished = \App\MeetingRoomBooking::where('status', 'finished')
            ->where('purpose', 'LIKE', 'BLOCKED:%')
            ->where('end_datetime', '>', $now)
            ->whereNotNull('finished_at')
            ->get();
            
        if ($inconsistentFinished->count() > 0) {
            foreach ($inconsistentFinished as $booking) {
                // Set end_datetime to finished_at (the actual time it was finished)
                $booking->end_datetime = $booking->finished_at;
                $booking->save();
            }
            
            \Log::info('Fixed inconsistent finished blocking bookings', [
                'count' => $inconsistentFinished->count(),
                'booking_ids' => $inconsistentFinished->pluck('id')->toArray(),
            ]);
        }
    }
}
