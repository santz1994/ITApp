<?php

namespace App\Http\Controllers;

use App\Services\PurchaseRequestService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class PurchaseRequestPortalController extends Controller
{
    protected PurchaseRequestService $purchaseRequestService;

    public function __construct(PurchaseRequestService $purchaseRequestService)
    {
        $this->middleware('auth');
        $this->purchaseRequestService = $purchaseRequestService;
    }

    /**
     * Display modular purchase request dashboard.
     */
    public function index(Request $request): View
    {
        /** @var \App\User $user */
        $user = $request->user();

        $viewData = $this->purchaseRequestService->buildDashboardData($user);

        return view('purchase-requests.index', $viewData);
    }
}
