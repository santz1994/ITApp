<?php

namespace App\Services;

use App\Repositories\Tickets\TicketRepositoryInterface;
use App\Ticket;
use App\DailyActivity;
use App\User;
use App\TicketsStatus;
use App\TicketsType;
use App\TicketComment;
use App\AdminOnlineStatus;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use App\Notifications\TicketCreated;
use App\Notifications\TicketAssigned;
use App\Notifications\TicketUpdated;

class TicketService
{
    protected $ticketRepository;

    /**
     * Constructor
     */
    public function __construct(TicketRepositoryInterface $ticketRepository)
    {
        $this->ticketRepository = $ticketRepository;
    }
    /**
     * Generate unique ticket code: Prefix-Date-SequentialNumber
     */
    public function generateTicketCode($prefix = 'TIK')
    {
        $date = Carbon::now()->format('Ymd');
        
        // Get today's ticket count for sequential number
        $todayTicketCount = $this->ticketRepository->getTodayTicketCount();
        $sequentialNumber = str_pad($todayTicketCount + 1, 3, '0', STR_PAD_LEFT);
        
        return "{$prefix}-{$date}-{$sequentialNumber}";
    }

    /**
     * Auto-assign ticket to available admin (simplified version)
     */
    public function autoAssignTicketSimple(Ticket $ticket)
    {
        // Get available admins (users with admin or super-admin role)
        $admins = User::role(['admin', 'super-admin'])->get();
        
        if ($admins->isNotEmpty()) {
            $randomAdmin = $admins->random();
            
            $ticket->update([
                'assigned_to' => $randomAdmin->id,
                'assigned_at' => Carbon::now(),
                'assignment_type' => 'auto'
            ]);
            
            return true;
        }
        
        return false;
    }

    /**
     * Create a new ticket with auto-assignment and intelligent defaults
     */
    public function createTicket(array $data)
    {
        return DB::transaction(function () use ($data) {
            // Remove sla_due from input data if present (will be calculated by SLA Learning System)
            unset($data['sla_due']);

            $smartRecommendation = null;
            if (empty($data['ticket_priority_id']) || empty($data['ticket_type_id'])) {
                $smartRecommendation = app(SmartTicketIntakeService::class)->analyze(
                    (string) ($data['subject'] ?? ''),
                    (string) ($data['description'] ?? ''),
                    false
                );
            }
            
            // Generate ticket code
            $data['ticket_code'] = $this->generateTicketCode();

            // AUTO-DETECT PRIORITY from subject and description if not provided
            if (empty($data['ticket_priority_id'])) {
                $data['ticket_priority_id'] = data_get($smartRecommendation, 'recommended.ticket_priority_id')
                    ?? \App\Services\TicketPriorityDetector::detectPriority(
                        $data['subject'] ?? '',
                        $data['description'] ?? ''
                    );

                Log::info('Auto-detected ticket priority', [
                    'priority_id' => $data['ticket_priority_id'],
                    'subject' => $data['subject'] ?? ''
                ]);
            }

            // AUTO-DETECT TYPE from subject and description if not provided
            if (empty($data['ticket_type_id'])) {
                $data['ticket_type_id'] = data_get($smartRecommendation, 'recommended.ticket_type_id')
                    ?? $this->getDefaultTicketTypeId();

                Log::info('Auto-detected ticket type', [
                    'type_id' => $data['ticket_type_id'],
                    'subject' => $data['subject'] ?? ''
                ]);
            }

            // Auto-set location from user's location if not provided
            if (empty($data['location_id']) && !empty($data['user_id'])) {
                $user = User::find($data['user_id']);
                if ($user && $user->location_id) {
                    $data['location_id'] = $user->location_id;
                    Log::info('Auto-set ticket location from user location', [
                        'user_id' => $user->id,
                        'location_id' => $user->location_id
                    ]);
                }
            }

            // Always set status to 'Open' for new tickets
            $data['ticket_status_id'] = $this->getStatusId('Open');

            // PRE-CALCULATE INTELLIGENT SLA before creating ticket
            // This ensures sla_due is set during ticket creation, not after
            $intelligentSLA = \App\Services\SLALearningSystem::calculateIntelligentSLA(
                $data['ticket_priority_id'],
                $data['ticket_type_id'] ?? null,
                $data['subject'] ?? '',
                $data['description'] ?? ''
            );
            $data['sla_due'] = $intelligentSLA;

            // PRE-SELECT agent for auto-assignment (Ridwan or Idol only)
            $agents = User::whereIn('name', ['Ridwan', 'Idol'])->get();
            if ($agents->isNotEmpty()) {
                $selectedAgent = $agents->random();
                $data['assigned_to'] = $selectedAgent->id;
                $data['assigned_at'] = now();
                $data['assignment_type'] = 'auto';
            }

            // Create ticket with SLA and assignment already set
            $ticket = $this->ticketRepository->create($data);
            
            // Send notification to assigned agent if assigned
            if ($ticket->assigned_to) {
                try {
                    $admin = User::find($ticket->assigned_to);
                    if ($admin) {
                        $assignedBy = Auth::user() ?? User::find($ticket->user_id);
                        $admin->notify(new \App\Notifications\TicketAssigned($ticket, $assignedBy));
                        
                        // Add automatic ticket entry for assignment
                        $this->addTicketEntry($ticket, $admin->id, "Tiket telah ditugaskan kepada {$admin->name}");
                    }
                } catch (\Exception $e) {
                    Log::error('Failed to send ticket assigned notification', [
                        'ticket_id' => $ticket->id,
                        'error' => $e->getMessage()
                    ]);
                }
            }
            
            // Log maintenance activity if ticket is related to an asset
            // Support multiple assets via pivot 'ticket_assets'. Backfill from single asset_id if present.
            try {
                if (!empty($data['asset_ids']) && is_array($data['asset_ids'])) {
                    $ticket->assets()->sync(array_values($data['asset_ids']));
                } elseif (!empty($data['asset_id'])) {
                    // attach the single asset id (keep for backwards compat)
                    $ticket->assets()->syncWithoutDetaching([$data['asset_id']]);
                }

                // Log maintenance activity for each attached asset (if any)
                foreach ($ticket->assets as $asset) {
                    try {
                        $asset->logMaintenanceActivity("Ticket created: {$ticket->subject}", $ticket->user_id);
                    } catch (\Exception $e) {
                        // ignore per-asset logging errors
                    }
                }
            } catch (\Exception $e) {
                Log::warning('Failed to attach assets to ticket during createTicket: ' . $e->getMessage());
            }
            
            // Send notification to user
            try {
                $ticket->user->notify(new TicketCreated($ticket));
            } catch (\Exception $e) {
                Log::error('Failed to send ticket created notification', [
                    'ticket_id' => $ticket->id,
                    'user_id' => $ticket->user_id,
                    'error' => $e->getMessage()
                ]);
            }
            
            return $ticket;
        });
    }

