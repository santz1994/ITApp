<?php

namespace App\Http\Controllers;

use App\Asset;
use App\Ticket;
use App\Services\AssetMaintenanceService;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon as CarbonDate;

class AssetMaintenanceController extends Controller
{
    protected $maintenanceService;

    public function __construct(AssetMaintenanceService $maintenanceService)
    {
        $this->middleware('auth');
        $this->maintenanceService = $maintenanceService;
    }

    /**
     * Show asset maintenance dashboard
     */
    public function index()
    {
        $assetsRequiringMaintenance = $this->maintenanceService->getAssetsRequiringMaintenance();
        $maintenanceSchedule = $this->maintenanceService->generateMaintenanceSchedule();
        
        $stats = [
            'assets_needing_maintenance' => $assetsRequiringMaintenance->count(),
            'scheduled_maintenance' => $maintenanceSchedule->count(),
            'high_priority' => $maintenanceSchedule->where('priority', 'high')->count(),
                'overdue_maintenance' => $assetsRequiringMaintenance->filter(function($asset) {
                return $asset->tickets()
                           ->where('tickets.created_at', '>=', CarbonDate::now()->subMonths(1))
                           ->count() >= 3;
            })->count()
        ];

        return view('asset-maintenance.index', compact(
            'assetsRequiringMaintenance', 
            'maintenanceSchedule', 
            'stats'
        ));
    }

    /**
     * Show asset maintenance history
     */
    public function show(Asset $asset)
    {
        $history = $this->maintenanceService->getAssetMaintenanceHistory($asset);
        
        return view('asset-maintenance.show', compact('asset', 'history'));
    }

    /**
     * Create maintenance ticket for asset
     */
    public function createMaintenanceTicket(Request $request, Asset $asset)
    {
        $request->validate([
            'subject' => 'required|string|max:255',
            'description' => 'required|string',
            'priority_id' => 'required|exists:tickets_priorities,id',
            'type_id' => 'required|exists:tickets_types,id'
        ]);

        $ticketData = [
            'user_id' => Auth::id(),
            'location_id' => $asset->location_id,
            'ticket_status_id' => 1, // Open
            'ticket_type_id' => $request->type_id,
            'ticket_priority_id' => $request->priority_id,
            'subject' => $request->subject,
            'description' => $request->description,
        ];

        $ticket = $this->maintenanceService->createMaintenanceTicket($asset, $ticketData);

        return redirect()
               ->route('asset-maintenance.show', $asset)
               ->with('success', "Maintenance ticket {$ticket->ticket_code} created successfully!");
    }

