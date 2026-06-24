<?php

namespace App\Features\Meetings\Controllers;

use App\Features\Meetings\Models\Meeting;
use App\Features\Meetings\Requests\StoreMeetingRequest;
use App\Features\Meetings\Requests\UpdateMeetingRequest;
use App\Features\Meetings\Services\MeetingSummaryService;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

/**
 * HTTP controller for Meeting.
 */
class MeetingController extends Controller
{
    /**
     * Create a new instance.
     */
    public function __construct(private MeetingSummaryService $meetingSummaryService) {}

    /**
     * Index.
     */
    public function index(): Response
    {
        $meetings = Meeting::query()
            ->withCount('attendees')
            ->latest('date')
            ->paginate(15);

        return Inertia::render('portal/meetings/index', [
            'meetings' => $meetings,
        ]);
    }

    /**
     * Create.
     */
    public function create(): Response
    {
        $today = now();

        return Inertia::render('portal/meetings/create', [
            'defaults' => [
                'title' => $today->format('l j, F Y'),
                'date' => $today->toDateString(),
                'status' => 'scheduled',
            ],
        ]);
    }

    /**
     * Store.
     */
    public function store(StoreMeetingRequest $request): RedirectResponse
    {
        Meeting::create([
            ...$request->validated(),
            'created_by' => $request->user()->id,
        ]);

        return redirect()->route('portal.meetings.index')
            ->with('success', 'Meeting created successfully.');
    }

    /**
     * Show.
     */
    public function show(Meeting $meeting): Response
    {
        $meeting->load(['createdBy']);

        return Inertia::render('portal/meetings/show', [
            'meeting' => $meeting,
            'summary' => $this->meetingSummaryService->summarize($meeting),
        ]);
    }

    /**
     * Edit.
     */
    public function edit(Meeting $meeting): Response
    {
        return Inertia::render('portal/meetings/edit', [
            'meeting' => $meeting,
        ]);
    }

    /**
     * Update.
     */
    public function update(UpdateMeetingRequest $request, Meeting $meeting): RedirectResponse
    {
        $meeting->update($request->validated());

        return redirect()->route('portal.meetings.show', $meeting)
            ->with('success', 'Meeting updated successfully.');
    }

    /**
     * Destroy.
     */
    public function destroy(Meeting $meeting): RedirectResponse
    {
        $meeting->delete();

        return redirect()->route('portal.meetings.index')
            ->with('success', 'Meeting deleted successfully.');
    }
}