    /**
     * Auto assign ticket to Ridwan or Idol (round-robin or random)
     */
    public function autoAssignTicket(Ticket $ticket)
    {
        // Only assign to Ridwan or Idol
        $agents = User::whereIn('name', ['Ridwan', 'Idol'])->get();
        
        if ($agents->isEmpty()) {
            Log::warning("No agents (Ridwan/Idol) available for ticket {$ticket->ticket_code}");
            return false;
        }

        // Random assignment between Ridwan and Idol
        $selectedAgent = $agents->random();
        
        return $this->assignTicket($ticket, $selectedAgent->id, 'auto');
    }

    /**
     * Manually assign ticket
     */
    public function assignTicket(Ticket $ticket, $adminId, $type = 'manual')
    {
        $admin = User::find($adminId);
        
        // Only allow assignment to Ridwan or Idol
        if (!$admin || !in_array($admin->name, ['Ridwan', 'Idol'])) {
            throw new \Exception('Tickets can only be assigned to Ridwan or Idol');
        }

        $ticket->update([
            'assigned_to' => $adminId,
            'assigned_at' => now(),
            'assignment_type' => $type,
            'ticket_status_id' => $this->getStatusId('In Progress') // Auto update to In Progress
        ]);

        // Add automatic ticket entry for assignment
        $this->addTicketEntry($ticket, $admin->id, "Tiket telah ditugaskan kepada {$admin->name}");

        // Send notification to assigned admin
        try {
            $assignedBy = Auth::user(); // Who assigned the ticket
            $admin->notify(new TicketAssigned($ticket, $assignedBy));
        } catch (\Exception $e) {
            Log::error('Failed to send ticket assigned notification', [
                'ticket_id' => $ticket->id,
                'admin_id' => $adminId,
                'error' => $e->getMessage()
            ]);
        }

        // Also notify the ticket creator
        try {
            $ticket->user->notify(new TicketUpdated($ticket, 'status', "Your ticket has been assigned to {$admin->name}"));
        } catch (\Exception $e) {
            Log::error('Failed to send ticket assignment notification to user', [
                'ticket_id' => $ticket->id,
                'user_id' => $ticket->user_id,
                'error' => $e->getMessage()
            ]);
        }

        Log::info("Ticket {$ticket->ticket_code} assigned to admin {$admin->name}");
        
        return true;
    }

