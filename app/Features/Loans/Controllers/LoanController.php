<?php

namespace App\Features\Loans\Controllers;

use App\Enums\LoanStatus;
use App\Features\Loans\Models\Loan;
use App\Features\Loans\Services\LoanRepaymentService;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * HTTP controller for Loan.
 */
class LoanController extends Controller
{
    /**
     * Create a new instance.
     */
    public function __construct(private LoanRepaymentService $loanRepaymentService) {}

    /**
     * Index.
     */
    public function index(Request $request): Response
    {
        $search = $request->string('search')->trim()->toString();
        $status = $request->string('status')->trim()->toString();

        $loans = Loan::query()
            ->with(['member', 'loanProduct'])
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($query) use ($search): void {
                    $query->where('product_name', 'like', "%{$search}%")
                        ->orWhereHas('member', function ($query) use ($search): void {
                            $query->where('full_name', 'like', "%{$search}%")
                                ->orWhere('membership_number', 'like', "%{$search}%");
                        });
                });
            })
            ->when(
                $status !== '' && $status !== 'all',
                fn ($query) => $query->where('status', LoanStatus::from($status)),
            )
            ->latest('disbursement_date')
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('portal/loans/index', [
            'loans' => $loans,
            'filters' => [
                'search' => $search,
                'status' => $status !== '' ? $status : 'all',
            ],
        ]);
    }

    /**
     * Show.
     */
    public function show(Loan $loan): Response
    {
        $loan->load(['member', 'loanProduct', 'loanApplication', 'repayments']);

        return Inertia::render('portal/loans/show', [
            'loan' => $loan,
            'interest_outstanding' => $this->loanRepaymentService->interestOutstanding($loan),
            'principal_outstanding' => $this->loanRepaymentService->principalOutstanding($loan),
        ]);
    }
}
