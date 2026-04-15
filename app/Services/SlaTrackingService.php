<?php

namespace App\Services;

use App\Ticket;
use App\SlaPolicy;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Facades\Log;

class SlaTrackingService
{
    /**
     * Calculate SLA due date for a ticket based on its priority
     *
     * @param int $priorityId
     * @param Carbon|null $startTime
     * @return Carbon|null
     */
    public function calculateSlaDue(int $priorityId, ?Carbon $startTime = null): ?Carbon
    {
        $startTime = $startTime ?? now();
        
        // Get SLA policy for this priority
        $policy = SlaPolicy::where('priority_id', $priorityId)
                          ->where('is_active', true)
                          ->first();
        
        if (!$policy) {
            // Fallback to default SLA times if no policy exists
            return $this->getDefaultSlaDue($priorityId, $startTime);
        }
        
        // Calculate due time based on business hours or 24/7
        if ($policy->business_hours_only) {
            return $this->addBusinessMinutes($startTime, $policy->resolution_time);
        }
        
        return $startTime->copy()->addMinutes($policy->resolution_time);
    }
    
    /**
     * Calculate first response due date
     *
     * @param int $priorityId
     * @param Carbon|null $startTime
     * @return Carbon|null
     */
    public function calculateResponseDue(int $priorityId, ?Carbon $startTime = null): ?Carbon
    {
        $startTime = $startTime ?? now();
        
        $policy = SlaPolicy::where('priority_id', $priorityId)
                          ->where('is_active', true)
                          ->first();
        
        if (!$policy) {
            // Fallback to default response times
            return $this->getDefaultResponseDue($priorityId, $startTime);
        }
        
        if ($policy->business_hours_only) {
            return $this->addBusinessMinutes($startTime, $policy->response_time);
        }
        
        return $startTime->copy()->addMinutes($policy->response_time);
    }
    
    /**
     * Check if ticket has breached SLA
     *
     * @param Ticket $ticket
     * @return array
     */
    public function checkSlaBreach(Ticket $ticket): array
    {
        $result = [
            'is_breached' => false,
            'response_breached' => false,
            'resolution_breached' => false,
            'response_remaining' => null,
            'resolution_remaining' => null,
            'breach_time' => null,
        ];
        
        // Check first response breach
        if (!$ticket->first_response_at) {
            $responseDue = $this->calculateResponseDue(
                $ticket->ticket_priority_id,
                $ticket->created_at
            );
            
            if ($responseDue && now()->gt($responseDue)) {
                $result['response_breached'] = true;
                $result['is_breached'] = true;
                $result['breach_time'] = now()->diff($responseDue);
            } else {
                $result['response_remaining'] = $responseDue ? now()->diff($responseDue) : null;
            }
        }
        
        // Check resolution breach
        if (!$ticket->resolved_at && $ticket->ticket_status_id != 3) { // Not resolved
            if ($ticket->sla_due && now()->gt($ticket->sla_due)) {
                $result['resolution_breached'] = true;
                $result['is_breached'] = true;
                $result['breach_time'] = now()->diff($ticket->sla_due);
            } else {
                $result['resolution_remaining'] = $ticket->sla_due ? now()->diff($ticket->sla_due) : null;
            }
        }
        
        return $result;
    }
    
