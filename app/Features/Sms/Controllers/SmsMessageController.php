<?php

namespace App\Features\Sms\Controllers;

use App\Features\Members\Models\Member;
use App\Features\Sms\Enums\SmsPlaceholder;
use App\Features\Sms\Models\SmsMessage;
use App\Features\Sms\Models\SmsTemplate;
use App\Features\Sms\Requests\PreviewSmsRequest;
use App\Features\Sms\Requests\SendSmsRequest;
use App\Features\Sms\Services\SmsService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

/**
 * HTTP controller for Sms Message.
 */
class SmsMessageController extends Controller
{
    /**
     * Create a new instance.
     */
    public function __construct(private SmsService $smsService) {}

    /**
     * Index.
     */
    public function index(): Response
    {
        $this->authorize('viewAny', SmsMessage::class);

        $messages = SmsMessage::query()
            ->with(['member', 'template', 'sender'])
            ->latest()
            ->paginate(20);

        return Inertia::render('portal/sms-messages/index', [
            'messages' => $messages,
        ]);
    }

    /**
     * Create.
     */
    public function create(): Response
    {
        $this->authorize('create', SmsMessage::class);

        return Inertia::render('portal/sms-messages/create', [
            'templates' => SmsTemplate::query()->where('status', 'active')->orderBy('name')->get(),
            'members' => Member::query()
                ->where('status', 'active')
                ->orderBy('full_name')
                ->get(['id', 'full_name', 'membership_number', 'phone_number']),
            'placeholders' => SmsPlaceholder::definitions(),
        ]);
    }

    /**
     * Store.
     */
    public function store(SendSmsRequest $request): RedirectResponse
    {
        $this->authorize('create', SmsMessage::class);

        $template = SmsTemplate::query()->findOrFail($request->validated('sms_template_id'));

        $members = Member::query()
            ->whereIn('id', $request->validated('member_ids'))
            ->get();

        $sent = $this->smsService->sendToMembers($template, $members, $request->user());

        return redirect()->route('portal.sms-messages.index')
            ->with('success', "{$sent->count()} SMS message(s) sent successfully.");
    }

    /**
     * Preview.
     */
    public function preview(PreviewSmsRequest $request): JsonResponse
    {
        $this->authorize('create', SmsMessage::class);

        $template = SmsTemplate::query()->findOrFail($request->validated('sms_template_id'));
        $member = Member::query()->findOrFail($request->validated('member_id'));

        return response()->json([
            'body' => $this->smsService->preview($template, $member),
        ]);
    }
}
