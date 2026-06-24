<?php

namespace App\Features\Loans\Controllers;

use App\Enums\LoanApplicationStatus;
use App\Features\Loans\Models\LoanApplication;
use App\Features\Loans\Models\LoanProduct;
use App\Features\Loans\Requests\StoreLoanApplicationRequest;
use App\Features\Loans\Requests\TransitionLoanApplicationRequest;
use App\Features\Loans\Requests\UpdateLoanApplicationRequest;
use App\Features\Loans\Services\LoanApplicationService;
use App\Features\Loans\Services\LoanDisbursementService;
use App\Features\Members\Models\Member;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

/**
 * HTTP controller for Loan Application.
 */
class LoanApplicationController extends Controller
{
    /**
     * Create a new instance.
     */
    public function __construct(
        private LoanApplicationService $loanApplicationService,
        private LoanDisbursementService $loanDisbursementService,
    ) {}

    /**
     * Index.
     */
    public function index(): Response
    {
        $this->authorize('viewAny', LoanApplication::class);

        $applications = LoanApplication::query()
            ->with(['member', 'loanProduct'])
            ->latest()
            ->paginate(15);

        return Inertia::render('portal/loan-applications/index', [
            'applications' => $applications,
        ]);
    }

    /**
     * Create.
     */
    public function create(): Response
    {
        $this->authorize('create', LoanApplication::class);

        return Inertia::render('portal/loan-applications/create', [
            'members' => Member::orderBy('full_name')->get(['id', 'full_name', 'membership_number']),
            'products' => LoanProduct::where('status', 'active')->get(),
        ]);
    }

    /**
     * Store.
     */
    public function store(StoreLoanApplicationRequest $request): RedirectResponse
    {
        $this->authorize('create', LoanApplication::class);

        $application = LoanApplication::create([
            ...$request->validated(),
            'status' => LoanApplicationStatus::Draft,
        ]);

        $application = $this->loanApplicationService->autoApprove($application, $request->user()->id);
        $loan = $this->loanDisbursementService->disburse($application, disbursedBy: $request->user()->id);

        return redirect()->route('portal.loans.show', $loan)
            ->with('success', 'Loan approved and added to active loans.');
    }

    /**
     * Show.
     */
    public function show(LoanApplication $loanApplication): Response
    {
        $this->authorize('view', $loanApplication);

        $loanApplication->load(['member', 'loanProduct', 'guarantors.member', 'loan', 'reviewedBy']);

        return Inertia::render('portal/loan-applications/show', [
            'application' => $loanApplication,
        ]);
    }

    /**
     * Edit.
     */
    public function edit(LoanApplication $loanApplication): Response
    {
        $this->authorize('update', $loanApplication);

        return Inertia::render('portal/loan-applications/edit', [
            'application' => $loanApplication,
            'members' => Member::orderBy('full_name')->get(['id', 'full_name', 'membership_number']),
            'products' => LoanProduct::where('status', 'active')->get(),
        ]);
    }

    /**
     * Update.
     */
    public function update(UpdateLoanApplicationRequest $request, LoanApplication $loanApplication): RedirectResponse
    {
        $this->authorize('update', $loanApplication);

        $loanApplication->update($request->validated());

        return redirect()->route('portal.loan-applications.show', $loanApplication)
            ->with('success', 'Loan application updated successfully.');
    }

    /**
     * Destroy.
     */
    public function destroy(LoanApplication $loanApplication): RedirectResponse
    {
        $this->authorize('delete', $loanApplication);

        $loanApplication->delete();

        return redirect()->route('portal.loan-applications.index')
            ->with('success', 'Loan application deleted successfully.');
    }

    /**
     * Transition.
     */
    public function transition(TransitionLoanApplicationRequest $request, LoanApplication $loanApplication): RedirectResponse
    {
        $this->authorize('transition', $loanApplication);

        $status = LoanApplicationStatus::from($request->validated('status'));
        $userId = $request->user()->id;
        $notes = $request->validated('review_notes');

        match ($status) {
            LoanApplicationStatus::Submitted => $this->loanApplicationService->submit($loanApplication),
            LoanApplicationStatus::UnderReview => $this->loanApplicationService->startReview($loanApplication, $userId),
            LoanApplicationStatus::Recommended => $this->loanApplicationService->recommend($loanApplication, $notes, $userId),
            LoanApplicationStatus::Approved => $this->approveAndDisburse($loanApplication, $notes, $userId),
            LoanApplicationStatus::Rejected => $this->loanApplicationService->reject($loanApplication, $notes, $userId),
            LoanApplicationStatus::Disbursed => $this->loanApplicationService->markDisbursed($loanApplication),
            LoanApplicationStatus::Closed => $this->loanApplicationService->close($loanApplication),
            default => null,
        };

        return back()->with('success', 'Loan application status updated.');
    }

    private function approveAndDisburse(LoanApplication $application, ?string $notes, int $userId): void
    {
        $application = $this->loanApplicationService->approve($application, $notes, $userId);
        $this->loanDisbursementService->disburse($application, disbursedBy: $userId);
    }
}
