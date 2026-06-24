<?php

namespace App\Features\Admin\Controllers;

use App\Features\Admin\Models\SmsProvider;
use App\Features\Admin\Requests\StoreSmsProviderRequest;
use App\Features\Admin\Requests\UpdateSmsProviderRequest;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

/**
 * HTTP controller for Sms Provider.
 */
class SmsProviderController extends Controller
{
    /**
     * Index.
     */
    public function index(): Response
    {
        $providers = SmsProvider::query()
            ->latest()
            ->paginate(15);

        return Inertia::render('admin/sms-providers/index', [
            'providers' => $providers,
        ]);
    }

    /**
     * Create.
     */
    public function create(): Response
    {
        return Inertia::render('admin/sms-providers/create');
    }

    /**
     * Store.
     */
    public function store(StoreSmsProviderRequest $request): RedirectResponse
    {
        if ($request->boolean('is_default')) {
            SmsProvider::query()->update(['is_default' => false]);
        }

        SmsProvider::create($request->validated());

        return redirect()->route('admin.sms-providers.index')
            ->with('success', 'SMS provider created successfully.');
    }

    /**
     * Edit.
     */
    public function edit(SmsProvider $smsProvider): Response
    {
        return Inertia::render('admin/sms-providers/edit', [
            'provider' => $smsProvider,
        ]);
    }

    /**
     * Update.
     */
    public function update(UpdateSmsProviderRequest $request, SmsProvider $smsProvider): RedirectResponse
    {
        if ($request->boolean('is_default')) {
            SmsProvider::query()->where('id', '!=', $smsProvider->id)->update(['is_default' => false]);
        }

        $smsProvider->update($request->validated());

        return redirect()->route('admin.sms-providers.index')
            ->with('success', 'SMS provider updated successfully.');
    }

    /**
     * Destroy.
     */
    public function destroy(SmsProvider $smsProvider): RedirectResponse
    {
        $smsProvider->delete();

        return redirect()->route('admin.sms-providers.index')
            ->with('success', 'SMS provider deleted successfully.');
    }
}
