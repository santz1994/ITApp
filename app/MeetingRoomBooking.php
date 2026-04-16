<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class MeetingRoomBooking extends Model
{
    protected $fillable = [
        'room_name',
        'room_id',
        'user_id',
        'requester_name', // Name of person requesting (can differ from user account)
        'department', // Bagian/Departemen
        'requester_position', // Jabatan Pemohon
        'start_datetime',
        'start_time',
        'end_datetime',
        'end_time',
        'purpose',
        'meeting_description', // Deskripsi/Keterangan Rapat
        'meeting_needs', // Keperluan Rapat
        'attendees_count',
        'status',
        'director_notes',
        'approved_by',
        'approved_at',
        'manager_id',
        'manager_approved_at',
        'finished_at',
    ];

    protected $casts = [
        'room_id' => 'integer',
        'start_datetime' => 'datetime',
        'start_time' => 'datetime',
        'end_datetime' => 'datetime',
        'end_time' => 'datetime',
        'approved_at' => 'datetime',
        'manager_approved_at' => 'datetime',
        'finished_at' => 'datetime',
        'attendees_count' => 'integer',
    ];

    /**
     * Get the user who created the booking
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get the room master data for this booking.
     */
    public function meetingRoom()
    {
        return $this->belongsTo(MeetingRoom::class, 'room_id');
    }

    /**
     * Get the director who approved/rejected the booking
     */
    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Get the manager who acknowledged the booking (Mengetahui)
     */
    public function manager()
    {
        return $this->belongsTo(User::class, 'manager_id');
    }

    /**
     * Scope for pending bookings
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope for approved bookings
     */
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    /**
     * Scope for rejected bookings
     */
    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }

    /**
     * Scope for upcoming bookings
     */
    public function scopeUpcoming($query)
    {
        return $query->where('start_datetime', '>=', now());
    }

    /**
     * Get status badge color
     */
    public function getStatusBadgeAttribute()
    {
        return [
            'pending' => 'bg-yellow',
            'approved' => 'bg-green',
            'rejected' => 'bg-red',
            'cancelled' => 'bg-gray',
            'finished' => 'bg-blue',
        ][$this->status] ?? 'bg-gray';
    }

    /**
     * Get duration in hours or minutes
     * Returns formatted string: "2 jam" or "30 menit"
     */
    public function getDurationAttribute()
    {
        $minutes = $this->start_datetime->diffInMinutes($this->end_datetime);
        
        if ($minutes >= 60) {
            $hours = floor($minutes / 60);
            $remainingMinutes = $minutes % 60;
            
            if ($remainingMinutes > 0) {
                return $hours . ' jam ' . $remainingMinutes . ' menit';
            }
            return $hours . ' jam';
        }
        
        return $minutes . ' menit';
    }

    /**
     * Check if booking can be edited (by user)
     */
    public function canBeEdited()
    {
        return $this->status === 'pending' && $this->start_datetime->isFuture();
    }

    /**
     * Check if booking can be edited by receptionist
     * Receptionist can edit approved bookings
     */
    public function canBeEditedByReceptionist()
    {
        return in_array($this->status, ['pending', 'approved']) && $this->start_datetime->isFuture();
    }

    /**
     * Check if booking can be cancelled
     */
    public function canBeCancelled()
    {
        return in_array($this->status, ['pending', 'approved']) && $this->start_datetime->isFuture();
    }

    /**
     * Check if booking can be marked as finished
     * Can finish if: approved status AND meeting has started (allow early finish)
     */
    public function canBeFinished()
    {
        return $this->status === 'approved' && $this->start_datetime->isPast();
    }

    /**
     * Check if booking can be approved
     */
    public function canBeApproved()
    {
        return $this->status === 'pending';
    }
}