    /**
     * Self-assign ticket by admin
     */
    public function selfAssignTicket(Ticket $ticket, $adminId)
    {
        if ($ticket->assigned_to) {
            throw new \Exception('Ticket already assigned');
        }

        return $this->assignTicket($ticket, $adminId, 'manual');
    }

    /**
     * Complete ticket and create daily activity
     */
    public function completeTicket(Ticket $ticket, $resolution = null)
    {
        return DB::transaction(function () use ($ticket, $resolution) {
            $ticket->update([
                'resolved_at' => now(),
                'ticket_status_id' => 3, // Assuming 3 = Resolved
                'closed' => now()
            ]);

            // Train SLA Learning System with this ticket's resolution data
            \App\Services\SLALearningSystem::train($ticket);

            // Auto-create daily activity
            if ($ticket->assigned_to) {
                DailyActivity::createFromTicketCompletion($ticket);
            }

            return $ticket;
        });
    }

    /**
     * Get tickets near SLA deadline
     */
    public function getTicketsNearDeadline($hours = 2)
    {
        return $this->ticketRepository->getTicketsNearDeadline($hours);
    }

    /**
     * Get overdue tickets
     */
    public function getOverdueTickets()
    {
        return $this->ticketRepository->getOverdueTickets();
    }

    /**
     * Get unassigned tickets
     */
    public function getUnassignedTickets()
    {
        return $this->ticketRepository->getUnassigned();
    }

    /**
     * Update admin activity status
     */
    public function updateAdminActivity($userId)
    {
        return AdminOnlineStatus::updateActivity($userId);
    }

    /**
     * Get admin performance metrics
     */
    public function getAdminPerformance($adminId, $startDate = null, $endDate = null)
    {
        // If no date range specified, get ALL tickets assigned to this agent
        if (!$startDate && !$endDate) {
            $tickets = Ticket::where('assigned_to', $adminId);
            
            return [
                'total_assigned' => $tickets->count(),
                'completed' => $tickets->whereNotNull('resolved_at')->count(),
                'overdue' => Ticket::where('assigned_to', $adminId)
                                  ->where('sla_due', '<', now())
                                  ->whereNull('resolved_at')->count(),
                'avg_resolution_time' => $this->calculateAverageResolutionTime($adminId, null, null),
                'completion_rate' => $this->calculateCompletionRate($adminId, null, null)
            ];
        }
        
        // With date range, filter by created_at
        $tickets = Ticket::where('assigned_to', $adminId)
                        ->whereBetween('created_at', [$startDate, $endDate]);

        return [
            'total_assigned' => $tickets->count(),
            'completed' => $tickets->whereNotNull('resolved_at')->count(),
            'overdue' => $tickets->where('sla_due', '<', now())
                              ->whereNull('resolved_at')->count(),
            'avg_resolution_time' => $this->calculateAverageResolutionTime($adminId, $startDate, $endDate),
            'completion_rate' => $this->calculateCompletionRate($adminId, $startDate, $endDate)
        ];
    }

    private function calculateAverageResolutionTime($adminId, $startDate, $endDate)
    {
        $query = Ticket::where('assigned_to', $adminId)
                      ->whereNotNull('resolved_at');
        
        // Only apply date filter if dates are provided
        if ($startDate && $endDate) {
            $query->whereBetween('created_at', [$startDate, $endDate]);
        }
        
        $resolvedTickets = $query->get();

        if ($resolvedTickets->isEmpty()) return 0;

        $totalMinutes = $resolvedTickets->sum(function ($ticket) {
            return $ticket->created_at->diffInMinutes($ticket->resolved_at);
        });

        return round($totalMinutes / $resolvedTickets->count() / 60, 2); // Return in hours
    }

