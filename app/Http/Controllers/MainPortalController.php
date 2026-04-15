<?php

namespace App\Http\Controllers;

use App\Services\MainPortalService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class MainPortalController extends Controller
{
    protected MainPortalService $portalService;

    public function __construct(MainPortalService $portalService)
    {
        $this->middleware('auth');
        $this->portalService = $portalService;
    }

    /**
     * Display the role-aware main portal dashboard.
     */
    public function index(Request $request): View
    {
        /** @var \App\User $user */
        $user = $request->user();

        $portalData = $this->portalService->buildPortalData($user);

        return view('portal.index', $portalData);
    }
}
