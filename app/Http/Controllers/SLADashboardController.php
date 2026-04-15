<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\SLALearningSystem;
use App\Ticket;
use App\TicketsPriority;
use App\TicketsType;
use Illuminate\Support\Facades\DB;

class SLADashboardController extends Controller
{
    /**
     * Display SLA learning system statistics
     */
    public function index()
    {
        // Get learning statistics
        $statistics = SLALearningSystem::getStatistics();
        
        // Get overall ticket resolution metrics
        $metrics = $this->getOverallMetrics();
        
        // Get recent learning updates
        $recentTickets = Ticket::whereNotNull('resolved_at')
            ->with(['ticket_priority', 'ticket_type'])
            ->orderBy('resolved_at', 'desc')
            ->limit(10)
            ->get()
            ->map(function($ticket) {
                return [
                    'ticket_code' => $ticket->ticket_code,
                    'priority' => $ticket->ticket_priority->priority ?? 'N/A',
                    'type' => $ticket->ticket_type->type ?? 'N/A',
                    'resolution_hours' => $ticket->created_at->diffInHours($ticket->resolved_at),
                    'resolved_at' => $ticket->resolved_at->format('d M Y H:i'),
                ];
            });
        
        $pageTitle = 'SLA Learning System Dashboard';
        
        return view('admin.sla-learning.index', compact('statistics', 'metrics', 'recentTickets', 'pageTitle'));
    }
    
    /**
     * Get overall ticket resolution metrics
     */
    private function getOverallMetrics()
    {
        $resolvedTickets = Ticket::whereNotNull('resolved_at')
            ->whereNotNull('created_at')
            ->where('created_at', '>=', now()->subDays(90))
            ->select('ticket_priority_id', DB::raw('COUNT(*) as count'), DB::raw('AVG(TIMESTAMPDIFF(HOUR, created_at, resolved_at)) as avg_hours'))
            ->groupBy('ticket_priority_id')
            ->get();
        
        $priorities = TicketsPriority::all()->keyBy('id');
        
        $metrics = [];
        foreach ($resolvedTickets as $ticket) {
            $priority = $priorities->get($ticket->ticket_priority_id);
            if ($priority) {
                $metrics[] = [
                    'priority' => $priority->priority,
                    'count' => $ticket->count,
                    'avg_hours' => round($ticket->avg_hours, 2),
                ];
            }
        }
        
        return $metrics;
    }
}
