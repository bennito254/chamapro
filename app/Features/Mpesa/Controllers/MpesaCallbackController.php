<?php

namespace App\Features\Mpesa\Controllers;

use App\Features\Mpesa\Services\MpesaService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * HTTP controller for Mpesa Callback.
 */
class MpesaCallbackController extends Controller
{
    /**
     * Create a new instance.
     */
    public function __construct(private MpesaService $mpesaService) {}

    /**
     * Handle.
     */
    public function handle(Request $request): JsonResponse
    {
        $log = $this->mpesaService->processCallback($request->all());

        return response()->json([
            'ResultCode' => 0,
            'ResultDesc' => 'Accepted',
            'log_id' => $log->id,
        ]);
    }
}
