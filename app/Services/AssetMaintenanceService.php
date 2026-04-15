<?php

namespace App\Services;

use App\Asset;
use App\Ticket;
use App\DailyActivity;
use App\AssetRequest;
use Carbon\Carbon as CarbonDate;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AssetMaintenanceService
{
    /**
     * Create maintenance ticket for an asset
     */
    public function createMaintenanceTicket(Asset $asset, array $ticketData)
    {
        $ticketData['asset_id'] = $asset->id;
        $ticketData['location_id'] = $asset->location_id ?? $ticketData['location_id'];
        $ticketData['subject'] = $ticketData['subject'] ?? "Maintenance required for {$asset->asset_tag}";
        
        $ticket = Ticket::create($ticketData);
        
        // Update asset status to maintenance
        $asset->update(['status_id' => 3]); // Assuming 3 = In Maintenance
        
        // Create daily activity record
        DailyActivity::create([
            'user_id' => Auth::id(),
            'activity_date' => CarbonDate::today(),
            'description' => "Created maintenance ticket {$ticket->ticket_code} for asset {$asset->asset_tag}",
            'ticket_id' => $ticket->id,
            'type' => 'auto_from_ticket'
        ]);
        
        return $ticket;
    }
    
    /**
     * Complete maintenance for an asset
     */
    public function completeMaintenanceTicket(Ticket $ticket, array $completionData = [])
    {
        if (!$ticket->asset) {
            throw new \Exception('Ticket is not associated with an asset');
        }
        
        // Update ticket status
        $ticket->update([
            'resolved_at' => CarbonDate::now(),
            'ticket_status_id' => 3 // Resolved
        ]);
        
        // Update asset status back to active
        $ticket->asset->update([
            'status_id' => 1, // Active
            'notes' => ($ticket->asset->notes ?? '') . "\n" . 
                      "Maintenance completed on " . CarbonDate::now()->format('Y-m-d H:i') . 
                      " via ticket {$ticket->ticket_code}"
        ]);
        
        // Create completion activity
        DailyActivity::createFromTicketCompletion($ticket);
        
        return $ticket;
    }
    
    /**
     * Get asset maintenance history
     */
    public function getAssetMaintenanceHistory(Asset $asset)
    {
        return [
            'tickets' => $asset->tickets()
                             ->with(['ticket_status', 'ticket_priority', 'assignedTo', 'user'])
                             ->orderBy('created_at', 'desc')
                             ->get(),
            'activities' => DailyActivity::whereHas('ticket', function($query) use ($asset) {
                                $query->where('asset_id', $asset->id);
                            })
                            ->with(['user', 'ticket'])
                            ->orderBy('activity_date', 'desc')
                            ->get(),
            'stats' => [
                'total_tickets' => $asset->tickets()->count(),
                'resolved_tickets' => $asset->tickets()->where('ticket_status_id', 3)->count(),
                'pending_tickets' => $asset->tickets()->whereNotIn('ticket_status_id', [3, 4])->count(),
                'last_maintenance' => $asset->tickets()
                                          ->where('ticket_status_id', 3)
                                          ->latest('resolved_at')
                                          ->first()?->resolved_at,
                'maintenance_cost' => $this->calculateMaintenanceCost($asset)
            ]
        ];
    }
    
    /**
     * Generate asset replacement request
     */
    public function generateReplacementRequest(Asset $asset, string $reason)
    {
        // Check if asset qualifies for replacement
        $qualifies = $this->checkReplacementEligibility($asset);
        
        if (!$qualifies['eligible']) {
            throw new \Exception($qualifies['reason']);
        }
        
        $request = AssetRequest::create([
            'requested_by' => Auth::id(),
            'asset_category' => $asset->model->asset_type_id ?? null,
            'request_type' => 'replacement',
            'justification' => $reason,
            'current_asset_id' => $asset->id,
            'priority' => $this->determineReplacementPriority($asset),
            'estimated_cost' => $asset->model->estimated_cost ?? 0,
            'status' => 'pending'
        ]);
        
        // Create activity log
        DailyActivity::create([
            'user_id' => Auth::id(),
            'activity_date' => today(),
            'description' => "Generated replacement request for asset {$asset->asset_tag} - Reason: {$reason}",
            'type' => 'manual'
        ]);
        
        return $request;
    }
    
    /**
     * Check if asset is eligible for replacement
     */
    private function checkReplacementEligibility(Asset $asset)
    {
    $ticketCount = $asset->tickets()->count();
    $recentTickets = $asset->tickets()
                 ->where('tickets.created_at', '>=', now()->subMonths(6))
                 ->count();
        
        // Asset qualifies if:
        // 1. More than 5 tickets total, OR
        // 2. More than 3 tickets in last 6 months, OR
        // 3. Asset is older than 5 years and has tickets
        $age = $asset->purchase_date ? 
               $asset->purchase_date->diffInYears(now()) : 0;
        
        if ($ticketCount > 5) {
            return ['eligible' => true, 'reason' => 'High maintenance frequency'];
        }
        
        if ($recentTickets > 3) {
            return ['eligible' => true, 'reason' => 'Recent maintenance issues'];
        }
        
        if ($age > 5 && $ticketCount > 0) {
            return ['eligible' => true, 'reason' => 'Asset age and maintenance history'];
        }
        
        return [
            'eligible' => false, 
            'reason' => 'Asset does not meet replacement criteria'
        ];
    }
    
    /**
     * Determine replacement priority based on asset criticality
     */
    private function determineReplacementPriority(Asset $asset)
    {
    $recentTickets = $asset->tickets()
                 ->where('tickets.created_at', '>=', now()->subMonths(3))
                 ->count();
        
        if ($recentTickets >= 3) return 'high';
        if ($recentTickets >= 2) return 'medium';
        return 'low';
    }
    
    /**
     * Calculate estimated maintenance cost for an asset
     */
    private function calculateMaintenanceCost(Asset $asset)
    {
        // This would integrate with actual cost tracking
        // For now, we'll estimate based on ticket frequency
        $ticketCount = $asset->tickets()->count();
        $estimatedCostPerTicket = 150000; // Rp 150k average per ticket
        
        return $ticketCount * $estimatedCostPerTicket;
    }
    
    /**
     * Get assets requiring maintenance
     */
    public function getAssetsRequiringMaintenance()
    {
        return Asset::with(['model', 'status', 'assignedTo'])
                   ->whereHas('tickets', function($query) {
                       $query->where('tickets.created_at', '>=', now()->subMonths(1));
                   }, '>=', 2)
                   ->orWhere(function($query) {
                       // Assets with no maintenance in 6+ months and have open tickets
                       $query->whereDoesntHave('tickets', function($subQuery) {
                           $subQuery->where('tickets.created_at', '>=', now()->subMonths(6))
                                   ->where('ticket_status_id', 3);
                       })
                       ->whereHas('tickets', function($subQuery) {
                           $subQuery->whereNotIn('ticket_status_id', [3, 4]);
                       });
                   })
                   ->get();
    }
    
    /**
     * Generate maintenance schedule recommendations
     */
    public function generateMaintenanceSchedule()
    {
        $assets = Asset::with(['model', 'tickets'])
                      ->where('status_id', 1) // Active assets only
                      ->get();
        
        $schedule = [];
        
        foreach ($assets as $asset) {
            $lastMaintenance = $asset->tickets()
                                   ->where('ticket_status_id', 3)
                                   ->latest('resolved_at')
                                   ->first();
            
            $monthsSinceLastMaintenance = $lastMaintenance ? 
                                        $lastMaintenance->resolved_at->diffInMonths(now()) : 12;
            
            // Recommend maintenance based on asset type and usage
            $recommendedInterval = $this->getMaintenanceInterval($asset);
            
            if ($monthsSinceLastMaintenance >= $recommendedInterval) {
                $schedule[] = [
                    'asset' => $asset,
                    'priority' => $monthsSinceLastMaintenance > ($recommendedInterval * 1.5) ? 'high' : 'medium',
                    'last_maintenance' => $lastMaintenance?->resolved_at,
                    'recommended_date' => now()->addDays(7), // Schedule within a week
                    'reason' => "Due for {$recommendedInterval}-month maintenance cycle"
                ];
            }
        }
        
        return collect($schedule)->sortBy('priority');
    }
    
    /**
     * Get recommended maintenance interval for asset type
     *
     * Accepts an Asset model instance or a query builder that resolves to an Asset.
     *
     * @param \App\Asset|\Illuminate\Database\Eloquent\Builder $asset
     * @return int
     */
    private function getMaintenanceInterval($asset)
    {
        // If a Builder was accidentally passed, resolve it to a model
        if ($asset instanceof \Illuminate\Database\Eloquent\Builder) {
            $asset = $asset->first();
        }

        // Default intervals by asset type (in months)
        $intervals = [
            'Server' => 3,
            'Desktop' => 6,
            'Laptop' => 6,
            'Printer' => 4,
            'Network Equipment' => 3,
            'Default' => 6
        ];
        
        $assetType = $asset->model->assetType->type ?? 'Default';
        return $intervals[$assetType] ?? $intervals['Default'];
    }
}