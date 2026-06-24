<?php

declare(strict_types=1);

namespace App\Features\Banking\Services;

use App\Features\Banking\Models\BankAccount;
use App\Features\Banking\Models\BankTransaction;
use App\Features\Contributions\Models\Contribution;
use App\Features\Ledger\Models\ChartOfAccount;
use App\Support\GroupContext;
use Illuminate\Support\Facades\DB;

/**
 * Domain service for Bank Account.
 */
class BankAccountService
{
    /**
     * Create a new instance.
     */
    public function __construct(private GroupContext $groupContext) {}

    /**
     * Loan fund account.
     */
    public function loanFundAccount(): BankAccount
    {
        $groupId = $this->groupContext->id();
        $bankChart = ChartOfAccount::query()
            ->where('group_id', $groupId)
            ->where('code', '1100')
            ->firstOrFail();

        return BankAccount::query()->firstOrCreate(
            [
                'group_id' => $groupId,
                'chart_of_account_id' => $bankChart->id,
            ],
            [
                'bank_name' => 'Group Bank',
                'account_name' => 'Loan Fund',
                'account_number' => 'LOAN-FUND',
                'opening_balance' => 0,
                'current_balance' => 0,
                'status' => 'active',
            ],
        );
    }

    /**
     * Record contribution deposit.
     */
    public function recordContributionDeposit(Contribution $contribution): BankTransaction
    {
        return DB::transaction(function () use ($contribution): BankTransaction {
            $account = $this->loanFundAccount();

            $transaction = BankTransaction::create([
                'group_id' => $contribution->group_id,
                'bank_account_id' => $account->id,
                'type' => 'receive',
                'amount' => $contribution->amount,
                'date' => $contribution->date,
                'reference' => $contribution->reference,
                'notes' => "Contribution from member #{$contribution->member_id}",
                'recorded_by' => $contribution->recorded_by,
            ]);

            $account->increment('current_balance', (float) $contribution->amount);

            return $transaction;
        });
    }
}
