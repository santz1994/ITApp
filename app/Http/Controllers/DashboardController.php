<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Ticket;
use App\Services\AssetService;

class DashboardController extends Controller
{
    protected $assetService;

    public function __construct(AssetService $assetService)
    {
        $this->assetService = $assetService;
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $stats = [];
        $stats['open_tickets'] = Ticket::where('ticket_status_id', '!=', 3)->count();
        $stats['overdue_tickets'] = Ticket::where('sla_due', '<', now())->count();
        $assetStats = $this->assetService->getAssetStatistics();
        $stats['total_assets'] = $assetStats['total'] ?? 0;
        $maintenanceDue = $this->assetService->getAssetsNeedingMaintenance();
        
        // Eager load relations to prevent N+1 query problem
        $recentTickets = Ticket::with(['user', 'ticketStatus', 'ticketPriority', 'location'])
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get();

        return view('dashboard.integrated-dashboard', compact('stats', 'recentTickets', 'assetStats', 'maintenanceDue'));
    }
}
