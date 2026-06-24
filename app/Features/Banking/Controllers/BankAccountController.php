<?php

namespace App\Features\Banking\Controllers;

use App\Features\Banking\Models\BankAccount;
use App\Features\Banking\Models\BankTransaction;
use App\Features\Banking\Requests\StoreBankAccountRequest;
use App\Features\Banking\Requests\StoreBankTransactionRequest;
use App\Features\Banking\Requests\UpdateBankAccountRequest;
use App\Features\Ledger\Models\ChartOfAccount;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

/**
 * HTTP controller for Bank Account.
 */
class BankAccountController extends Controller
{
    /**
     * Index.
     */
    public function index(): Response
    {
        $accounts = BankAccount::query()
            ->withCount('transactions')
            ->latest()
            ->paginate(15);

        return Inertia::render('portal/bank-accounts/index', [
            'accounts' => $accounts,
        ]);
    }

    /**
     * Create.
     */
    public function create(): Response
    {
        return Inertia::render('portal/bank-accounts/create');
    }

    /**
     * Store.
     */
    public function store(StoreBankAccountRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $openingBalance = $data['opening_balance'] ?? 0;
        $bankChart = ChartOfAccount::query()
            ->where('code', '1100')
            ->first();

        BankAccount::create([
            ...$data,
            'current_balance' => $openingBalance,
            'chart_of_account_id' => $bankChart?->id,
        ]);

        return redirect()->route('portal.bank-accounts.index')
            ->with('success', 'Bank account created successfully.');
    }

    /**
     * Show.
     */
    public function show(BankAccount $bankAccount): Response
    {
        $bankAccount->load(['transactions' => fn ($q) => $q->latest('date')->limit(20)]);

        return Inertia::render('portal/bank-accounts/show', [
            'account' => $bankAccount,
        ]);
    }

    /**
     * Edit.
     */
    public function edit(BankAccount $bankAccount): Response
    {
        return Inertia::render('portal/bank-accounts/edit', [
            'account' => $bankAccount,
        ]);
    }

    /**
     * Update.
     */
    public function update(UpdateBankAccountRequest $request, BankAccount $bankAccount): RedirectResponse
    {
        $bankAccount->update($request->validated());

        return redirect()->route('portal.bank-accounts.show', $bankAccount)
            ->with('success', 'Bank account updated successfully.');
    }

    /**
     * Destroy.
     */
    public function destroy(BankAccount $bankAccount): RedirectResponse
    {
        $bankAccount->delete();

        return redirect()->route('portal.bank-accounts.index')
            ->with('success', 'Bank account deleted successfully.');
    }

    /**
     * Store transaction.
     */
    public function storeTransaction(StoreBankTransactionRequest $request, BankAccount $bankAccount): RedirectResponse
    {
        $data = $request->validated();

        BankTransaction::create([
            ...$data,
            'bank_account_id' => $bankAccount->id,
            'recorded_by' => $request->user()->id,
        ]);

        $amount = (float) $data['amount'];

        match ($data['type']) {
            'receive' => $bankAccount->increment('current_balance', $amount),
            'pay' => $bankAccount->decrement('current_balance', $amount),
            'transfer' => $bankAccount->decrement('current_balance', $amount),
            default => null,
        };

        return back()->with('success', 'Transaction recorded successfully.');
    }
}
