<?php

namespace App\Features\Portal\Controllers;

use App\Features\Contributions\Models\Contribution;
use App\Features\Fines\Models\Fine;
use App\Features\Loans\Models\Loan;
use App\Features\Loans\Services\LoanFundService;
use App\Features\Members\Models\Member;
use App\Http\Controllers\Controller;
use App\Support\GroupContext;
use Inertia\Inertia;
use Inertia\Response;

/**
 * HTTP controller for Portal Dashboard.
 */
class PortalDashboardController extends Controller
{
    /**
     * Create a new instance.
     */
    public function __construct(
        private GroupContext $groupContext,
        private LoanFundService $loanFundService,
    ) {}

    /**
     * Index.
     */
    public function index(): Response
    {
        $group = $this->groupContext->get();

        $stats = [
            'members_total' => Member::count(),
            'members_active' => Member::where('status', 'active')->count(),
            'contributions_month' => (float) Contribution::whereMonth('date', now()->month)->sum('amount'),
            'loan_fund_available' => $this->loanFundService->availableForLoans(),
            'loans_active' => Loan::where('status', 'active')->count(),
            'fines_unpaid' => Fine::where('is_paid', false)->count(),
        ];

        $recentContributions = Contribution::query()
            ->with(['member', 'contributionType'])
            ->latest('date')
            ->limit(5)
            ->get();

        return Inertia::render('portal/dashboard', [
            'group' => $group,
            'stats' => $stats,
            'recentContributions' => $recentContributions,
        ]);
    }
}
