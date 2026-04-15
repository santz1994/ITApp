<?php

namespace App\Http\Controllers;

use App\AssetMaintenanceLog;
use App\Asset;
use App\Ticket;
use App\Http\Requests\StoreAssetMaintenanceLogRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class AssetMaintenanceLogController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('role:admin|super-admin');
    }

    /**
     * Display a listing of maintenance logs.
     */
    public function index(Request $request)
    {
        $query = AssetMaintenanceLog::with(['asset', 'ticket', 'performedBy'])
                    ->orderBy('scheduled_at', 'desc');

        // Filter by asset
        if ($request->has('asset_id') && $request->asset_id) {
            $query->where('asset_id', $request->asset_id);
        }

        // Filter by status
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        // Filter by maintenance type
        if ($request->has('maintenance_type') && $request->maintenance_type) {
            $query->where('maintenance_type', $request->maintenance_type);
        }

        // Filter by date range
        if ($request->has('date_from') && $request->date_from) {
            $query->whereDate('scheduled_at', '>=', $request->date_from);
        }
        if ($request->has('date_to') && $request->date_to) {
            $query->whereDate('scheduled_at', '<=', $request->date_to);
        }

        $maintenanceLogs = $query->paginate(15);
        
        // Get filter options
        $assets = Asset::select('assets.id', 'assets.asset_tag', 'asset_models.asset_model as model_name')
                      ->leftJoin('asset_models', 'assets.model_id', '=', 'asset_models.id')
                      ->orderBy('assets.asset_tag')
                      ->get();
        $statuses = ['planned', 'in_progress', 'completed', 'cancelled'];
        $maintenanceTypes = ['repair', 'preventive', 'upgrade', 'inspection', 'other'];

        return view('maintenance.index', compact('maintenanceLogs', 'assets', 'statuses', 'maintenanceTypes'));
    }

    /**
     * Show the form for creating a new maintenance log.
     */
    public function create(Request $request)
    {
        $assets = Asset::select('assets.id', 'assets.asset_tag', 'asset_models.asset_model as model_name')
                      ->leftJoin('asset_models', 'assets.model_id', '=', 'asset_models.id')
                      ->orderBy('assets.asset_tag')
                      ->get();
        $tickets = Ticket::with('asset')->where('ticket_status_id', '!=', 3)->orderBy('created_at', 'desc')->get(); // Exclude closed tickets
        
        $selectedAsset = null;
        if ($request->has('asset_id')) {
            $selectedAsset = Asset::find($request->asset_id);
        }

        return view('maintenance.create', compact('assets', 'tickets', 'selectedAsset'));
    }

    /**
     * Store a newly created maintenance log.
     */
    public function store(StoreAssetMaintenanceLogRequest $request)
    {
        $data = $request->validated();
        $data['performed_by'] = Auth::id();
        
        // Convert parts_used array to JSON if provided
        if (isset($data['parts_used']) && is_array($data['parts_used'])) {
            $data['parts_used'] = array_filter($data['parts_used']); // Remove empty values
        }

        // Auto-set timestamps based on status
        if ($data['status'] === 'in_progress' && !isset($data['started_at'])) {
            $data['started_at'] = Carbon::now();
        }
        
        if ($data['status'] === 'completed') {
            if (!isset($data['started_at'])) {
                $data['started_at'] = Carbon::now();
            }
            if (!isset($data['completed_at'])) {
                $data['completed_at'] = Carbon::now();
            }
        }

        $maintenanceLog = AssetMaintenanceLog::create($data);

        return redirect()->route('maintenance.index')
                         ->with('success', 'Log maintenance berhasil dibuat.');
    }

    /**
     * Display the specified maintenance log.
     */
    public function show($id)
    {
        $maintenanceLog = AssetMaintenanceLog::with(['asset', 'ticket', 'performedBy'])
                                           ->findOrFail($id);
        
        return view('maintenance.show', compact('maintenanceLog'));
    }

    /**
     * Show the form for editing the specified maintenance log.
     */
    public function edit($id)
    {
        $maintenanceLog = AssetMaintenanceLog::findOrFail($id);
        $assets = Asset::select('assets.id', 'assets.asset_tag', 'asset_models.asset_model as model_name')
                      ->leftJoin('asset_models', 'assets.model_id', '=', 'asset_models.id')
                      ->orderBy('assets.asset_tag')
                      ->get();
        $tickets = Ticket::with('asset')->where('ticket_status_id', '!=', 3)->orderBy('created_at', 'desc')->get();
        
        return view('maintenance.edit', compact('maintenanceLog', 'assets', 'tickets'));
    }

    /**
     * Update the specified maintenance log.
     */
    public function update(StoreAssetMaintenanceLogRequest $request, $id)
    {
        $maintenanceLog = AssetMaintenanceLog::findOrFail($id);
        $data = $request->validated();
        
        // Convert parts_used array to JSON if provided
        if (isset($data['parts_used']) && is_array($data['parts_used'])) {
            $data['parts_used'] = array_filter($data['parts_used']);
        }

        // Auto-set timestamps based on status changes
        if ($data['status'] === 'in_progress' && !$maintenanceLog->started_at) {
            $data['started_at'] = Carbon::now();
        }
        
        if ($data['status'] === 'completed' && !$maintenanceLog->completed_at) {
            $data['completed_at'] = Carbon::now();
        }

        $maintenanceLog->update($data);

        return redirect()->route('maintenance.show', $maintenanceLog->id)
                         ->with('success', 'Log maintenance berhasil diupdate.');
    }

    /**
     * Remove the specified maintenance log.
     */
    public function destroy($id)
    {
        $maintenanceLog = AssetMaintenanceLog::findOrFail($id);
        $maintenanceLog->delete();

        return redirect()->route('maintenance.index')
                         ->with('success', 'Log maintenance berhasil dihapus.');
    }

    /**
     * Get maintenance logs for specific asset (AJAX)
     */
    public function getByAsset($assetId)
    {
        $logs = AssetMaintenanceLog::with(['performedBy', 'ticket'])
                                 ->where('asset_id', $assetId)
                                 ->orderBy('scheduled_at', 'desc')
                                 ->get();
        
        return response()->json($logs);
    }
}
