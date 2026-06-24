<?php

namespace App\Features\Members\Controllers;

use App\Features\Members\Models\Member;
use App\Features\Members\Requests\ImportMembersRequest;
use App\Features\Members\Requests\StoreMemberRequest;
use App\Features\Members\Requests\UpdateMemberRequest;
use App\Features\Members\Services\MemberActivityService;
use App\Features\Reports\Services\ReportService;
use App\Features\Subscriptions\Services\SubscriptionService;
use App\Http\Controllers\Controller;
use App\Support\GroupContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * HTTP controller for Member.
 */
class MemberController extends Controller
{
    /**
     * Create a new instance.
     */
    public function __construct(
        private ReportService $reportService,
        private SubscriptionService $subscriptionService,
        private GroupContext $groupContext,
        private MemberActivityService $memberActivityService,
    ) {}

    /**
     * Index.
     */
    public function index(): Response
    {
        $this->authorize('viewAny', Member::class);

        $members = Member::query()
            ->latest('date_joined')
            ->paginate(15);

        return Inertia::render('portal/members/index', [
            'members' => $members,
        ]);
    }

    /**
     * Create.
     */
    public function create(): Response
    {
        $this->authorize('create', Member::class);

        return Inertia::render('portal/members/create');
    }

    /**
     * Store.
     */
    public function store(StoreMemberRequest $request): RedirectResponse
    {
        $this->authorize('create', Member::class);

        $group = $this->groupContext->get();

        if ($group && ! $this->subscriptionService->canAddMember($group)) {
            return back()->withErrors(['member' => 'Member limit reached for your subscription plan.']);
        }

        Member::create($request->validated());

        return redirect()->route('portal.members.index')
            ->with('success', 'Member created successfully.');
    }

    /**
     * Show.
     */
    public function show(Member $member): Response
    {
        $this->authorize('view', $member);

        $activity = $this->memberActivityService->forMember($member);

        return Inertia::render('portal/members/show', [
            'member' => $member,
            'activity' => $activity,
        ]);
    }

    /**
     * Edit.
     */
    public function edit(Member $member): Response
    {
        $this->authorize('update', $member);

        return Inertia::render('portal/members/edit', [
            'member' => $member,
        ]);
    }

    /**
     * Update.
     */
    public function update(UpdateMemberRequest $request, Member $member): RedirectResponse
    {
        $this->authorize('update', $member);

        $member->update($request->validated());

        return redirect()->route('portal.members.show', $member)
            ->with('success', 'Member updated successfully.');
    }

    /**
     * Destroy.
     */
    public function destroy(Member $member): RedirectResponse
    {
        $this->authorize('delete', $member);

        $member->delete();

        return redirect()->route('portal.members.index')
            ->with('success', 'Member deleted successfully.');
    }

    /**
     * Import.
     */
    public function import(ImportMembersRequest $request): RedirectResponse
    {
        $this->authorize('import', Member::class);

        $imported = 0;

        foreach ($request->validated('members') as $row) {
            Member::create($row);
            $imported++;
        }

        return back()->with('success', "{$imported} members imported successfully.");
    }

    /**
     * Statement.
     */
    public function statement(Request $request, Member $member): Response
    {
        $this->authorize('view', $member);

        $statement = $this->reportService->memberStatement(
            $member,
            $request->date('from'),
            $request->date('to'),
        );

        return Inertia::render('portal/members/statement', [
            'member' => $member,
            'statement' => $statement,
            'filters' => $request->only(['from', 'to']),
        ]);
    }
}
