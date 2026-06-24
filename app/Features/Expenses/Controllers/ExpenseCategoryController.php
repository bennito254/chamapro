<?php

namespace App\Features\Expenses\Controllers;

use App\Features\Expenses\Models\ExpenseCategory;
use App\Features\Expenses\Requests\StoreExpenseCategoryRequest;
use App\Features\Expenses\Requests\UpdateExpenseCategoryRequest;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

/**
 * HTTP controller for Expense Category.
 */
class ExpenseCategoryController extends Controller
{
    /**
     * Index.
     */
    public function index(): Response
    {
        $categories = ExpenseCategory::query()
            ->latest()
            ->paginate(15);

        return Inertia::render('portal/expense-categories/index', [
            'categories' => $categories,
        ]);
    }

    /**
     * Create.
     */
    public function create(): Response
    {
        return Inertia::render('portal/expense-categories/create');
    }

    /**
     * Store.
     */
    public function store(StoreExpenseCategoryRequest $request): RedirectResponse
    {
        ExpenseCategory::create($request->validated());

        return redirect()->route('portal.expense-categories.index')
            ->with('success', 'Expense category created successfully.');
    }

    /**
     * Edit.
     */
    public function edit(ExpenseCategory $expenseCategory): Response
    {
        return Inertia::render('portal/expense-categories/edit', [
            'category' => $expenseCategory,
        ]);
    }

    /**
     * Update.
     */
    public function update(UpdateExpenseCategoryRequest $request, ExpenseCategory $expenseCategory): RedirectResponse
    {
        $expenseCategory->update($request->validated());

        return redirect()->route('portal.expense-categories.index')
            ->with('success', 'Expense category updated successfully.');
    }

    /**
     * Destroy.
     */
    public function destroy(ExpenseCategory $expenseCategory): RedirectResponse
    {
        $expenseCategory->delete();

        return redirect()->route('portal.expense-categories.index')
            ->with('success', 'Expense category deleted successfully.');
    }
}
