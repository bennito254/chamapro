<?php

use App\Features\Admin\Models\SmsProvider;
use App\Features\Admin\Models\SystemSetting;
use App\Features\Auth\Models\SuperAdmin;
use App\Features\Banking\Models\BankAccount;
use App\Features\Banking\Models\BankTransaction;
use App\Features\Banking\Models\CashAccount;
use App\Features\Banking\Models\CashTransaction;
use App\Features\Contributions\Models\Contribution;
use App\Features\Contributions\Models\ContributionChannel;
use App\Features\Contributions\Models\ContributionType;
use App\Features\Dividends\Models\DividendAllocation;
use App\Features\Dividends\Models\DividendRun;
use App\Features\Expenses\Models\Expense;
use App\Features\Expenses\Models\ExpenseCategory;
use App\Features\Fines\Models\Fine;
use App\Features\Fines\Models\FineType;
use App\Features\Groups\Models\Group;
use App\Features\Ledger\Models\ChartOfAccount;
use App\Features\Ledger\Models\JournalEntry;
use App\Features\Ledger\Models\JournalEntryLine;
use App\Features\Loans\Models\Loan;
use App\Features\Loans\Models\LoanApplication;
use App\Features\Loans\Models\LoanGuarantor;
use App\Features\Loans\Models\LoanProduct;
use App\Features\Loans\Models\LoanRepayment;
use App\Features\Meetings\Models\Meeting;
use App\Features\Meetings\Models\MeetingAttendee;
use App\Features\Members\Models\Member;
use App\Features\Mpesa\Models\MpesaCallbackLog;
use App\Features\Mpesa\Models\MpesaTransaction;
use App\Features\Shares\Models\SharePurchase;
use App\Features\Shares\Models\ShareSetting;
use App\Features\Sms\Models\SmsMessage;
use App\Features\Sms\Models\SmsTemplate;
use App\Features\Subscriptions\Models\Subscription;
use App\Features\Subscriptions\Models\SubscriptionPayment;
use App\Features\Subscriptions\Models\SubscriptionPlan;
use App\Features\Support\Models\SupportTicket;
use App\Features\Support\Models\SupportTicketNote;
use App\Features\Welfare\Models\WelfareContribution;
use App\Features\Welfare\Models\WelfareDisbursement;
use App\Models\User;

return [

    /*
    |--------------------------------------------------------------------------
    | Sqids Alphabet
    |--------------------------------------------------------------------------
    |
    | Custom alphabet for encoding. Leave empty to use the Sqids default.
    | Set a unique value in production so IDs are not guessable across apps.
    |
    */

    'alphabet' => env('SQIDS_ALPHABET'),

    /*
    |--------------------------------------------------------------------------
    | Minimum Length
    |--------------------------------------------------------------------------
    |
    | Minimum length of generated Sqids. Higher values produce longer IDs.
    |
    */

    'min_length' => 8,

    /*
    |--------------------------------------------------------------------------
    | Model Type Registry
    |--------------------------------------------------------------------------
    |
    | Each model is assigned a unique type ID used when encoding [typeId, pk]
    | tuples. This prevents collisions across different model classes.
    |
    */

    'models' => [
        User::class => 1,
        Member::class => 2,
        Group::class => 3,
        SuperAdmin::class => 4,
        SystemSetting::class => 5,
        SmsProvider::class => 6,
        DividendAllocation::class => 7,
        DividendRun::class => 8,
        MpesaCallbackLog::class => 9,
        MpesaTransaction::class => 10,
        SmsMessage::class => 11,
        SmsTemplate::class => 41,
        SupportTicketNote::class => 12,
        SupportTicket::class => 13,
        ShareSetting::class => 14,
        Expense::class => 15,
        ExpenseCategory::class => 16,
        MeetingAttendee::class => 17,
        Meeting::class => 18,
        SharePurchase::class => 19,
        WelfareDisbursement::class => 20,
        WelfareContribution::class => 21,
        Fine::class => 22,
        FineType::class => 23,
        LoanRepayment::class => 24,
        LoanGuarantor::class => 25,
        Loan::class => 26,
        LoanApplication::class => 27,
        LoanProduct::class => 28,
        CashTransaction::class => 29,
        CashAccount::class => 30,
        BankTransaction::class => 31,
        BankAccount::class => 32,
        JournalEntryLine::class => 33,
        JournalEntry::class => 34,
        ChartOfAccount::class => 35,
        Contribution::class => 36,
        ContributionChannel::class => 37,
        ContributionType::class => 38,
        SubscriptionPlan::class => 39,
        Subscription::class => 40,
        SubscriptionPayment::class => 42,
    ],

];
