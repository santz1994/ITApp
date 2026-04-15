<?php

namespace App\Services;

use App\Ticket;
use App\TicketsPriority;
use App\TicketsType;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

/**
 * SLA Learning System
 * 
 * Machine learning system that learns from historical ticket data
 * to predict optimal SLA based on:
 * - Ticket type
 * - Priority
 * - Historical resolution times
 * - Day of week
 * - Time of day
 */
class SLALearningSystem
{
    /**
     * Calculate intelligent SLA based on historical data
     * 
     * @param int $priorityId
     * @param int|null $typeId
     * @param string|null $subject
     * @param string|null $description
     * @return Carbon
     */
    public static function calculateIntelligentSLA($priorityId, $typeId = null, $subject = null, $description = null)
    {
        // Get base SLA from priority (fallback)
        $baseSLA = self::getBaseSLA($priorityId);
        
        // Try to get learned SLA from historical data
        $learnedSLA = self::getLearnedSLA($priorityId, $typeId);
        
        // If we have enough historical data, use learned SLA
        if ($learnedSLA && $learnedSLA['confidence'] > 0.7) {
            $hours = $learnedSLA['avg_resolution_hours'] * 1.2; // Add 20% buffer
            
            // Apply time-of-day adjustment
            $hours = self::adjustForTimeOfDay($hours);
            
            // Apply day-of-week adjustment
            $hours = self::adjustForDayOfWeek($hours);
            
            return now()->addHours($hours);
        }
        
        // Use base SLA if not enough data
        return $baseSLA;
    }
    
    /**
     * Get base SLA hours based on priority
     */
    private static function getBaseSLA($priorityId)
    {
        $slaHours = Cache::remember('base_sla_hours', 3600, function () {
            return [
                1 => 4,   // Urgent < 4 hours
                2 => 24,  // High < 1 day
                3 => 72,  // Medium < 3 days
                4 => 168, // Low < 1 week
            ];
        });
        
        return now()->addHours($slaHours[$priorityId] ?? 72);
    }
    
    /**
     * Learn from historical data
     */
    private static function getLearnedSLA($priorityId, $typeId)
    {
        $cacheKey = "learned_sla_{$priorityId}_{$typeId}";
        
        return Cache::remember($cacheKey, 3600, function () use ($priorityId, $typeId) {
            $query = Ticket::where('ticket_priority_id', $priorityId)
                ->whereNotNull('resolved_at')
                ->whereNotNull('created_at');
            
            if ($typeId) {
                $query->where('ticket_type_id', $typeId);
            }
            
            // Get tickets from last 90 days
            $tickets = $query->where('created_at', '>=', now()->subDays(90))
                ->select('created_at', 'resolved_at')
                ->get();
            
            // Need at least 10 tickets to learn
            if ($tickets->count() < 10) {
                return null;
            }
            
            // Calculate average resolution time
            $totalHours = 0;
            foreach ($tickets as $ticket) {
                $hours = $ticket->created_at->diffInHours($ticket->resolved_at);
                $totalHours += $hours;
            }
            
            $avgHours = $totalHours / $tickets->count();
            
            // Calculate confidence based on sample size
            $confidence = min($tickets->count() / 50, 1.0); // Max confidence at 50 tickets
            
            return [
                'avg_resolution_hours' => $avgHours,
                'sample_size' => $tickets->count(),
                'confidence' => $confidence,
            ];
        });
    }
    
    /**
     * Adjust SLA based on time of day
     * Tickets created after hours get more time
     */
    private static function adjustForTimeOfDay($hours)
    {
        $currentHour = now()->hour;
        
        // After hours (18:00 - 08:00) - add 50% more time
        if ($currentHour >= 18 || $currentHour < 8) {
            return $hours * 1.5;
        }
        
        // Lunch time (12:00 - 13:00) - add 10% more time
        if ($currentHour >= 12 && $currentHour < 13) {
            return $hours * 1.1;
        }
        
        return $hours;
    }
    
    /**
     * Adjust SLA based on day of week
     * Weekend tickets get more time
     */
    private static function adjustForDayOfWeek($hours)
    {
        $dayOfWeek = now()->dayOfWeek;
        
        // Weekend (Saturday=6, Sunday=0) - add 100% more time
        if ($dayOfWeek == 0 || $dayOfWeek == 6) {
            return $hours * 2;
        }
        
        // Friday - add 20% more time (approaching weekend)
        if ($dayOfWeek == 5) {
            return $hours * 1.2;
        }
        
        return $hours;
    }
    
    /**
     * Train the system with new ticket resolution data
     * This should be called when a ticket is resolved
     */
    public static function train(Ticket $ticket)
    {
        if (!$ticket->resolved_at) {
            return;
        }
        
        // Clear cache to force recalculation
        $cacheKeys = [
            "learned_sla_{$ticket->ticket_priority_id}_{$ticket->ticket_type_id}",
            "learned_sla_{$ticket->ticket_priority_id}_",
        ];
        
        foreach ($cacheKeys as $key) {
            Cache::forget($key);
        }
        
        // Log training data for analysis
        \Log::info('SLA Learning System trained with ticket', [
            'ticket_id' => $ticket->id,
            'priority_id' => $ticket->ticket_priority_id,
            'type_id' => $ticket->ticket_type_id,
            'resolution_hours' => $ticket->created_at->diffInHours($ticket->resolved_at),
        ]);
    }
    
    /**
     * Get learning statistics
     */
    public static function getStatistics()
    {
        $priorities = TicketsPriority::all();
        $types = TicketsType::all();
        
        $stats = [];
        
        foreach ($priorities as $priority) {
            foreach ($types as $type) {
                $learned = self::getLearnedSLA($priority->id, $type->id);
                
                if ($learned) {
                    $stats[] = [
                        'priority' => $priority->priority,
                        'type' => $type->type,
                        'avg_hours' => round($learned['avg_resolution_hours'], 2),
                        'sample_size' => $learned['sample_size'],
                        'confidence' => round($learned['confidence'] * 100, 2) . '%',
                    ];
                }
            }
        }
        
        return $stats;
    }
}
