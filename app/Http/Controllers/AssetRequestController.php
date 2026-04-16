<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\CreateAssetRequestRequest;
use App\AssetRequest;
use App\AssetType;
use App\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;
use App\Services\PurchaseRequestApprovalWorkflowService;

class AssetRequestController extends Controller
{
    protected PurchaseRequestApprovalWorkflowService $approvalWorkflowService;

    public function __construct(PurchaseRequestApprovalWorkflowService $approvalWorkflowService)
    {
        $this->approvalWorkflowService = $approvalWorkflowService;
    }

    /**
     * Get the authenticated user
     * 
     * @return \App\User
     */
    protected function user(): User
    {
        /** @var User $user */
        $user = Auth::user();
        return $user;
    }
    /**
     * Display a listing of asset requests
     */
    public function index(Request $request)
    {
        // Defensive: ensure user object is available and log access for debugging
        $user = null;
        try {
            $user = $this->user();
        } catch (\Throwable $e) {
            // If Auth::user() can't be resolved, log and continue as guest (shouldn't happen under 'auth' middleware)
            Log::warning('AssetRequestController@index: failed to get auth user', ['error' => $e->getMessage()]);
        }

        Log::info('AssetRequestController@index accessed', [
            'user_id' => $user ? $user->id : null,
            'roles' => $user && method_exists($user, 'getRoleNames') ? $user->getRoleNames()->toArray() : [],
            'query_params' => $request->query()
        ]);

        $query = AssetRequest::with(['requestedBy', 'assetType', 'approvedBy']);

        // Filter by status (only when filled)
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by asset type (only when filled)
        if ($request->filled('asset_type')) {
            $query->where('asset_type_id', $request->asset_type);
        }

        // Priority filter for export (only when filled AND DB column exists)
        if ($request->filled('priority')) {
            try {
                if (Schema::hasColumn('asset_requests', 'priority')) {
                    $query->where('priority', $request->priority);
                } else {
                    Log::warning('Attempted to export filtered by priority but column is missing', ['user_id' => Auth::id()]);
                }
            } catch (\Exception $e) {
                Log::warning('Schema check failed for export asset_requests.priority: ' . $e->getMessage());
            }
        }

        // Filter by priority (only when filled AND column exists in DB)
        if ($request->filled('priority')) {
            try {
                if (Schema::hasColumn('asset_requests', 'priority')) {
                    $query->where('priority', $request->priority);
                } else {
                    Log::warning('Attempted to filter by priority but column is missing', ['user_id' => $user ? $user->id : null]);
                }
            } catch (\Exception $e) {
                // In some environments Schema facade may not be available; log and skip the filter
                Log::warning('Schema check failed for asset_requests.priority: ' . $e->getMessage());
            }
        }

        // If regular user, only show their requests. Be defensive if user object not available.
        if (! $user || ! (method_exists($user, 'hasRole') && $user->hasRole(['admin', 'super-admin']))) {
            $query->where('requested_by', Auth::id());
        }

        $requests = $query->orderBy('created_at', 'desc')->paginate(20);

        // Get filter options
        $assetTypes = \App\Services\CacheService::getAssetTypes();
        $statuses = ['pending', 'approved', 'rejected', 'fulfilled'];
        $priorities = ['low', 'medium', 'high', 'urgent'];

        return view('asset-requests.index', compact('requests', 'assetTypes', 'statuses', 'priorities'));
    }

    /**
     * Show the form for creating a new asset request
     */
    public function create()
    {
        $assetTypes = \App\Services\CacheService::getAssetTypes();
        $priorities = ['low', 'medium', 'high', 'urgent'];

        return view('asset-requests.create', compact('assetTypes', 'priorities'));
    }

    /**
     * Store a newly created asset request
     */
    public function store(CreateAssetRequestRequest $request)
    {
        try {
            $data = $request->validated();

            // Prepare data to save: only include columns that actually exist in DB to avoid silent mismatches
            $saveData = [];
            $candidateFields = ['asset_type_id', 'justification', 'priority', 'title', 'requested_quantity', 'unit', 'division'];
            foreach ($candidateFields as $field) {
                if (array_key_exists($field, $data)) {
                    try {
                        if (Schema::hasColumn('asset_requests', $field)) {
                            $saveData[$field] = $data[$field];
                        } else {
                            // column not present; skip but continue
                            Log::debug("AssetRequest store: skipping field because column missing: {$field}");
                        }
                    } catch (\Exception $e) {
                        // If Schema isn't available, fall back to only fill known fillable attributes
                        Log::warning('Schema check failed in AssetRequestController@store: ' . $e->getMessage());
                    }
                }
            }

            $saveData['requested_by'] = Auth::id();
            $saveData['status'] = 'pending';

            // Fallback: if none of the candidate fields exists in DB, still create with minimal data
            if (empty($saveData['asset_type_id']) && isset($data['asset_type_id'])) {
                $saveData['asset_type_id'] = $data['asset_type_id'];
            }

            AssetRequest::create($saveData);
            
            return redirect()->route('asset-requests.index')
                           ->with('success', 'Permintaan asset berhasil diajukan');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Gagal mengajukan permintaan: ' . $e->getMessage()])
                         ->withInput();
        }
    }

