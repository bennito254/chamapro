<?php

namespace App\Features\Loans\Controllers;

use App\Features\Loans\Models\LoanProduct;
use App\Features\Loans\Requests\StoreLoanProductRequest;
use App\Features\Loans\Requests\UpdateLoanProductRequest;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

/**
 * HTTP controller for Loan Product.
 */
class LoanProductController extends Controller
{
    /**
     * Index.
     */
    public function index(): Response
    {
        $products = LoanProduct::query()
            ->latest()
            ->paginate(15);

        return Inertia::render('portal/loan-products/index', [
            'products' => $products,
        ]);
    }

    /**
     * Create.
     */
    public function create(): Response
    {
        return Inertia::render('portal/loan-products/create');
    }

    /**
     * Store.
     */
    public function store(StoreLoanProductRequest $request): RedirectResponse
    {
        LoanProduct::create($request->validated());

        return redirect()->route('portal.loan-products.index')
            ->with('success', 'Loan product created successfully.');
    }

    /**
     * Edit.
     */
    public function edit(LoanProduct $loanProduct): Response
    {
        return Inertia::render('portal/loan-products/edit', [
            'product' => $loanProduct,
        ]);
    }

    /**
     * Update.
     */
    public function update(UpdateLoanProductRequest $request, LoanProduct $loanProduct): RedirectResponse
    {
        $loanProduct->update($request->validated());

        return redirect()->route('portal.loan-products.index')
            ->with('success', 'Loan product updated successfully.');
    }

    /**
     * Destroy.
     */
    public function destroy(LoanProduct $loanProduct): RedirectResponse
    {
        $loanProduct->delete();

        return redirect()->route('portal.loan-products.index')
            ->with('success', 'Loan product deleted successfully.');
    }
}
