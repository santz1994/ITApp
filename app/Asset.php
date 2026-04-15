<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use App\Traits\Auditable;
use App\Traits\SortableQuery;
use App\Traits\SearchServiceTrait;
use App\Traits\FilterBuilder;
use App\Traits\BulkOperationBuilder;
use App\Traits\ExportBuilder;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Asset extends Model implements HasMedia
{
  use InteractsWithMedia, Auditable, SortableQuery, SearchServiceTrait, FilterBuilder, BulkOperationBuilder, ExportBuilder, HasFactory, SoftDeletes;
  
  /**
   * Mass assignable attributes
   * @var array
   */
  protected $fillable = [
    'asset_tag', 'name', 'serial_number', 'model_id', 'division_id', 'location_id', 'supplier_id', 
    'purchase_date', 'warranty_months', 'warranty_type_id', 'invoice_id', 
    'ip_address', 'mac_address', 'qr_code', 'status_id', 'assigned_to', 'notes', 'purchase_order_id'
  ];

  /**
   * FULLTEXT searchable columns
   * @var array
   */
  protected $searchColumns = ['name', 'description', 'asset_tag', 'serial_number'];

  protected $dates = ['purchase_date', 'deleted_at'];
  
  protected $casts = [
    'purchase_date' => 'date',
    'warranty_months' => 'integer',
  ];

  /**
   * Define sortable columns for API queries
   * @return array
   */
  public function getSortableColumns()
  {
    return [
      'id' => 'id',
      'asset_tag' => 'asset_tag',
      'name' => 'name',
      'serial_number' => 'serial_number',
      'created_at' => 'created_at',
      'updated_at' => 'updated_at',
      'status_id' => 'status_id',
      'division_id' => 'division_id',
      'purchase_date' => 'purchase_date',
      'warranty_expiry' => 'warranty_expiry_date',
      'assigned_to' => 'assigned_to',
    ];
  }

  /**
   * Define relationship-based sorting (requires joins)
   * @return array
   */
  public function getSortableRelations()
  {
    return [
      'status' => ['statuses', 'id', 'name'],
      'division' => ['divisions', 'id', 'name'],
      'location' => ['locations', 'id', 'name'],
      'manufacturer' => ['manufacturers', 'id', 'name'],
    ];
  }

  protected static function boot()
  {
    parent::boot();
    
    static::creating(function ($asset) {
      if (!$asset->qr_code) {
        $asset->qr_code = self::generateQRCode();
      }
    });
  }

  public static function generateQRCode()
  {
    return 'AST-' . strtoupper(uniqid());
  }

  /**
   * Register media collections
   */
  public function registerMediaCollections(): void
  {
    $this->addMediaCollection('images')
         ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/gif', 'image/webp']);
    
    $this->addMediaCollection('documents')
         ->acceptsMimeTypes(['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document']);
    
    $this->addMediaCollection('invoices')
         ->acceptsMimeTypes(['application/pdf', 'image/jpeg', 'image/png']);
  }

  /**
   * Generate QR Code URL for mobile access
   */
  public function getQRCodeUrlAttribute()
  {
    return route('assets.qr', $this->qr_code);
  }

  /**
   * Generate QR Code image
   */
  public function generateQRCodeImage()
  {
    return QrCode::size(200)->generate($this->qr_code_url);
  }

  /**
   * Get the asset name from the related model
   */
  public function getNameAttribute()
  {
    return $this->model ? $this->model->asset_model : 'Unknown Model';
  }

  // Relationships
  public function model()
  {
    return $this->belongsTo(AssetModel::class, 'model_id');
  }

  /**
   * Get asset type through model relationship
   * Asset -> AssetModel -> AssetType
   */
  public function assetType()
  {
    return $this->hasOneThrough(
      AssetType::class,       // Final model
      AssetModel::class,      // Intermediate model
      'id',                   // Foreign key on AssetModel table (matches model_id on assets)
      'id',                   // Foreign key on AssetType table
      'model_id',             // Local key on Asset table
      'asset_type_id'         // Local key on AssetModel table
    );
  }

  public function division()
  {
    return $this->belongsTo(Division::class);
  }

  /**
   * Location relation (assets have a location_id)
   */
  public function location()
  {
    return $this->belongsTo(Location::class, 'location_id');
  }

  public function supplier()
  {
    return $this->belongsTo(Supplier::class);
  }

  public function movement()
  {
    return $this->belongsTo(Movement::class);
  }

  /**
   * Movements history for this asset (hasMany)
   */
  public function movements()
  {
    return $this->hasMany(Movement::class, 'asset_id');
  }

  public function warranty_type()
  {
    return $this->belongsTo(WarrantyType::class);
  }

  public function invoice()
  {
    return $this->belongsTo(Invoice::class);
  }

  public function status()
  {
    return $this->belongsTo(Status::class);
  }

  public function assignedTo()
  {
    return $this->belongsTo(User::class, 'assigned_to');
  }

  /**
   * Legacy alias for assignedTo, used in some views/tests
   */
  public function assignedUser()
  {
    // Backwards compatibility alias for older code expecting 'assignedUser'
    // Return the relation directly rather than delegating to assignedTo()
    // to ensure eager loading works reliably with both names.
    return $this->belongsTo(User::class, 'assigned_to');
  }

  // Backward compatibility alias for older code expecting 'assignedUser'

  /**
   * Purchase order relationship
   */
  public function purchaseOrder()
  {
    return $this->belongsTo(PurchaseOrder::class, 'purchase_order_id');
  }

  /**
   * Many-to-many relation to tickets via 'ticket_assets' pivot table.
   * Replaces the legacy hasMany relationship.
   */
  public function tickets()
  {
    return $this->belongsToMany(Ticket::class, 'ticket_assets', 'asset_id', 'ticket_id')->withTimestamps();
  }

  public function assetRequests()
  {
    return $this->hasMany(AssetRequest::class, 'fulfilled_asset_id');
  }

  /**
   * Relasi ke Asset Maintenance Logs
   */
  public function maintenanceLogs()
  {
    return $this->hasMany(AssetMaintenanceLog::class)->orderBy('scheduled_at', 'desc');
  }

  // Scopes
  public function scopeInUse($query)
  {
    return $query->where('status_id', 15); // Active
  }

  public function scopeActive($query)
  {
    return $query->whereIn('status_id', [1, 15]); // Ready to Deploy OR In Use
  }

  public function scopeInactive($query)
  {
    return $query->whereNotIn('status_id', [1, 15]); // Not Ready or In Use
  }

  public function scopeInStock($query)
  {
    return $query->where('status_id', 1); // Ready to Deploy
  }

  public function scopeInRepair($query)
  {
    return $query->whereIn('status_id', [3, 4]); // Out for Repairs, Waiting for Repairs
  }

  public function scopeDisposed($query)
  {
    return $query->whereIn('status_id', [5, 6, 8, 9]); // Written Off - Broken, Written Off - Age, Pending Disposal, Retired
  }

  public function scopeForDivision($query, $divisionId)
  {
    return $query->where('division_id', $divisionId);
  }

  public function scopeAssigned($query)
  {
    return $query->whereNotNull('assigned_to');
  }

  public function scopeUnassigned($query)
  {
    return $query->whereNull('assigned_to');
  }

  public function scopeAssignedTo($query, $userId)
  {
    return $query->where('assigned_to', $userId);
  }

  public function scopeByAssetTag($query, $assetTag)
  {
    return $query->where('asset_tag', 'LIKE', "%{$assetTag}%");
  }

  public function scopeBySerial($query, $serial)
  {
    return $query->where('serial_number', 'LIKE', "%{$serial}%");
  }

  public function scopeByStatus($query, $statusId)
  {
    return $query->where('status_id', $statusId);
  }

  public function scopeWarrantyExpired($query)
  {
    return $query->whereRaw('DATE_ADD(purchase_date, INTERVAL warranty_months MONTH) < NOW()');
  }

  public function scopeWarrantyExpiring($query, $days = 30)
  {
    return $query->whereRaw('DATE_ADD(purchase_date, INTERVAL warranty_months MONTH) BETWEEN NOW() AND DATE_ADD(NOW(), INTERVAL ? DAY)', [$days]);
  }

  public function scopeWithRelations($query)
  {
    return $query->with(['model', 'division', 'status', 'assignedTo', 'supplier', 'purchaseOrder']);
  }

  /**
   * Enhanced eager loading with nested relationships (Level 2)
   * Includes manufacturer for asset model
   */
  public function scopeWithNestedRelations($query)
  {
    return $query->with([
      'model.manufacturer', // Nested: AssetModel->Manufacturer
      'division',
      'location',
      'status',
      'assignedTo',
      'supplier',
      'purchaseOrder'
    ]);
  }

  /**
   * Eager load tickets via many-to-many relationship
   */
  public function scopeWithTickets($query)
  {
    return $query->with(['tickets', 'tickets.ticket_status', 'tickets.ticket_priority', 'tickets.assignedTo']);
  }

  /**
   * Eager load all related data for detail views
   * Maximum safe level of nesting
   */
  public function scopeWithAllData($query)
  {
    return $query->with([
      'model.manufacturer',
      'division',
      'location',
      'status',
      'warranty_type',
      'assignedTo',
      'supplier',
      'purchaseOrder',
      'invoice',
      'maintenanceLogs',
      'movements',
      'tickets'
    ]);
  }

  // ========================
  // ACCESSORS & MUTATORS
  // ========================
  
  /**
   * Format asset tag for display (uppercase)
   */
  protected function assetTag(): Attribute
  {
    return Attribute::make(
      get: fn ($value) => strtoupper($value),
      set: fn ($value) => strtoupper(trim($value))
    );
  }

  /**
   * Format serial number for display (uppercase)
   */
  protected function serialNumber(): Attribute
  {
    return Attribute::make(
      get: fn ($value) => strtoupper($value),
      set: fn ($value) => strtoupper(trim($value))
    );
  }

  /**
   * Get warranty expiry date
   */
  protected function warrantyExpiryDate(): Attribute
  {
    return Attribute::make(
      get: function () {
        if (!$this->purchase_date || !$this->warranty_months) {
          return null;
        }
        return $this->purchase_date->addMonths($this->warranty_months);
      }
    );
  }

  /**
   * Check if warranty is active
   */
  protected function isWarrantyActive(): Attribute
  {
    return Attribute::make(
      get: function () {
        $expiryDate = $this->warranty_expiry_date;
        return $expiryDate && now()->lte($expiryDate);
      }
    );
  }

  /**
   * Check if warranty is expiring soon (within 30 days)
   */
  protected function isWarrantyExpiringSoon(): Attribute
  {
    return Attribute::make(
      get: function () {
        $expiryDate = $this->warranty_expiry_date;
        if (!$expiryDate) return false;
        
        return now()->diffInDays($expiryDate, false) <= 30 && now()->lte($expiryDate);
      }
    );
  }

  /**
   * Get asset age in years
   */
  protected function ageInYears(): Attribute
  {
    return Attribute::make(
      get: function () {
        if (!$this->purchase_date) return null;
        return round($this->purchase_date->diffInYears(now()), 1);
      }
    );
  }

  /**
   * Get depreciation percentage (assuming 5-year depreciation)
   */
  protected function depreciationPercentage(): Attribute
  {
    return Attribute::make(
      get: function () {
        $age = $this->age_in_years;
        if (!$age) return 0;
        
        return min(100, round(($age / 5) * 100, 1));
      }
    );
  }

  /**
   * Get status badge HTML
   */
  protected function statusBadge(): Attribute
  {
    return Attribute::make(
      get: function () {
        $status = $this->status->name ?? 'Unknown';
        $badges = [
          'In Use' => '<span class="badge badge-success">In Use</span>',
          'In Stock' => '<span class="badge badge-info">In Stock</span>',
          'In Repair' => '<span class="badge badge-warning">In Repair</span>',
          'Disposed' => '<span class="badge badge-danger">Disposed</span>',
          'Lost' => '<span class="badge badge-dark">Lost</span>',
        ];
        return $badges[$status] ?? '<span class="badge badge-light">' . $status . '</span>';
      }
    );
  }

  /**
   * Get formatted purchase date
   */
  protected function formattedPurchaseDate(): Attribute
  {
    return Attribute::make(
      get: fn () => $this->purchase_date ? $this->purchase_date->format('d M Y') : null
    );
  }

  /**
   * Get MAC address formatted with colons
   */
  protected function formattedMacAddress(): Attribute
  {
    return Attribute::make(
      get: function ($value) {
        if (!$this->mac_address || strlen($this->mac_address) !== 12) {
          return $this->mac_address;
        }
        return implode(':', str_split($this->mac_address, 2));
      },
      set: fn ($value) => str_replace([':', '-', ' '], '', strtoupper(trim($value)))
    );
  }

  // Legacy Accessors (for backward compatibility)
  public function getTicketHistoryAttribute()
  {
    return $this->tickets()
                ->with(['user', 'ticket_status', 'ticket_priority'])
                ->orderBy('created_at', 'desc')
                ->get();
  }

  public function getIsLemonAssetAttribute()
  {
    // Asset yang sering rusak (>3 tiket dalam 6 bulan terakhir)
  $recentTickets = $this->tickets()
              ->where('tickets.created_at', '>=', now()->subMonths(6))
              ->count();
    
    return $recentTickets > 3;
  }

  // ========================
  // HELPER METHODS
  // ========================
  
  /**
   * Check if asset can be assigned
   */
  public function canBeAssigned(): bool
  {
    return $this->status && in_array($this->status->name, ['In Stock']);
  }

  /**
   * Check if asset is currently assigned
   */
  public function isAssigned(): bool
  {
    return !is_null($this->assigned_to);
  }

  /**
   * Check if asset needs maintenance (has unresolved tickets)
   */
  public function needsMaintenance(): bool
  {
    return $this->tickets()
                ->whereNull('resolved_at')
                ->exists();
  }

  /**
   * Assign asset to user
   */
  public function assignTo(User $user): bool
  {
    if (!$this->canBeAssigned()) {
      return false;
    }

    // Update asset
    $this->update([
      'assigned_to' => $user->id,
      'status_id' => Status::where('name', 'In Use')->first()?->id ?? 1,
    ]);

    // Log activity
    DailyActivity::create([
      'user_id' => $user->id,
      'activity_date' => today(),
      'description' => "Asset assigned: {$this->asset_tag} - " . ($this->model->name ?? 'Unknown Model'),
      'type' => 'asset_assignment',
      'notes' => "Automated assignment log",
    ]);

    // Send notification for assignment
    // TODO: Create AssetAssigned notification class if needed
    try {
      // For now, just log the assignment - notification can be added later
      Log::info('Asset assigned', [
        'asset_id' => $this->id,
        'asset_tag' => $this->asset_tag,
        'user_id' => $user->id,
        'user_name' => $user->name
      ]);
    } catch (\Exception $e) {
      Log::error('Failed to log asset assignment', [
        'asset_id' => $this->id,
        'user_id' => $user->id,
        'error' => $e->getMessage()
      ]);
    }

    return true;
  }

  /**
   * Unassign asset from user
   */
  public function unassign(): bool
  {
    if (!$this->isAssigned()) {
      return false;
    }

    $previousUser = $this->assignedTo;
    
    // Update asset
    $this->update([
      'assigned_to' => null,
      'status_id' => Status::where('name', 'In Stock')->first()?->id ?? 2,
    ]);

    // Log activity
    if ($previousUser) {
      DailyActivity::create([
        'user_id' => $previousUser->id,
        'activity_date' => today(),
        'description' => "Asset unassigned: {$this->asset_tag} - " . ($this->model->name ?? 'Unknown Model'),
        'type' => 'asset_unassignment',
        'notes' => "Automated unassignment log",
      ]);
    }

    return true;
  }

  /**
   * Mark asset for maintenance
   */
  public function markForMaintenance(?string $reason = null): bool
  {
    $this->update([
      'status_id' => Status::where('name', 'In Repair')->first()?->id ?? 3,
    ]);

    // Create maintenance ticket if reason provided
    if ($reason) {
  $ticketType = TicketsType::where('name', 'Maintenance')->first();
  $ticketPriority = TicketsPriority::where('name', 'Normal')->first();
  // tickets_statuses uses `status` column
  $ticketStatus = TicketsStatus::where('status', 'Open')->first();

      if ($ticketType && $ticketPriority && $ticketStatus) {
        Ticket::create([
          'user_id' => Auth::id(),
          'asset_id' => $this->id,
          'ticket_type_id' => $ticketType->id,
          'ticket_priority_id' => $ticketPriority->id,
          'ticket_status_id' => $ticketStatus->id,
          'subject' => "Maintenance Required: {$this->asset_tag}",
          'description' => $reason,
        ]);
      }
    }

    return true;
  }

  /**
   * Mark asset as disposed
   */
  public function dispose(?string $reason = null): bool
  {
    $this->update([
      'status_id' => Status::where('name', 'Disposed')->first()?->id ?? 4,
      'assigned_to' => null,
      'notes' => $this->notes . "\n\nDisposed: " . ($reason ?? 'No reason provided') . " (" . now()->format('Y-m-d') . ")",
    ]);

    // Log activity
    DailyActivity::create([
      'user_id' => Auth::id(),
      'activity_date' => today(),
      'description' => "Asset disposed: {$this->asset_tag} - " . ($reason ?? 'No reason provided'),
      'type' => 'asset_disposal',
      'notes' => "Asset disposal log",
    ]);

    return true;
  }

  /**
   * Get asset utilization percentage (based on assignment history)
   */
  public function getUtilizationPercentage(int $months = 12): float
  {
    if (!$this->purchase_date) return 0;

    $totalDays = min($this->purchase_date->diffInDays(now()), $months * 30);
    if ($totalDays <= 0) return 0;

    // This is a simplified calculation - in reality you'd track assignment history
    $assignedDays = $this->isAssigned() ? $totalDays : 0;
    
    return round(($assignedDays / $totalDays) * 100, 2);
  }

  /**
   * Get maintenance cost (sum of related ticket costs - if tracked)
   */
  public function getMaintenanceCost(): float
  {
    // This would require additional fields in tickets table for cost tracking
    return $this->tickets()
                ->whereNotNull('cost')
                ->sum('cost') ?? 0;
  }

  /**
   * Get asset statistics
   */
  public static function getStatistics(): array
  {
    return [
      'total' => self::count(),
      'in_use' => self::whereHas('status', fn($q) => $q->where('name', 'In Use'))->count(),
      'in_stock' => self::whereHas('status', fn($q) => $q->where('name', 'In Stock'))->count(),
      'in_repair' => self::whereHas('status', fn($q) => $q->where('name', 'In Repair'))->count(),
      'disposed' => self::whereHas('status', fn($q) => $q->where('name', 'Disposed'))->count(),
      'warranty_expiring' => self::warrantyExpiring(30)->count(),
      'warranty_expired' => self::warrantyExpired()->count(),
      'unassigned' => self::unassigned()->count(),
      'requiring_maintenance' => self::whereHas('tickets', function($q) {
        $q->whereNull('resolved_at');
      })->count(),
    ];
  }

  /**
   * Get assets requiring attention (expiring warranty, maintenance needed, etc.)
   */
  public static function getAssetsRequiringAttention()
  {
    return [
      'warranty_expiring' => self::warrantyExpiring(30)->with(['model', 'assignedTo'])->get(),
      'warranty_expired' => self::warrantyExpired()->with(['model', 'assignedTo'])->get(),
      'maintenance_required' => self::whereHas('tickets', function($q) {
        $q->whereNull('resolved_at')
          ->whereHas('ticket_priority', fn($q2) => $q2->whereIn('name', ['Urgent', 'High']));
      })->with(['model', 'assignedTo', 'tickets.ticket_priority'])->get(),
      'unassigned_stock' => self::unassigned()->inStock()->with(['model', 'division'])->get(),
    ];
  }

  public function getWarrantyStatusAttribute()
  {
    if (!$this->purchase_date || !$this->warranty_months) {
      return 'No warranty info';
    }

    $warrantyExpiry = $this->purchase_date->addMonths($this->warranty_months);
    
    if ($warrantyExpiry->isPast()) {
      return 'Expired';
    } elseif ($warrantyExpiry->diffInDays(now()) <= 30) {
      return 'Expiring soon';
    } else {
      return 'Active';
    }
  }

  /**
   * Convenience accessor to expose the asset type via the related model.
   * Many views reference `$asset->assetType` for display; this accessor
   * returns the related AssetType model (or null) while preserving the
   * canonical relation which lives on AssetModel.
   */
  public function getAssetTypeAttribute()
  {
    return $this->model ? $this->model->asset_type : null;
  }

  /**
   * Get ticket history for this asset
   */
  public function getTicketHistory()
  {
    return $this->tickets()
                ->with(['user', 'ticket_status', 'ticket_priority', 'ticket_type'])
                ->orderBy('created_at', 'desc')
                ->get();
  }

  /**
   * Get maintenance tickets count
   */
  public function getMaintenanceTicketsCount()
  {
    return $this->tickets()
                ->where('ticket_type_id', 1) // Assuming 1 = Maintenance
                ->count();
  }



  /**
   * Get average resolution time for this asset's tickets
   */
  public function getAverageResolutionTime()
  {
    $resolvedTickets = $this->tickets()
                            ->whereNotNull('resolved_at')
                            ->get();

    if ($resolvedTickets->isEmpty()) {
      return null;
    }

    $totalMinutes = 0;
    foreach ($resolvedTickets as $ticket) {
      $totalMinutes += $ticket->created_at->diffInMinutes($ticket->resolved_at);
    }

    return round($totalMinutes / $resolvedTickets->count());
  }

  /**
   * Get recent issues (last 30 days)
   */
  public function getRecentIssues()
  {
    return $this->tickets()
                ->where('tickets.created_at', '>=', Carbon::now()->subDays(30))
                ->with(['ticket_status', 'ticket_priority'])
                ->orderBy('tickets.created_at', 'desc')
                ->get();
  }

  /**
   * Log maintenance activity
   */
  public function logMaintenanceActivity($description, $userId = null)
  {
    DailyActivity::create([
      'user_id' => $userId ?? Auth::id(),
      'activity_date' => Carbon::today(),
      'type' => 'maintenance',
      'description' => "Asset {$this->asset_tag}: {$description}",
      'duration_minutes' => null,
      'notes' => "Automated log entry for asset maintenance"
    ]);
  }

  // ========================
  // ADDITIONAL TICKET HISTORY METHODS
  // ========================

  /**
   * Get recent tickets (last 10)
   */
  public function getRecentTicketsAttribute()
  {
    return $this->tickets()
                ->with(['ticket_status', 'ticket_priority', 'ticket_type'])
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get();
  }

  /**
   * Get open tickets count
   */
  public function getOpenTicketsCountAttribute()
  {
    return $this->tickets()
                ->whereHas('ticket_status', function($query) {
                    $query->whereIn('status', ['Open', 'In Progress', 'Pending']);
                })
                ->count();
  }

  /**
   * Get resolved tickets count (includes Closed tickets)
   */
  public function getResolvedTicketsCountAttribute()
  {
    return $this->tickets()
                ->whereHas('ticket_status', function($query) {
                    $query->whereIn('status', ['Resolved', 'Closed']);
                })
                ->count();
  }

  /**
   * Get last ticket date
   */
  public function getLastTicketDateAttribute()
  {
    $lastTicket = $this->tickets()->orderBy('created_at', 'desc')->first();
    return $lastTicket ? $lastTicket->created_at : null;
  }

  /**
   * Get asset health score based on ticket history
   * 0-100 score where 100 is excellent (no issues)
   */
  public function getHealthScoreAttribute()
  {
    $totalTickets = $this->tickets()->count();
    
    if ($totalTickets == 0) {
        return 100; // Perfect score for no reported issues
    }

  $recentTickets = $this->tickets()->where('tickets.created_at', '>=', now()->subMonths(6))->count();
    $openTickets = $this->open_tickets_count;
    
    // Base score calculation
    $score = 100;
    
    // Deduct points for recent tickets (more recent issues = lower score)
    $score -= ($recentTickets * 10);
    
    // Deduct more points for open tickets (unresolved issues)
    $score -= ($openTickets * 20);
    
    // Ensure score doesn't go below 0
    return max(0, $score);
  }

  /**
   * Check if asset needs attention (has open tickets or recent frequent issues)
   */
  public function getNeedsAttentionAttribute()
  {
    return $this->open_tickets_count > 0 || $this->health_score < 70;
  }
}
