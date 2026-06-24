<?php

use App\Features\Mpesa\Controllers\MpesaCallbackController;
use App\Features\Mpesa\Controllers\MpesaStkPushController;
use Illuminate\Support\Facades\Route;

Route::post('mpesa/callback', [MpesaCallbackController::class, 'handle'])->name('mpesa.callback');

Route::middleware(['auth:sanctum'])->group(function () {
    Route::post('mpesa/stk-push', [MpesaStkPushController::class, 'initiate'])->name('mpesa.stk-push');
});
