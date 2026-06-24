<?php

namespace App\Features\Contributions\Controllers;

use App\Features\Contributions\Models\Contribution;
use App\Features\Contributions\Models\ContributionChannel;
use App\Features\Contributions\Models\ContributionType;
use App\Features\Contributions\Requests\StoreBulkContributionsRequest;
use App\Features\Contributions\Requests\StoreContributionRequest;
use App\Features\Contributions\Requests\UpdateContributionRequest;
use App\Features\Contributions\Services\ContributionEligibilityService;
use App\Features\Contributions\Services\ContributionService;
use App\Features\Meetings\Models\Meeting;
use App\Features\Members\Models\Member;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * HTTP controller for Contribution.
 */
class ContributionController extends Controller
{
    /**
     * Create a new instance.
     */
    public function __construct(
        private ContributionService $contributionService,
        private ContributionEligibilityService $contributionEligibilityService,
    ) {}

    /**
     * Index.
     */
    public function index(): Response
    {
        $this->authorize('viewAny', Contribution::class);

        $dateGroups = Contribution::query()
            ->selectRaw('date, COUNT(*) as contributions_count, COALESCE(SUM(amount), 0) as total_amount')
            ->groupBy('date')
            ->orderByDesc('date')
            ->paginate(15);

        $dateStrings = collect($dateGroups->items())->map(fn ($row) => $this->normalizeContributionDate($row->date))->unique();

        $meetings = Meeting::query()
            ->whereIn('date', $dateStrings)
            ->get()
            ->keyBy(fn (Meeting $meeting) => $meeting->date->format('Y-m-d'));

        $dateGroups->getCollection()->transform(function ($row) use ($meetings) {
            $dateKey = $this->normalizeContributionDate($row->date);

            return [
                'date' => $dateKey,
                'contributions_count' => (int) $row->contributions_count,
                'total_amount' => (float) $row->total_amount,
                'meeting_title' => $meetings->get($dateKey)?->title,
            ];
        });

        return Inertia::render('portal/contributions/index', [
            'dateGroups' => $dateGroups,
        ]);
    }

    /**
     * By date.
     */
    public function byDate(string $date): Response
    {
        $this->authorize('viewAny', Contribution::class);

        $meetingDate = $this->parseMeetingDate($date);

        $contributions = Contribution::query()
            ->with(['member', 'contributionType', 'contributionChannel'])
            ->whereDate('date', $meetingDate)
            ->get()
            ->sortBy(fn (Contribution $contribution) => $contribution->member?->full_name ?? '')
            ->values();

        $meeting = Meeting::query()->whereDate('date', $meetingDate)->first();

        return Inertia::render('portal/contributions/by-date', [
            'date' => $meetingDate->toDateString(),
            'meeting' => $meeting,
            'contributionGroups' => $this->contributionService->groupByType($contributions),
            'summary' => [
                'contributions_count' => $contributions->count(),
                'total_amount' => (float) $contributions->sum('amount'),
                'types_count' => $contributions->pluck('contribution_type_id')->unique()->count(),
            ],
        ]);
    }

    /**
     * Create.
     */
    public function create(): Response
    {
        $this->authorize('create', Contribution::class);

        return Inertia::render('portal/contributions/create', [
            ...$this->contributionFormOptions(),
        ]);
    }

    /**
     * Bulk.
     */
    public function bulk(Request $request): Response
    {
        $this->authorize('create', Contribution::class);

        $options = $this->contributionFormOptions();
        $date = $request->string('date')->toString() ?: ($options['defaultMeetingDate'] ?? now()->toDateString());
        $members = Member::query()
            ->where('status', 'active')
            ->orderBy('full_name')
            ->get(['id', 'full_name', 'membership_number']);

        return Inertia::render('portal/contributions/bulk', [
            ...$options,
            'members' => $members,
            'selectedDate' => $date,
            'memberContributionTotals' => $this->contributionEligibilityService->memberTotalsForDate(
                $members->pluck('id'),
                $date,
            ),
        ]);
    }