    /**
     * Complete maintenance ticket
     */
    public function completeMaintenanceTicket(Request $request, Ticket $ticket)
    {
        if (!$ticket->asset) {
            return redirect()->back()->with('error', 'This ticket is not associated with an asset.');
        }

        $completionData = $request->only(['completion_notes']);
        
        try {
            $this->maintenanceService->completeMaintenanceTicket($ticket, $completionData);
            
            return redirect()
                   ->route('asset-maintenance.show', $ticket->asset)
                   ->with('success', "Maintenance ticket {$ticket->ticket_code} completed successfully!");
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    /**
     * Generate replacement request for asset
     */
    public function generateReplacementRequest(Request $request, Asset $asset)
    {
        $request->validate([
            'reason' => 'required|string|max:500'
        ]);

        try {
            $replacementRequest = $this->maintenanceService->generateReplacementRequest(
                $asset, 
                $request->reason
            );
            
            return redirect()
                   ->route('asset-maintenance.show', $asset)
                   ->with('success', 'Replacement request generated successfully!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    /**
     * Get maintenance recommendations API
     */
    public function getMaintenanceRecommendations(Request $request)
    {
        $recommendations = $this->maintenanceService->generateMaintenanceSchedule();
        
        if ($request->wantsJson()) {
            return response()->json([
                'recommendations' => $recommendations->map(function($item) {
                    return [
                        'asset_id' => $item['asset']->id,
                        'asset_tag' => $item['asset']->asset_tag,
                        'model' => $item['asset']->model->name ?? 'Unknown',
                        'priority' => $item['priority'],
                        'recommended_date' => $item['recommended_date']->format('Y-m-d'),
                        'reason' => $item['reason'],
                        'last_maintenance' => $item['last_maintenance'] ? 
                                            $item['last_maintenance']->format('Y-m-d') : null
                    ];
                })->values()
            ]);
        }

        return $recommendations;
    }

    /**
     * Asset maintenance analytics
     */
    public function analytics(Request $request)
    {
        $dateFrom = $request->input('date_from', now()->subMonths(6)->format('Y-m-d'));
        $dateTo = $request->input('date_to', now()->format('Y-m-d'));

        // Get maintenance statistics
        $maintenanceTickets = Ticket::whereNotNull('asset_id')
                                   ->whereBetween('created_at', [$dateFrom, $dateTo])
                                   ->with(['asset', 'ticket_status', 'ticket_priority'])
                                   ->get();

        $analytics = [
            'total_maintenance_tickets' => $maintenanceTickets->count(),
            'completed_maintenance' => $maintenanceTickets->where('ticket_status_id', 3)->count(),
            'pending_maintenance' => $maintenanceTickets->whereNotIn('ticket_status_id', [3, 4])->count(),
            'average_resolution_time' => $this->calculateAverageResolutionTime($maintenanceTickets),
            'most_problematic_assets' => $this->getMostProblematicAssets($maintenanceTickets),
            'maintenance_by_month' => $this->getMaintenanceByMonth($maintenanceTickets),
            'cost_estimates' => $this->calculateMaintenanceCosts($maintenanceTickets)
        ];

        if ($request->wantsJson()) {
            return response()->json($analytics);
        }

        return view('asset-maintenance.analytics', compact('analytics', 'dateFrom', 'dateTo'));
    }

    /**
     * Calculate average resolution time for maintenance tickets
     */
    private function calculateAverageResolutionTime($tickets)
    {
        $resolvedTickets = $tickets->whereNotNull('resolved_at');
        
        if ($resolvedTickets->isEmpty()) {
            return 0;
        }

        $totalHours = $resolvedTickets->sum(function($ticket) {
            return $ticket->created_at->diffInHours($ticket->resolved_at);
        });

        return round($totalHours / $resolvedTickets->count(), 2);
    }

    /**
     * Get most problematic assets (highest ticket count)
     */
    private function getMostProblematicAssets($tickets)
    {
        return $tickets->groupBy('asset_id')
                      ->map(function($assetTickets) {
                          $asset = $assetTickets->first()->asset;
                          return [
                              'asset' => $asset,
                              'ticket_count' => $assetTickets->count(),
                              'resolved_count' => $assetTickets->where('ticket_status_id', 3)->count(),
                              'pending_count' => $assetTickets->whereNotIn('ticket_status_id', [3, 4])->count()
                          ];
                      })
                      ->sortByDesc('ticket_count')
                      ->take(10)
                      ->values();
    }

    /**
     * Get maintenance tickets grouped by month
     */
    private function getMaintenanceByMonth($tickets)
    {
        return $tickets->groupBy(function($ticket) {
                          return $ticket->created_at->format('Y-m');
                      })
                      ->map(function($monthTickets, $month) {
                          return [
                              'month' => $month,
                              'total' => $monthTickets->count(),
                              'completed' => $monthTickets->where('ticket_status_id', 3)->count(),
                              'pending' => $monthTickets->whereNotIn('ticket_status_id', [3, 4])->count()
                          ];
                      })
                      ->sortBy('month')
                      ->values();
    }

    /**
     * Show lemon assets (frequently problematic assets)
     */
    public function lemonAssets()
    {
        // Get all assets and filter for lemon assets
        $lemonAssets = collect();
        
        Asset::with(['tickets', 'assetModel', 'status', 'division'])
             ->chunk(100, function($assets) use (&$lemonAssets) {
                 foreach($assets as $asset) {
                     if($asset->isLemonAsset()) {
                         $lemonAssets->push($asset);
                     }
                 }
             });

        // Calculate additional statistics
        $lemonStats = [
            'total_count' => $lemonAssets->count(),
                'total_tickets' => $lemonAssets->sum(function($asset) {
                return $asset->tickets()->where('tickets.created_at', '>=', CarbonDate::now()->subMonths(6))->count();
            }),
            'average_tickets_per_asset' => $lemonAssets->count() > 0 ? 
                round($lemonAssets->sum(function($asset) {
                    return $asset->tickets()->where('tickets.created_at', '>=', CarbonDate::now()->subMonths(6))->count();
                }) / $lemonAssets->count(), 1) : 0,
            'replacement_cost_estimate' => $lemonAssets->count() * 5000000 // Rp 5M per asset estimate
        ];

        return view('asset-maintenance.lemon-assets', compact('lemonAssets', 'lemonStats'));
    }

    /**
     * Calculate estimated maintenance costs
     */
    private function calculateMaintenanceCosts($tickets)
    {
        $costPerTicket = 150000; // Rp 150k average
        
        return [
            'total_estimated_cost' => $tickets->count() * $costPerTicket,
            'completed_cost' => $tickets->where('ticket_status_id', 3)->count() * $costPerTicket,
            'pending_cost' => $tickets->whereNotIn('ticket_status_id', [3, 4])->count() * $costPerTicket,
            'average_cost_per_asset' => $tickets->groupBy('asset_id')->count() > 0 ? 
                                      ($tickets->count() * $costPerTicket) / $tickets->groupBy('asset_id')->count() : 0
        ];
    }
}