    private function calculateCompletionRate($adminId, $startDate, $endDate)
    {
        $query = Ticket::where('assigned_to', $adminId);
        
        // Only apply date filter if dates are provided
        if ($startDate && $endDate) {
            $query->whereBetween('created_at', [$startDate, $endDate]);
        }
        
        $totalTickets = $query->count();

        if ($totalTickets === 0) return 0;

        $queryCompleted = Ticket::where('assigned_to', $adminId)
                               ->whereNotNull('resolved_at');
        
        // Only apply date filter if dates are provided
        if ($startDate && $endDate) {
            $queryCompleted->whereBetween('created_at', [$startDate, $endDate]);
        }
        
        $completedTickets = $queryCompleted->count();

        return round(($completedTickets / $totalTickets) * 100, 2);
    }

    /**
     * Get status ID by name
     */
    private function getStatusId($statusName)
    {
        $status = TicketsStatus::where('status', $statusName)->first();
        return $status ? $status->id : 1; // Default to 1 if not found
    }

    /**
     * Get default ticket type ID as a safe fallback.
     */
    private function getDefaultTicketTypeId()
    {
        $ticketType = TicketsType::query()->orderBy('id')->first();

        return $ticketType ? $ticketType->id : 1;
    }

    /**
     * Add ticket entry for logging activities (using TicketComment)
     */
    public function addTicketEntry(Ticket $ticket, $userId, $message, $isPublic = true)
    {
        return TicketComment::create([
            'ticket_id' => $ticket->id,
            'user_id' => $userId,
            'comment' => $message,
            'is_internal' => !$isPublic, // is_public = true means external comment
            'created_at' => now(),
            'updated_at' => now()
        ]);
    }

    /**
     * Add first response to ticket (updates status and logs activity)
     */
    public function addFirstResponse(Ticket $ticket, $userId, $response)
    {
        DB::transaction(function () use ($ticket, $userId, $response) {
            // Mark first response time if not set
            if (!$ticket->first_response_at) {
                $ticket->update([
                    'first_response_at' => now()
                ]);
            }

            // Add the response as ticket entry
            $this->addTicketEntry($ticket, $userId, $response);

            // Update status if still open
            if ($ticket->ticket_status_id == $this->getStatusId('Open')) {
                $ticket->update([
                    'ticket_status_id' => $this->getStatusId('In Progress')
                ]);
            }
        });

        return true;
    }

    /**
     * Update ticket status with automatic logging
     */
    public function updateTicketStatus(Ticket $ticket, $statusName, $userId, $notes = null)
    {
        $oldStatus = $ticket->ticket_status->status ?? 'Unknown';
        $newStatusId = $this->getStatusId($statusName);

        DB::transaction(function () use ($ticket, $newStatusId, $statusName, $oldStatus, $userId, $notes) {
            $ticket->update([
                'ticket_status_id' => $newStatusId
            ]);

            // Log status change
            $message = "Status tiket diubah dari '{$oldStatus}' menjadi '{$statusName}'";
            if ($notes) {
                $message .= "\n\nCatatan: " . $notes;
            }

            $this->addTicketEntry($ticket, $userId, $message);

            // Handle special status changes
            if ($statusName == 'Resolved' && !$ticket->resolved_at) {
                $ticket->update([
                    'resolved_at' => now()
                ]);

                // Create daily activity for resolution
                if ($ticket->assigned_to) {
                    DailyActivity::createFromTicketCompletion($ticket);
                }
            }
        });

        // Send notification to ticket creator about status change
        try {
            $ticket->user->notify(new TicketUpdated($ticket, 'status', $notes));
        } catch (\Exception $e) {
            Log::error('Failed to send ticket status update notification', [
                'ticket_id' => $ticket->id,
                'user_id' => $ticket->user_id,
                'new_status' => $statusName,
                'error' => $e->getMessage()
            ]);
        }

        return true;
    }

    /**
     * Close ticket with resolution
     */
    public function closeTicket(Ticket $ticket, $resolution, $userId)
    {
        DB::transaction(function () use ($ticket, $resolution, $userId) {
            $ticket->update([
                'ticket_status_id' => $this->getStatusId('Resolved'),
                'resolved_at' => now(),
                'resolution' => $resolution,
                'closed' => now()
            ]);

            // Log resolution
            $this->addTicketEntry($ticket, $userId, "Tiket diselesaikan.\n\nResolusi:\n" . $resolution);

            // Create daily activity
            if ($ticket->assigned_to) {
                DailyActivity::createFromTicketCompletion($ticket);
            }
        });

        return true;
    }
}