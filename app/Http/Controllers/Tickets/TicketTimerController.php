<?php

namespace App\Http\Controllers\Tickets;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\TicketService;
use App\Ticket;
use App\DailyActivity;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * TicketTimerController
 * 
 * Handles time tracking functionality for technicians working on tickets.
 * Manages timer start/stop, status tracking, and work summary reporting.
 */
class TicketTimerController extends Controller
{
    protected $ticketService;

    public function __construct(TicketService $ticketService)
    {
        $this->middleware('auth');
        $this->ticketService = $ticketService;
    }

    /**
     * Start timer for technician work on ticket
     * 
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Ticket  $ticket
     * @return \Illuminate\Http\JsonResponse
     */
    public function startTimer(Request $request, Ticket $ticket)
    {
        $userId = auth()->id();
        $sessionKey = "ticket_timer_{$ticket->id}_{$userId}";
        
        // Check if timer is already running
        if (session()->has($sessionKey)) {
            return response()->json([
                'success' => false,
                'message' => 'Timer sudah berjalan untuk tiket ini'
            ]);
        }

        // Store timer start time in session
        session()->put($sessionKey, [
            'start_time' => now(),
            'ticket_id' => $ticket->id,
            'user_id' => $userId,
            'description' => $request->input('description', 'Bekerja pada tiket: ' . $ticket->subject)
        ]);

        // Add ticket entry for timer start
        $this->ticketService->addTicketEntry($ticket, $userId, "Timer dimulai: " . $request->input('description', 'Mulai bekerja pada tiket ini'));

        return response()->json([
            'success' => true,
            'message' => 'Timer berhasil dimulai',
            'start_time' => now()->toISOString()
        ]);
    }

    /**
     * Stop timer and log activity
     * 
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Ticket  $ticket
     * @return \Illuminate\Http\JsonResponse
     */
    public function stopTimer(Request $request, Ticket $ticket)
    {
        $userId = auth()->id();
        $sessionKey = "ticket_timer_{$ticket->id}_{$userId}";
        
        // Check if timer is running
        if (!session()->has($sessionKey)) {
            return response()->json([
                'success' => false,
                'message' => 'Timer tidak ditemukan atau sudah dihentikan'
            ]);
        }

        $timerData = session()->get($sessionKey);
        $startTime = Carbon::parse($timerData['start_time']);
        $endTime = now();
        $durationMinutes = $startTime->diffInMinutes($endTime);

        // Validate minimum work time (e.g., at least 1 minute)
        if ($durationMinutes < 1) {
            return response()->json([
                'success' => false,
                'message' => 'Durasi kerja minimal 1 menit'
            ]);
        }

        try {
            DB::transaction(function () use ($ticket, $userId, $timerData, $durationMinutes, $request, $startTime, $endTime) {
                // Create daily activity
                DailyActivity::create([
                    'user_id' => $userId,
                    'activity_date' => $startTime->toDateString(),
                    'description' => $timerData['description'],
                    'ticket_id' => $ticket->id,
                    'type' => 'timer_tracking',
                    'duration_minutes' => $durationMinutes,
                    'notes' => $request->input('notes', ''),
                    'status' => 'completed'
                ]);

                // Add ticket entry with work summary
                $workSummary = $request->input('work_summary', 'Menyelesaikan pekerjaan pada tiket');
                $entryMessage = "Timer dihentikan (Durasi: {$durationMinutes} menit)\n\nRingkasan Pekerjaan:\n{$workSummary}";
                
                if ($request->input('notes')) {
                    $entryMessage .= "\n\nCatatan:\n" . $request->input('notes');
                }

                $this->ticketService->addTicketEntry($ticket, $userId, $entryMessage);

                // Update ticket status if requested
                if ($request->input('status_change')) {
                    $this->ticketService->updateTicketStatus($ticket, $request->input('status_change'), $userId);
                }
            });

            // Remove timer from session
            session()->forget($sessionKey);

            return response()->json([
                'success' => true,
                'message' => 'Timer berhasil dihentikan dan aktivitas telah dicatat',
                'duration_minutes' => $durationMinutes,
                'duration_formatted' => $this->formatDuration($durationMinutes)
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghentikan timer: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Get current timer status
     * 
     * @param  \App\Ticket  $ticket
     * @return \Illuminate\Http\JsonResponse
     */
    public function getTimerStatus(Ticket $ticket)
    {
        $userId = auth()->id();
        $sessionKey = "ticket_timer_{$ticket->id}_{$userId}";
        
        if (!session()->has($sessionKey)) {
            return response()->json([
                'is_running' => false
            ]);
        }

        $timerData = session()->get($sessionKey);
        $startTime = Carbon::parse($timerData['start_time']);
        $currentDuration = $startTime->diffInMinutes(now());

        return response()->json([
            'is_running' => true,
            'start_time' => $startTime->toISOString(),
            'duration_minutes' => $currentDuration,
            'duration_formatted' => $this->formatDuration($currentDuration),
            'description' => $timerData['description']
        ]);
    }

    /**
     * Get ticket work summary (time spent by all technicians)
     * 
     * @param  \App\Ticket  $ticket
     * @return \Illuminate\Http\JsonResponse
     */
    public function getWorkSummary(Ticket $ticket)
    {
        $activities = DailyActivity::where('ticket_id', $ticket->id)
                                  ->with('user')
                                  ->orderBy('activity_date', 'desc')
                                  ->get();

        $totalMinutes = $activities->sum('duration_minutes');
        $workByTechnician = $activities->groupBy('user_id')
                                     ->map(function ($userActivities) {
                                         $user = $userActivities->first()->user;
                                         return [
                                             'name' => $user->name,
                                             'total_minutes' => $userActivities->sum('duration_minutes'),
                                             'activities_count' => $userActivities->count(),
                                             'last_activity' => $userActivities->first()->activity_date->format('d M Y')
                                         ];
                                     });

        return response()->json([
            'total_minutes' => $totalMinutes,
            'total_formatted' => $this->formatDuration($totalMinutes),
            'work_by_technician' => $workByTechnician,
            'activities' => $activities->map(function ($activity) {
                return [
                    'date' => $activity->activity_date->format('d M Y'),
                    'duration' => $activity->duration_minutes,
                    'duration_formatted' => $this->formatDuration($activity->duration_minutes),
                    'description' => $activity->description,
                    'technician' => $activity->user->name,
                    'notes' => $activity->notes
                ];
            })
        ]);
    }

    /**
     * Format duration in minutes to readable format
     * 
     * @param  int  $minutes
     * @return string
     */
    private function formatDuration($minutes)
    {
        if ($minutes < 60) {
            return $minutes . ' menit';
        }

        $hours = floor($minutes / 60);
        $mins = $minutes % 60;

        if ($hours == 1) {
            return $mins > 0 ? "1 jam {$mins} menit" : "1 jam";
        }

        return $mins > 0 ? "{$hours} jam {$mins} menit" : "{$hours} jam";
    }
}
