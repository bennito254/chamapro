<?php

namespace App\Features\Sms\Controllers;

use App\Features\Sms\Enums\SmsPlaceholder;
use App\Features\Sms\Models\SmsTemplate;
use App\Features\Sms\Requests\StoreSmsTemplateRequest;
use App\Features\Sms\Requests\UpdateSmsTemplateRequest;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

/**
 * HTTP controller for Sms Template.
 */
class SmsTemplateController extends Controller
{
    /**
     * Index.
     */
    public function index(): Response
    {
        $this->authorize('viewAny', SmsTemplate::class);

        $templates = SmsTemplate::query()
            ->latest()
            ->paginate(15);

        return Inertia::render('portal/sms-templates/index', [
            'templates' => $templates,
        ]);
    }

    /**
     * Create.
     */
    public function create(): Response
    {
        $this->authorize('create', SmsTemplate::class);

        return Inertia::render('portal/sms-templates/create', [
            'placeholders' => SmsPlaceholder::definitions(),
        ]);
    }

    /**
     * Store.
     */
    public function store(StoreSmsTemplateRequest $request): RedirectResponse
    {
        $this->authorize('create', SmsTemplate::class);

        SmsTemplate::create($request->validated());

        return redirect()->route('portal.sms-templates.index')
            ->with('success', 'SMS template created successfully.');
    }

    /**
     * Edit.
     */
    public function edit(SmsTemplate $smsTemplate): Response
    {
        $this->authorize('update', $smsTemplate);

        return Inertia::render('portal/sms-templates/edit', [
            'template' => $smsTemplate,
            'placeholders' => SmsPlaceholder::definitions(),
        ]);
    }

    /**
     * Update.
     */
    public function update(UpdateSmsTemplateRequest $request, SmsTemplate $smsTemplate): RedirectResponse
    {
        $this->authorize('update', $smsTemplate);

        $smsTemplate->update($request->validated());

        return redirect()->route('portal.sms-templates.index')
            ->with('success', 'SMS template updated successfully.');
    }

    /**
     * Destroy.
     */
    public function destroy(SmsTemplate $smsTemplate): RedirectResponse
    {
        $this->authorize('delete', $smsTemplate);

        $smsTemplate->delete();

        return redirect()->route('portal.sms-templates.index')
            ->with('success', 'SMS template deleted successfully.');
    }
}
