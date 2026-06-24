<?php

namespace App\Features\Mpesa\Controllers;

use App\Features\Mpesa\Requests\InitiateMpesaStkPushRequest;
use App\Features\Mpesa\Services\MpesaService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

/**
 * HTTP controller for Mpesa Stk Push.
 */
class MpesaStkPushController extends Controller
{
    /**
     * Create a new instance.
     */
    public function __construct(private MpesaService $mpesaService) {}

    /**
     * Initiate.
     */
    public function initiate(InitiateMpesaStkPushRequest $request): JsonResponse
    {
        $transaction = $this->mpesaService->initiateStkPush($request->validated());

        return response()->json([
            'message' => 'STK push initiated.',
            'transaction' => $transaction,
        ]);
    }
}