    /**
     * Display the specified asset request
     */
    public function show(AssetRequest $assetRequest)
    {
        // Check if user can view this request
        if (!$this->user()->hasRole(['admin', 'super-admin']) && $assetRequest->requested_by !== Auth::id()) {
            abort(403, 'Tidak memiliki akses untuk melihat permintaan ini');
        }

        $assetRequest->load(['requestedBy', 'assetType', 'approvedBy']);
        
        return view('asset-requests.show', compact('assetRequest'));
    }

    /**
     * Show edit form for asset request
     */
    public function edit(AssetRequest $assetRequest)
    {
        // Check if user can edit this request
        if (!$this->user()->hasRole(['admin', 'super-admin']) && $assetRequest->requested_by !== Auth::id()) {
            abort(403, 'Tidak memiliki akses untuk mengubah permintaan ini');
        }

        // Can only edit pending requests
        if ($assetRequest->status !== 'pending') {
            return back()->with('error', 'Hanya dapat mengubah permintaan yang masih pending');
        }

        $assetRequest->load(['requestedBy', 'assetType']);
        $assetTypes = AssetType::orderBy('type_name')->get();
        $statuses = ['pending', 'approved', 'rejected', 'fulfilled'];

        return view('asset-requests.edit', compact('assetRequest', 'assetTypes', 'statuses'));
    }

    /**
     * Update asset request
     */
    public function update(Request $request, AssetRequest $assetRequest)
    {
        // Check if user can update this request
        if (!$this->user()->hasRole(['admin', 'super-admin']) && $assetRequest->requested_by !== Auth::id()) {
            abort(403, 'Tidak memiliki akses untuk mengubah permintaan ini');
        }

        // Can only edit pending requests
        if ($assetRequest->status !== 'pending') {
            return back()->with('error', 'Hanya dapat mengubah permintaan yang masih pending');
        }

        $validated = $request->validate([
            'asset_type_id' => 'required|exists:asset_types,id',
            'justification' => 'required|string|max:1000',
            'title' => 'nullable|string|max:255',
            'requested_quantity' => 'nullable|integer|min:1',
            'unit' => 'nullable|string|max:50',
            'priority' => 'nullable|in:low,medium,high,urgent'
        ]);

        try {
            $updateData = [
                'asset_type_id' => $request->asset_type_id,
                'justification' => $request->justification,
            ];

            $optionalFields = ['title', 'requested_quantity', 'unit', 'priority'];
            foreach ($optionalFields as $f) {
                if ($request->has($f)) {
                    try {
                        if (Schema::hasColumn('asset_requests', $f)) {
                            $updateData[$f] = $request->input($f);
                        } else {
                            Log::debug("AssetRequest update: skipping field because column missing: {$f}");
                        }
                    } catch (\Exception $e) {
                        Log::warning('Schema check failed in AssetRequestController@update: ' . $e->getMessage());
                    }
                }
            }

            $assetRequest->update($updateData);

            return redirect()->route('asset-requests.show', $assetRequest->id)
                           ->with('success', 'Permintaan asset berhasil diperbarui');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal memperbarui permintaan: ' . $e->getMessage());
        }
    }

    /**
     * Approve asset request
     */
    public function approve(Request $request, AssetRequest $assetRequest)
    {
        // Only admin can approve
        if (!$this->user()->hasRole(['admin', 'super-admin'])) {
            abort(403, 'Tidak memiliki akses untuk menyetujui permintaan');
        }

        $request->validate([
            'admin_notes' => 'nullable|string|max:1000'
        ]);

        try {
            $this->approvalWorkflowService->approve(
                $assetRequest,
                (int) Auth::id(),
                $request->input('admin_notes')
            );
            
            return redirect()->route('asset-requests.show', $assetRequest->id)
                           ->with('success', 'Permintaan asset berhasil disetujui');
        } catch (\Throwable $e) {
            return back()->with('error', 'Gagal menyetujui permintaan: ' . $e->getMessage());
        }
    }

