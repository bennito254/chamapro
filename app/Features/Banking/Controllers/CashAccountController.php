<?php

namespace App\Features\Banking\Controllers;

use App\Features\Banking\Models\CashAccount;
use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Inertia\Response;

/**
 * HTTP controller for Cash Account.
 */
class CashAccountController extends Controller
{
    /**
     * Index.
     */
    public function index(): Response
    {
        $accounts = CashAccount::query()
            ->with('chartOfAccount')
            ->paginate(15);

        return Inertia::render('portal/cash-account/index', [
            'accounts' => $accounts,
        ]);
    }

    /**
     * Show.
     */
    public function show(CashAccount $cashAccount): Response
    {
        $cashAccount->load(['transactions' => fn ($q) => $q->latest('date')->limit(20)]);

        return Inertia::render('portal/cash-account/show', [
            'account' => $cashAccount,
        ]);
    }
}
