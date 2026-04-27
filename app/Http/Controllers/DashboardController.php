<?php

namespace App\Http\Controllers;

use App\Services\DashboardService;

class DashboardController extends Controller
{
    protected DashboardService $dashboardService;

    public function __construct(DashboardService $dashboardService)
    {
        $this->dashboardService = $dashboardService;
        $this->middleware('auth');
    }

    public function index()
    {
        $dashboardData = $this->dashboardService->buildDashboardData();

        return view('dashboard.integrated-dashboard', $dashboardData);
    }
}
