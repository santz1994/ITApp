<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\V1\StoreSmartTicketIntakeRequest;
use App\Http\Resources\API\V1\SmartTicketIntakeResource;
use App\Services\SmartTicketIntakeService;
use Illuminate\Http\JsonResponse;

class TicketIntelligenceController extends Controller
{
    private SmartTicketIntakeService $smartTicketIntakeService;

    public function __construct(SmartTicketIntakeService $smartTicketIntakeService)
    {
        $this->smartTicketIntakeService = $smartTicketIntakeService;
    }

    public function store(StoreSmartTicketIntakeRequest $request): JsonResponse
    {
        $analysis = $this->smartTicketIntakeService->analyze(
            (string) $request->input('subject'),
            (string) $request->input('description'),
            true
        );

        return (new SmartTicketIntakeResource($analysis))
            ->response()
            ->setStatusCode(200);
    }
}