    /**
     * Get SLA status with color coding
     *
     * @param Ticket $ticket
     * @return array
     */
    public function getSlaStatus(Ticket $ticket): array
    {
        if (!$ticket->sla_due) {
            return [
                'status' => 'no_sla',
                'label' => 'No SLA',
                'color' => 'default',
                'icon' => 'fa-clock-o',
            ];
        }
        
        // If resolved, check if it was within SLA
        if ($ticket->resolved_at) {
            if ($ticket->resolved_at->lte($ticket->sla_due)) {
                return [
                    'status' => 'met',
                    'label' => 'SLA Met',
                    'color' => 'success',
                    'icon' => 'fa-check-circle',
                    'resolved_at' => $ticket->resolved_at,
                    'sla_due' => $ticket->sla_due,
                ];
            } else {
                return [
                    'status' => 'breached',
                    'label' => 'SLA Breached',
                    'color' => 'danger',
                    'icon' => 'fa-exclamation-triangle',
                    'breach_time' => $ticket->resolved_at->diff($ticket->sla_due),
                ];
            }
        }
        
        // Still open - check if breached or warning
        $now = now();
        $timeRemaining = $now->diff($ticket->sla_due);
        $percentageRemaining = $this->calculatePercentageRemaining($ticket->created_at, $ticket->sla_due);
        
        if ($now->gt($ticket->sla_due)) {
            return [
                'status' => 'breached',
                'label' => 'SLA Breached',
                'color' => 'danger',
                'icon' => 'fa-exclamation-triangle',
                'breach_time' => $timeRemaining,
            ];
        } elseif ($percentageRemaining < 20) {
            return [
                'status' => 'critical',
                'label' => 'Critical',
                'color' => 'warning',
                'icon' => 'fa-exclamation-circle',
                'time_remaining' => $timeRemaining,
                'percentage_remaining' => $percentageRemaining,
            ];
        } elseif ($percentageRemaining < 50) {
            return [
                'status' => 'warning',
                'label' => 'Warning',
                'color' => 'yellow',
                'icon' => 'fa-clock-o',
                'time_remaining' => $timeRemaining,
                'percentage_remaining' => $percentageRemaining,
            ];
        } else {
            return [
                'status' => 'on_track',
                'label' => 'On Track',
                'color' => 'info',
                'icon' => 'fa-check',
                'time_remaining' => $timeRemaining,
                'percentage_remaining' => $percentageRemaining,
            ];
        }
    }
    
    /**
     * Update ticket with first response time
     *
     * @param Ticket $ticket
     * @return void
     */
    public function recordFirstResponse(Ticket $ticket): void
    {
        if (!$ticket->first_response_at) {
            $ticket->first_response_at = now();
            $ticket->save();
            
            Log::info("First response recorded for ticket {$ticket->ticket_code}");
        }
    }
    
    /**
     * Check for SLA escalation
     *
     * @param Ticket $ticket
     * @return bool
     */
    public function checkEscalation(Ticket $ticket): bool
    {
        $policy = SlaPolicy::where('priority_id', $ticket->ticket_priority_id)
                          ->where('is_active', true)
                          ->first();
        
        if (!$policy || !$policy->escalation_time || !$policy->escalate_to_user_id) {
            return false;
        }
        
        $escalationDue = $ticket->created_at->copy()->addMinutes($policy->escalation_time);
        
        if (now()->gt($escalationDue) && !$ticket->resolved_at) {
            $this->escalateTicket($ticket, $policy);
            return true;
        }
        
        return false;
    }
    
    /**
     * Escalate ticket to designated user
     *
     * @param Ticket $ticket
     * @param SlaPolicy $policy
     * @return void
     */
    protected function escalateTicket(Ticket $ticket, SlaPolicy $policy): void
    {
        // Assign to escalation user
        $ticket->assigned_to = $policy->escalate_to_user_id;
        $ticket->assignment_type = 'escalated';
        $ticket->assigned_at = now();
        $ticket->save();
        
        Log::info("Ticket {$ticket->ticket_code} escalated to user {$policy->escalate_to_user_id}");
    }
    
    /**
     * Get SLA metrics for dashboard
     *
     * @param array $filters
     * @return array
     */
    public function getSlaMetrics(array $filters = []): array
    {
        $query = Ticket::query();
        
        // Apply filters
        if (isset($filters['start_date'])) {
            $query->where('created_at', '>=', $filters['start_date']);
        }
        if (isset($filters['end_date'])) {
            $query->where('created_at', '<=', $filters['end_date']);
        }
        if (isset($filters['priority_id'])) {
            $query->where('ticket_priority_id', $filters['priority_id']);
        }
        if (isset($filters['assigned_to'])) {
            $query->where('assigned_to', $filters['assigned_to']);
        }
        
        $tickets = $query->get();
        
        $totalTickets = $tickets->count();
        $resolvedTickets = $tickets->where('resolved_at', '!=', null)->count();
        $slaMet = 0;
        $slaBreached = 0;
        $criticalTickets = 0;
        $avgResolutionTime = 0;
        $avgResponseTime = 0;
        
        $resolutionTimes = [];
        $responseTimes = [];
        
        foreach ($tickets as $ticket) {
            $slaStatus = $this->getSlaStatus($ticket);
            
            if ($slaStatus['status'] === 'met') {
                $slaMet++;
            } elseif ($slaStatus['status'] === 'breached') {
                $slaBreached++;
            } elseif ($slaStatus['status'] === 'critical') {
                $criticalTickets++;
            }
            
            // Calculate resolution time
            if ($ticket->resolved_at) {
                $resolutionMinutes = $ticket->created_at->diffInMinutes($ticket->resolved_at);
                $resolutionTimes[] = $resolutionMinutes;
            }
            
            // Calculate response time
            if ($ticket->first_response_at) {
                $responseMinutes = $ticket->created_at->diffInMinutes($ticket->first_response_at);
                $responseTimes[] = $responseMinutes;
            }
        }
        
        if (count($resolutionTimes) > 0) {
            $avgResolutionTime = array_sum($resolutionTimes) / count($resolutionTimes);
        }
        
        if (count($responseTimes) > 0) {
            $avgResponseTime = array_sum($responseTimes) / count($responseTimes);
        }
        
        $slaComplianceRate = $resolvedTickets > 0 ? 
                            ($slaMet / $resolvedTickets) * 100 : 0;
        
        return [
            'total_tickets' => $totalTickets,
            'resolved_tickets' => $resolvedTickets,
            'sla_met' => $slaMet,
            'sla_breached' => $slaBreached,
            'critical_tickets' => $criticalTickets,
            'sla_compliance_rate' => round($slaComplianceRate, 2),
            'avg_resolution_time_minutes' => round($avgResolutionTime, 2),
            'avg_resolution_time_hours' => round($avgResolutionTime / 60, 2),
            'avg_response_time_minutes' => round($avgResponseTime, 2),
            'avg_response_time_hours' => round($avgResponseTime / 60, 2),
        ];
    }
    
