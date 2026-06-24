<?php

namespace App\Features\Groups\Services;

use App\Enums\AccountType;
use App\Enums\AmountType;
use App\Enums\ContributionFrequency;
use App\Features\Banking\Models\BankAccount;
use App\Features\Banking\Models\CashAccount;
use App\Features\Contributions\Models\ContributionChannel;
use App\Features\Contributions\Models\ContributionType;
use App\Features\Expenses\Models\ExpenseCategory;
use App\Features\Groups\Models\Group;
use App\Features\Ledger\Models\ChartOfAccount;
use App\Features\Shares\Models\ShareSetting;
use App\Features\Sms\Models\SmsTemplate;
use App\Features\Subscriptions\Models\SubscriptionPlan;
use App\Features\Subscriptions\Services\SubscriptionService;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Support\Facades\DB;

/**
 * Domain service for Group Provisioning.
 */
class GroupProvisioningService
{
    /**
     * Create a new instance.
     */
    public function __construct(
        private SubscriptionService $subscriptionService,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function provision(array $data, SubscriptionPlan $plan): Group
    {
        return DB::transaction(function () use ($data, $plan) {
            $group = Group::create($data);

            $this->provisionExisting($group, $plan);

            return $group;
        });
    }

    /**
     * Provision existing.
     */
    public function provisionExisting(Group $group, SubscriptionPlan $plan): void
    {
        DB::transaction(function () use ($group, $plan): void {
            $this->seedChartOfAccounts($group);
            $this->seedContributionTypes($group);
            $this->seedContributionChannels($group);
            $this->seedExpenseCategories($group);
            $this->seedCashAccount($group);
            $this->seedBankAccount($group);
            $this->seedShareSettings($group);
            $this->seedSmsTemplates($group);
            $this->subscriptionService->createTrial($group, $plan);

            app(RolesAndPermissionsSeeder::class)->seedForGroup($group);
        });
    }

    private function seedChartOfAccounts(Group $group): void
    {
        $accounts = [
            ['code' => '1000', 'name' => 'Cash', 'type' => AccountType::Asset],
            ['code' => '1100', 'name' => 'Bank', 'type' => AccountType::Asset],
            ['code' => '1200', 'name' => 'Loan Receivable', 'type' => AccountType::Asset],
            ['code' => '4000', 'name' => 'Contribution Income', 'type' => AccountType::Income],
            ['code' => '4100', 'name' => 'Fine Income', 'type' => AccountType::Income],
            ['code' => '4200', 'name' => 'Interest Income', 'type' => AccountType::Income],
            ['code' => '5000', 'name' => 'General Expense', 'type' => AccountType::Expense],
            ['code' => '3000', 'name' => 'Welfare Fund', 'type' => AccountType::Liability],
            ['code' => '3100', 'name' => 'Share Capital', 'type' => AccountType::Equity],
        ];

        foreach ($accounts as $account) {
            ChartOfAccount::create([
                'group_id' => $group->id,
                'code' => $account['code'],
                'name' => $account['name'],
                'type' => $account['type'],
                'is_system' => true,
            ]);
        }
    }

    private function seedContributionTypes(Group $group): void
    {
        $types = [
            ['name' => 'Monthly Contribution', 'default_amount' => 1000, 'frequency' => ContributionFrequency::Monthly, 'save_to_bank' => true],
            ['name' => 'Welfare Fund', 'default_amount' => 200, 'frequency' => ContributionFrequency::Monthly, 'save_to_bank' => false],
            ['name' => 'Registration Fee', 'default_amount' => 500, 'frequency' => ContributionFrequency::OneTime, 'save_to_bank' => true],
        ];

        foreach ($types as $type) {
            ContributionType::create([
                'group_id' => $group->id,
                'name' => $type['name'],
                'default_amount' => $type['default_amount'],
                'amount_type' => AmountType::Fixed,
                'frequency' => $type['frequency'],
                'save_to_bank' => $type['save_to_bank'],
            ]);
        }
    }

    private function seedContributionChannels(Group $group): void
    {
        $channels = ['Cash', 'M-Pesa Deposit', 'M-Pesa STK Push', 'Bank Deposit', 'Bank Transfer', 'Cheque', 'Internal Transfer'];

        foreach ($channels as $name) {
            ContributionChannel::create([
                'group_id' => $group->id,
                'name' => $name,
                'is_system' => true,
            ]);
        }
    }

    private function seedExpenseCategories(Group $group): void
    {
        foreach (['Rent', 'Stationery', 'Transport', 'Welfare', 'Utilities', 'Other'] as $name) {
            ExpenseCategory::create(['group_id' => $group->id, 'name' => $name]);
        }
    }

    private function seedCashAccount(Group $group): void
    {
        $cashAccount = ChartOfAccount::where('group_id', $group->id)->where('code', '1000')->first();

        CashAccount::create([
            'group_id' => $group->id,
            'chart_of_account_id' => $cashAccount?->id,
        ]);
    }

    private function seedBankAccount(Group $group): void
    {
        $bankChart = ChartOfAccount::where('group_id', $group->id)->where('code', '1100')->first();

        BankAccount::create([
            'group_id' => $group->id,
            'bank_name' => 'Group Bank',
            'account_name' => 'Loan Fund',
            'account_number' => 'LOAN-FUND',
            'opening_balance' => 0,
            'current_balance' => 0,
            'chart_of_account_id' => $bankChart?->id,
            'status' => 'active',
        ]);
    }

    private function seedShareSettings(Group $group): void
    {
        ShareSetting::create([
            'group_id' => $group->id,
            'share_value' => 100,
        ]);
    }

    private function seedSmsTemplates(Group $group): void
    {
        $templates = [
            [
                'name' => 'Contribution Reminder',
                'body' => 'Dear {name}, you missed {contributions_missed} contribution(s) totaling KES {contributions_due}. Please pay at the next meeting. - {group_name}',
            ],
            [
                'name' => 'Loan Balance Notice',
                'body' => 'Dear {name}, your loan balance is KES {loan_balance} (principal KES {principal_balance}, interest KES {interest_balance}). - {group_name}',
            ],
            [
                'name' => 'Fine Reminder',
                'body' => 'Dear {name}, you have unpaid fines of KES {unpaid_fines}. Please settle soon. - {group_name}',
            ],
        ];

        foreach ($templates as $template) {
            SmsTemplate::create([
                'group_id' => $group->id,
                'name' => $template['name'],
                'body' => $template['body'],
                'status' => 'active',
            ]);
        }
    }
}
