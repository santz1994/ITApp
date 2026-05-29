<?php

namespace App\Http\Controllers;

use App\Division;
use App\Http\Controllers\Controller as BaseController;

class SystemSettingsController extends BaseController
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('role:developer');
    }

    public function index()
    {
        $pageTitle = 'System Settings';
        
        $stats = [
            'divisions' => Division::count(),
            'vehicles' => \App\Vehicle::count(),
            'inventory_items' => \App\InventoryItem::count(),
            'inventory_categories' => \App\InventoryCategory::count(),
        ];

        return view('system-settings.index', compact('pageTitle', 'stats'));
    }

    public function divisions()
    {
        $pageTitle = 'Divisions Management';
        $divisions = Division::orderBy('name')->paginate(20);
        
        return view('system-settings.divisions', compact('pageTitle', 'divisions'));
    }
}