    /**
     * Get default SLA due time (fallback when no policy exists)
     *
     * @param int $priorityId
     * @param Carbon $startTime
     * @return Carbon
     */
    protected function getDefaultSlaDue(int $priorityId, Carbon $startTime): Carbon
    {
        $hours = [
            1 => 4,   // Urgent: 4 hours
            2 => 24,  // High: 1 day
            3 => 72,  // Medium: 3 days
            4 => 168, // Low: 1 week
        ];
        
        return $startTime->copy()->addHours($hours[$priorityId] ?? 72);
    }
    
    /**
     * Get default response due time (fallback)
     *
     * @param int $priorityId
     * @param Carbon $startTime
     * @return Carbon
     */
    protected function getDefaultResponseDue(int $priorityId, Carbon $startTime): Carbon
    {
        $hours = [
            1 => 1,   // Urgent: 1 hour
            2 => 4,   // High: 4 hours
            3 => 24,  // Medium: 1 day
            4 => 48,  // Low: 2 days
        ];
        
        return $startTime->copy()->addHours($hours[$priorityId] ?? 24);
    }
    
    /**
     * Add business minutes to a datetime (M-F, 8am-5pm)
     *
     * @param Carbon $startTime
     * @param int $minutes
     * @return Carbon
     */
    protected function addBusinessMinutes(Carbon $startTime, int $minutes): Carbon
    {
        $time = $startTime->copy();
        $minutesRemaining = $minutes;
        
        while ($minutesRemaining > 0) {
            // Skip to next business day if weekend
            if ($time->isWeekend()) {
                $time->next(Carbon::MONDAY)->setTime(8, 0);
            }
            
            // Set to business hours if outside
            if ($time->hour < 8) {
                $time->setTime(8, 0);
            } elseif ($time->hour >= 17) {
                $time->addDay()->setTime(8, 0);
                if ($time->isWeekend()) {
                    $time->next(Carbon::MONDAY);
                }
            }
            
            // Calculate minutes until end of business day
            $endOfDay = $time->copy()->setTime(17, 0);
            $minutesUntilEnd = $time->diffInMinutes($endOfDay);
            
            if ($minutesRemaining <= $minutesUntilEnd) {
                $time->addMinutes($minutesRemaining);
                $minutesRemaining = 0;
            } else {
                $minutesRemaining -= $minutesUntilEnd;
                $time->addDay()->setTime(8, 0);
            }
        }
        
        return $time;
    }
    
    /**
     * Calculate percentage of time remaining
     *
     * @param Carbon $startTime
     * @param Carbon $dueTime
     * @return float
     */
    protected function calculatePercentageRemaining(Carbon $startTime, Carbon $dueTime): float
    {
        $totalMinutes = $startTime->diffInMinutes($dueTime);
        $elapsedMinutes = $startTime->diffInMinutes(now());
        
        if ($totalMinutes == 0) {
            return 0;
        }
        
        $percentage = (($totalMinutes - $elapsedMinutes) / $totalMinutes) * 100;
        
        return max(0, $percentage);
    }
}
