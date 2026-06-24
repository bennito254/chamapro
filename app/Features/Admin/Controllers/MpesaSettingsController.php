<?php

namespace App\Features\Admin\Controllers;

use App\Features\Admin\Requests\UpdateMpesaSettingsRequest;
use App\Features\Admin\Services\PlatformMpesaSettingsService;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;

/**
 * HTTP controller for M-Pesa platform settings.
 */
class MpesaSettingsController extends Controller
{
    public function __construct(
        private PlatformMpesaSettingsService $mpesaSettings,
    ) {}

    /**
     * Update platform M-Pesa settings.
     */
    public function update(UpdateMpesaSettingsRequest $request): RedirectResponse
    {
        $this->mpesaSettings->sync($request->mpesaSettings());

        return back()->with('success', 'M-Pesa settings saved successfully.');
    }
}
