<?php

namespace App\Http\Controllers;

use App\Movement;
use App\Asset;
use App\Location;
use App\Division;
use Illuminate\Support\Facades\Auth;
use App\Traits\RoleBasedAccessTrait;

class HomeController extends Controller
{
    use RoleBasedAccessTrait;
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     */
    public function index()
    {
      $pageTitle = 'Dashboard';

  // Get Authenticated User
  /** @var \App\User $user */
  $user = Auth::user();

      // If the user is in the management role, show KPI Dashboard as their home
      if ($this->hasRole('management')) {
          return redirect()->route('kpi.dashboard');
      }

      if ($this->hasRole('user')) {
        return redirect()->route('tickets.index');
      } else {
        // Get summary statistics instead of all records for better performance
        $assetStats = [
          'total_assets' => Asset::count(),
          'active_assets' => Asset::inUse()->count(),
          'available_assets' => Asset::inStock()->count(),
          'maintenance_assets' => Asset::inRepair()->count(),
        ];
        
        $locationCount = Location::count();
        $divisionCount = Division::count();
        $year = \Carbon\Carbon::now()->year;
        
        // Load only recent movements with relationships
        $movements = Movement::with(['asset', 'location', 'user'])
                            ->orderBy('created_at', 'desc')
                            ->take(5)
                            ->get();
        
        // Get recent assets with their relationships
        $recentAssets = Asset::withRelations()
                           ->orderBy('created_at', 'desc')
                           ->take(10)
                           ->get();
        
        // Chart Data: Asset Distribution by Type (via AssetModel -> AssetType)
        $assetsByCategory = \DB::table('assets')
            ->join('asset_models', 'assets.model_id', '=', 'asset_models.id')
            ->join('asset_types', 'asset_models.asset_type_id', '=', 'asset_types.id')
            ->select('asset_types.type_name', \DB::raw('count(*) as total'))
            ->groupBy('asset_types.type_name')
            ->pluck('total', 'type_name');
        
        // Chart Data: Ticket Status Overview
        $ticketsByStatus = \App\Ticket::select('ticket_status_id', \DB::raw('count(*) as total'))
            ->groupBy('ticket_status_id')
            ->with('ticket_status')
            ->get()
            ->mapWithKeys(function($item) {
                return [optional($item->ticket_status)->status ?? 'Unknown' => $item->total];
            });
        
        // Chart Data: Monthly Ticket Trend (Last 6 Months)
        // OPTIMIZED: Single query instead of 12 separate queries (6 months × 2 status)
        $closedStatusIds = \App\TicketsStatus::whereIn('status', ['Closed', 'Resolved'])->pluck('id')->toArray();
        $sixMonthsAgo = \Carbon\Carbon::now()->subMonths(5)->startOfMonth();
        
        // Use database-agnostic date functions (compatible with MySQL and SQLite)
        $yearExpression = \DB::getDriverName() === 'sqlite' 
            ? "strftime('%Y', created_at)" 
            : 'YEAR(created_at)';
        $monthExpression = \DB::getDriverName() === 'sqlite' 
            ? "strftime('%m', created_at)" 
            : 'MONTH(created_at)';
        
        $placeholders = implode(',', array_fill(0, count($closedStatusIds), '?'));
        $ticketTrends = \App\Ticket::where('created_at', '>=', $sixMonthsAgo)
            ->select(
                \DB::raw("{$yearExpression} as year"),
                \DB::raw("{$monthExpression} as month"),
                \DB::raw('COUNT(*) as total'),
                \DB::raw("SUM(CASE WHEN ticket_status_id IN ({$placeholders}) THEN 1 ELSE 0 END) as resolved")
            )
            ->setBindings($closedStatusIds, 'select')
            ->groupBy(\DB::raw($yearExpression), \DB::raw($monthExpression))
            ->orderBy(\DB::raw($yearExpression))
            ->orderBy(\DB::raw($monthExpression))
            ->get()
            ->keyBy(function($item) {
                return $item->year . '-' . str_pad($item->month, 2, '0', STR_PAD_LEFT);
            });
        
        $monthlyTickets = [];
        $monthlyTicketsResolved = [];
        for ($i = 5; $i >= 0; $i--) {
            $month = \Carbon\Carbon::now()->subMonths($i);
            $key = $month->format('Y-m');
            $monthlyTickets[] = $ticketTrends->get($key)->total ?? 0;
            $monthlyTicketsResolved[] = $ticketTrends->get($key)->resolved ?? 0;
        }
        
        // Chart Data: Asset Lifecycle Status
        $assetsByStatus = Asset::select('status_id', \DB::raw('count(*) as total'))
            ->groupBy('status_id')
            ->with('status')
            ->get()
            ->mapWithKeys(function($item) {
                return [optional($item->status)->name ?? 'Unknown' => $item->total];
            });
                            
        return view('home', compact(
            'assetStats', 'movements', 'recentAssets', 'locationCount', 'divisionCount', 'year', 'pageTitle',
            'assetsByCategory', 'ticketsByStatus', 'monthlyTickets', 'monthlyTicketsResolved', 'assetsByStatus'
        ));
      }
    }
}
