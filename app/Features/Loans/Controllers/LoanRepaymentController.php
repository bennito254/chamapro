<?php

namespace App\Features\Loans\Controllers;

use App\Features\Loans\Models\Loan;
use App\Features\Loans\Requests\StoreLoanRepaymentRequest;
use App\Features\Loans\Services\LoanRepaymentService;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;

/**
 * HTTP controller for Loan Repayment.
 */
class LoanRepaymentController extends Controller
{
    /**
     * Create a new instance.
     */
    public function __construct(private LoanRepaymentService $loanRepaymentService) {}

    /**
     * Store.
     */
    public function store(StoreLoanRepaymentRequest $request, Loan $loan): RedirectResponse
    {
        $this->loanRepaymentService->record($loan, [
            ...$request->validated(),
            'recorded_by' => $request->user()->id,
        ]);

        return back()->with('success', 'Loan repayment recorded successfully.');
    }
}
