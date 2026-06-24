<?php

declare(strict_types=1);

namespace App\Features\Dividends\Services;

use App\Features\Dividends\Models\DividendAllocation;
use App\Features\Dividends\Models\DividendRun;
use App\Features\Ledger\Models\ChartOfAccount;
use App\Features\Members\Models\Member;
use App\Support\GroupContext;
use Illuminate\Support\Facades\DB;

/**
 * Domain service for Dividend.
 */
class DividendService
{
    /**
     * Create a new instance.
     */
    public function __construct(private GroupContext $groupContext) {}

    /**
     * @return array<string, mixed>
     */
    public function defaultFormula(): array
    {
        return [
            'profit' => [
                'add' => ['4200', '4100'],
                'subtract' => ['5000'],
            ],
            'allocation' => 'contributions_proportional',
        ];
    }

    /**
     * Run.
     */
    public function run(int $year, ?float $totalProfit = null, ?int $createdBy = null): DividendRun
    {
        return DB::transaction(function () use ($year, $totalProfit, $createdBy): DividendRun {
            $formula = $this->defaultFormula();
            $profit = $totalProfit ?? $this->calculateProfit($formula);

            $run = DividendRun::create([
                'group_id' => $this->groupContext->id(),
                'year' => $year,
                'total_profit' => $profit,
                'formula' => $formula,
                'status' => 'completed',
                'created_by' => $createdBy,
            ]);

            $this->allocate($run, $profit);

            return $run->load('allocations.member');
        });
    }

    /**
     * @param  array<string, mixed>  $formula
     */
    public function calculateProfit(array $formula): float
    {
        $profit = 0.0;

        foreach ($formula['profit']['add'] ?? [] as $accountCode) {
            $profit += $this->accountBalance($accountCode);
        }

        foreach ($formula['profit']['subtract'] ?? [] as $accountCode) {
            $profit -= $this->accountBalance($accountCode);
        }

        return round(max(0, $profit), 2);
    }

    private function allocate(DividendRun $run, float $profit): void
    {
        $members = Member::query()->withSum('contributions as total_contributions', 'amount')->get();
        $groupTotal = (float) $members->sum('total_contributions');

        foreach ($members as $member) {
            $memberContributions = (float) ($member->total_contributions ?? 0);
            $ownership = $groupTotal > 0 ? ($memberContributions / $groupTotal) * 100 : 0;
            $dividend = $groupTotal > 0 ? round(($memberContributions / $groupTotal) * $profit, 2) : 0;

            DividendAllocation::create([
                'dividend_run_id' => $run->id,
                'member_id' => $member->id,
                'member_contributions' => $memberContributions,
                'ownership_percentage' => round($ownership, 4),
                'dividend_amount' => $dividend,
            ]);
        }
    }

    private function accountBalance(string $code): float
    {
        return (float) ChartOfAccount::query()
            ->where('code', $code)
            ->value('balance');
    }
}
