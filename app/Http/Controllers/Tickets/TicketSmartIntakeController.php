<?php

namespace App\Http\Controllers\Tickets;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\V1\StoreSmartTicketIntakeRequest;
use App\Http\Resources\API\V1\SmartTicketIntakeResource;
use App\Services\SmartTicketIntakeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class TicketSmartIntakeController extends Controller
{
    private SmartTicketIntakeService $smartTicketIntakeService;

    public function __construct(SmartTicketIntakeService $smartTicketIntakeService)
    {
        $this->smartTicketIntakeService = $smartTicketIntakeService;
    }

    public function store(StoreSmartTicketIntakeRequest $request): JsonResponse
    {
        try {
            $analysis = $this->smartTicketIntakeService->analyze(
                (string) $request->input('subject'),
                (string) $request->input('description'),
                true
            );

            return (new SmartTicketIntakeResource($analysis))
                ->response()
                ->setStatusCode(200);
        } catch (\Throwable $exception) {
            Log::error('Failed to generate smart ticket intake recommendation from web route', [
                'user_id' => auth()->id(),
                'exception_message' => $exception->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'SMART_TICKET_INTAKE_FAILED',
                    'message' => 'Failed to generate smart ticket recommendation.',
                ],
                'message' => 'Unable to process smart suggestion request.',
            ], 500);
        }
    }
}