    /**
     * Reject asset request
     */
    public function reject(Request $request, AssetRequest $assetRequest)
    {
        // Only admin can reject
        if (!$this->user()->hasRole(['admin', 'super-admin'])) {
            abort(403, 'Tidak memiliki akses untuk menolak permintaan');
        }

        $request->validate([
            'admin_notes' => 'required|string|max:1000'
        ]);

        try {
            $this->approvalWorkflowService->reject(
                $assetRequest,
                (int) Auth::id(),
                $request->input('admin_notes')
            );
            
            return redirect()->route('asset-requests.show', $assetRequest->id)
                           ->with('success', 'Permintaan asset berhasil ditolak');
        } catch (\Throwable $e) {
            return back()->with('error', 'Gagal menolak permintaan: ' . $e->getMessage());
        }
    }

    /**
     * Mark request as fulfilled
     */
    public function fulfill(Request $request, AssetRequest $assetRequest)
    {
        // Only admin can fulfill
        if (!$this->user()->hasRole(['admin', 'super-admin'])) {
            abort(403, 'Tidak memiliki akses untuk memenuhi permintaan');
        }

        $request->validate([
            'fulfillment_notes' => 'nullable|string|max:1000',
            'fulfilled_asset_id' => 'nullable|exists:assets,id',
        ]);

        try {
            $fulfilledAssetId = $request->filled('fulfilled_asset_id')
                ? (int) $request->input('fulfilled_asset_id')
                : null;

            $this->approvalWorkflowService->fulfill(
                $assetRequest,
                (int) Auth::id(),
                $request->input('fulfillment_notes'),
                $fulfilledAssetId
            );
            
            return redirect()->route('asset-requests.show', $assetRequest->id)
                           ->with('success', 'Permintaan asset berhasil dipenuhi');
        } catch (\Throwable $e) {
            return back()->with('error', 'Gagal memenuhi permintaan: ' . $e->getMessage());
        }
    }

    /**
     * Show pending requests (for admin)
     */
    public function pending()
    {
        // Only admin can view
        if (!$this->user()->hasRole(['admin', 'super-admin'])) {
            abort(403, 'Tidak memiliki akses');
        }

        $requests = AssetRequest::with(['requestedBy', 'assetType'])
                              ->where('status', 'pending')
                              ->orderBy('created_at', 'desc')
                              ->get();

        return view('asset-requests.pending', compact('requests'));
    }

    /**
     * Show my requests (for regular users)
     */
    public function myRequests()
    {
        $requests = AssetRequest::with(['assetType', 'approvedBy'])
                              ->where('requested_by', Auth::id())
                              ->orderBy('created_at', 'desc')
                              ->get();

        return view('asset-requests.my-requests', compact('requests'));
    }

    /**
     * Cancel asset request (user can cancel their own pending requests)
     */
    public function cancel(AssetRequest $assetRequest)
    {
        // Check if user can cancel this request
        if ($assetRequest->requested_by !== Auth::id()) {
            abort(403, 'Tidak memiliki akses untuk membatalkan permintaan ini');
        }

        // Can only cancel pending requests
        if ($assetRequest->status !== 'pending') {
            return back()->with('error', 'Hanya dapat membatalkan permintaan yang masih pending');
        }

        try {
            $assetRequest->delete();
            
            return redirect()->route('asset-requests.index')
                           ->with('success', 'Permintaan asset berhasil dibatalkan');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal membatalkan permintaan: ' . $e->getMessage());
        }
    }

    /**
     * Export requests to CSV
     */
    public function export(Request $request)
    {
        $query = AssetRequest::with(['requestedBy', 'assetType', 'approvedBy']);

        // Apply filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('asset_type')) {
            $query->where('asset_type_id', $request->asset_type);
        }

        // If regular user, only export their requests
        if (!$this->user()->hasRole(['admin', 'super-admin'])) {
            $query->where('requested_by', Auth::id());
        }

        $requests = $query->orderBy('created_at', 'desc')->get();

        // Generate CSV
        $filename = 'asset-requests-' . date('Y-m-d') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($requests) {
            $file = fopen('php://output', 'w');
            
            // CSV headers
            fputcsv($file, ['Tanggal Request', 'User', 'Tipe Asset', 'Deskripsi', 'Prioritas', 'Status', 'Disetujui Oleh', 'Tanggal Disetujui']);
            
            foreach ($requests as $req) {
                // Use justification (stored field) and guard date formatting in case values are strings
                $created = $req->created_at ? Carbon::parse($req->created_at)->format('Y-m-d H:i:s') : '';
                $approved = $req->approved_at ? Carbon::parse($req->approved_at)->format('Y-m-d H:i:s') : '';

                fputcsv($file, [
                    $created,
                    optional($req->requestedBy)->name ?: '',
                    optional($req->assetType)->name ?: '',
                    $req->justification ?? '',
                    $req->priority ?? '',
                    $req->status ?? '',
                    $req->approvedBy ? $req->approvedBy->name : '',
                    $approved
                ]);
            }
            
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}