    /**
     * Bulk store.
     */
    public function bulkStore(StoreBulkContributionsRequest $request): RedirectResponse
    {
        $this->authorize('create', Contribution::class);

        $validated = $request->validated();
        $entries = $validated['entries'];

        $this->contributionService->recordMany(
            [
                'contribution_type_id' => $validated['contribution_type_id'],
                'contribution_channel_id' => $validated['contribution_channel_id'],
                'date' => $validated['date'],
            ],
            $entries,
            $request->user()->id,
        );

        return redirect()->route('portal.contributions.by-date', $validated['date'])
            ->with('success', count($entries).' contribution(s) recorded successfully.');
    }

    /**
     * Store.
     */
    public function store(StoreContributionRequest $request): RedirectResponse
    {
        $this->authorize('create', Contribution::class);

        $contribution = $this->contributionService->record([
            ...$request->validated(),
            'recorded_by' => $request->user()->id,
        ]);

        return redirect()->route('portal.contributions.by-date', $contribution->date->toDateString())
            ->with('success', 'Contribution recorded successfully.');
    }

    /**
     * Show.
     */
    public function show(Contribution $contribution): Response
    {
        $this->authorize('view', $contribution);

        $contribution->load(['member', 'contributionType', 'contributionChannel', 'recordedBy']);

        return Inertia::render('portal/contributions/show', [
            'contribution' => $contribution,
        ]);
    }

    /**
     * Edit.
     */
    public function edit(Contribution $contribution): Response
    {
        $this->authorize('update', $contribution);

        return Inertia::render('portal/contributions/edit', [
            'contribution' => $contribution,
            'members' => Member::orderBy('full_name')->get(['id', 'full_name', 'membership_number']),
            'types' => ContributionType::orderBy('name')->get(),
            'channels' => ContributionChannel::orderBy('name')->get(),
        ]);
    }

    /**
     * Update.
     */
    public function update(UpdateContributionRequest $request, Contribution $contribution): RedirectResponse
    {
        $this->authorize('update', $contribution);

        $contribution->update($request->validated());

        return redirect()->route('portal.contributions.by-date', $contribution->date->toDateString())
            ->with('success', 'Contribution updated successfully.');
    }

    /**
     * Destroy.
     */
    public function destroy(Contribution $contribution): RedirectResponse
    {
        $this->authorize('delete', $contribution);

        $date = $contribution->date->toDateString();
        $contribution->delete();

        return redirect()->route('portal.contributions.by-date', $date)
            ->with('success', 'Contribution deleted successfully.');
    }

    private function parseMeetingDate(string $date): Carbon
    {
        if (! preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            abort(404);
        }

        try {
            return Carbon::createFromFormat('Y-m-d', $date)->startOfDay();
        } catch (\Throwable) {
            abort(404);
        }
    }

    private function normalizeContributionDate(mixed $date): string
    {
        if ($date instanceof \DateTimeInterface) {
            return $date->format('Y-m-d');
        }

        return substr((string) $date, 0, 10);
    }

    /**
     * @return array<string, mixed>
     */
    private function contributionFormOptions(): array
    {
        $meetings = Meeting::query()
            ->orderByDesc('date')
            ->orderByDesc('id')
            ->get(['id', 'title', 'date']);

        return [
            'members' => Member::orderBy('full_name')->get(['id', 'full_name', 'membership_number']),
            'types' => ContributionType::where('status', 'active')->orderBy('name')->get(),
            'channels' => ContributionChannel::where('status', 'active')->orderBy('name')->get(),
            'meetings' => $meetings->map(fn (Meeting $meeting) => [
                'id' => $meeting->id,
                'title' => $meeting->title,
                'date' => $meeting->date->toDateString(),
            ])->values(),
            'defaultMeetingId' => $meetings->first()?->id,
            'defaultMeetingDate' => $meetings->first()?->date->toDateString() ?? now()->toDateString(),
        ];
    }
}
