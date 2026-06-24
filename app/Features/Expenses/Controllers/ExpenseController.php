<?php

namespace App\Features\Expenses\Controllers;

use App\Features\Expenses\Models\Expense;
use App\Features\Expenses\Models\ExpenseCategory;
use App\Features\Expenses\Requests\StoreExpenseRequest;
use App\Features\Expenses\Requests\UpdateExpenseRequest;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

/**
 * HTTP controller for Expense.
 */
class ExpenseController extends Controller
{
    /**
     * Index.
     */
    public function index(): Response
    {
        $expenses = Expense::query()
            ->with('expenseCategory')
            ->latest('date')
            ->paginate(15);

        return Inertia::render('portal/expenses/index', [
            'expenses' => $expenses,
        ]);
    }

    /**
     * Create.
     */
    public function create(): Response
    {
        return Inertia::render('portal/expenses/create', [
            'categories' => ExpenseCategory::orderBy('name')->get(),
        ]);
    }

    /**
     * Store.
     */
    public function store(StoreExpenseRequest $request): RedirectResponse
    {
        Expense::create([
            ...$request->validated(),
            'recorded_by' => $request->user()->id,
        ]);

        return redirect()->route('portal.expenses.index')
            ->with('success', 'Expense recorded successfully.');
    }

    /**
     * Show.
     */
    public function show(Expense $expense): Response
    {
        $expense->load(['expenseCategory', 'recordedBy']);

        return Inertia::render('portal/expenses/show', [
            'expense' => $expense,
        ]);
    }

    /**
     * Edit.
     */
    public function edit(Expense $expense): Response
    {
        return Inertia::render('portal/expenses/edit', [
            'expense' => $expense,
            'categories' => ExpenseCategory::orderBy('name')->get(),
        ]);
    }

    /**
     * Update.
     */
    public function update(UpdateExpenseRequest $request, Expense $expense): RedirectResponse
    {
        $expense->update($request->validated());

        return redirect()->route('portal.expenses.show', $expense)
            ->with('success', 'Expense updated successfully.');
    }

    /**
     * Destroy.
     */
    public function destroy(Expense $expense): RedirectResponse
    {
        $expense->delete();

        return redirect()->route('portal.expenses.index')
            ->with('success', 'Expense deleted successfully.');
    }
}
