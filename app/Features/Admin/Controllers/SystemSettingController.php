<?php

namespace App\Features\Admin\Controllers;

use App\Features\Admin\Models\SystemSetting;
use App\Features\Admin\Requests\UpdateSystemSettingRequest;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

/**
 * HTTP controller for System Setting.
 */
class SystemSettingController extends Controller
{
    /**
     * Index.
     */
    public function index(): Response
    {
        $settings = SystemSetting::query()
            ->orderBy('key')
            ->get()
            ->keyBy('key');

        return Inertia::render('admin/system-settings/index', [
            'settings' => $settings,
        ]);
    }

    /**
     * Update.
     */
    public function update(UpdateSystemSettingRequest $request): RedirectResponse
    {
        foreach ($request->validated('settings') as $key => $value) {
            SystemSetting::updateOrCreate(
                ['key' => $key],
                ['value' => is_array($value) ? json_encode($value) : (string) $value],
            );
        }

        return back()->with('success', 'System settings updated successfully.');
    }
}
