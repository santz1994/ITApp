<?php

namespace App\Http\Controllers;

use App\Status;
use App\Division;
use App\Supplier;
use App\Invoice;
use App\WarrantyType;
use App\Http\Controllers\Controller as BaseController;

class SystemSettingsController extends BaseController
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('role:developer'); // Only developer can access system settings
    }

    /**
     * Display system settings dashboard
     */
    public function index()
    {
        $pageTitle = 'System Settings';
        
        $stats = [
            'asset_configs' => [
                'asset_statuses' => Status::count(),
                'divisions' => Division::count(),
                'suppliers' => Supplier::count(),
                'invoices' => Invoice::count(),
                'warranty_types' => WarrantyType::count(),
            ]
        ];

        return view('system-settings.index', compact('pageTitle', 'stats'));
    }

    // Ticket configuration pages removed (legacy)

    // ========================================
    // ASSET CONFIGURATIONS
    // ========================================

    /**
     * Manage Asset Statuses
     */
    public function assetStatuses()
    {
        $pageTitle = 'Asset Statuses Management';
        $statuses = Status::orderBy('name')->paginate(20);
        
        return view('system-settings.asset-configs.statuses', compact('pageTitle', 'statuses'));
    }

    /**
     * Manage Divisions
     */
    public function divisions()
    {
        $pageTitle = 'Divisions Management';
        $divisions = Division::orderBy('name')->paginate(20);
        
        return view('system-settings.asset-configs.divisions', compact('pageTitle', 'divisions'));
    }

    /**
     * Manage Suppliers
     */
    public function suppliers()
    {
        $pageTitle = 'Suppliers Management';
        $suppliers = Supplier::orderBy('name')->paginate(20);
        
        return view('system-settings.asset-configs.suppliers', compact('pageTitle', 'suppliers'));
    }

    /**
     * Manage Invoices
     */
    public function invoices()
    {
        $pageTitle = 'Invoices Management';
        $invoices = Invoice::with(['supplier'])->orderBy('created_at', 'desc')->paginate(20);
        $suppliers = Supplier::orderBy('name')->get();
        
        return view('system-settings.asset-configs.invoices', compact('pageTitle', 'invoices', 'suppliers'));
    }

    /**
     * Manage Warranty Types
     */
    public function warrantyTypes()
    {
        $pageTitle = 'Warranty Types Management';
        $warrantyTypes = WarrantyType::orderBy('name')->paginate(20);
        
        return view('system-settings.asset-configs.warranty-types', compact('pageTitle', 'warrantyTypes'));
    }

    // ========================================
    // STOREROOM MANAGEMENT
    // ========================================

    /**
     * Manage Storeroom
     */
    public function storeroom()
    {
        $pageTitle = 'Storeroom Management';
        
        // For now, return empty collection until StoreroomItem model is created
        // TODO: Create StoreroomItem model with fields: name, description, category, 
        // sku, quantity, min_quantity, unit, unit_price
        $storeroomItems = collect();
        
        return view('system-settings.storeroom.index', compact('pageTitle', 'storeroomItems'));
    }
}