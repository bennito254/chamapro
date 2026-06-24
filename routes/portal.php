<?php

use App\Features\Banking\Controllers\BankAccountController;
use App\Features\Banking\Controllers\CashAccountController;
use App\Features\Contributions\Controllers\ContributionChannelController;
use App\Features\Contributions\Controllers\ContributionController;
use App\Features\Contributions\Controllers\ContributionTypeController;
use App\Features\Dividends\Controllers\DividendController;
use App\Features\Expenses\Controllers\ExpenseCategoryController;
use App\Features\Expenses\Controllers\ExpenseController;
use App\Features\Fines\Controllers\FineController;
use App\Features\Fines\Controllers\FineTypeController;
use App\Features\Loans\Controllers\LoanApplicationController;
use App\Features\Loans\Controllers\LoanController;
use App\Features\Loans\Controllers\LoanProductController;
use App\Features\Loans\Controllers\LoanRepaymentController;
use App\Features\Meetings\Controllers\MeetingController;
use App\Features\Members\Controllers\MemberController;
use App\Features\Notifications\Controllers\NotificationController;
use App\Features\Portal\Controllers\PortalDashboardController;
use App\Features\Reports\Controllers\ReportController;
use App\Features\Shares\Controllers\ShareController;
use App\Features\Sms\Controllers\SmsMessageController;
use App\Features\Sms\Controllers\SmsTemplateController;
use App\Features\Subscriptions\Controllers\SubscriptionRenewalController;
use App\Features\Support\Controllers\SupportTicketController;
use App\Features\Welfare\Controllers\WelfareController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified', 'tenant', 'subscription', 'subscription.writable'])->group(function () {
    Route::get('dashboard', [PortalDashboardController::class, 'index'])->name('dashboard');

    Route::resource('members', MemberController::class);
    Route::post('members/import', [MemberController::class, 'import'])->name('members.import');
    Route::get('members/{member}/statement', [MemberController::class, 'statement'])->name('members.statement');

    Route::resource('contribution-types', ContributionTypeController::class)->except(['show']);
    Route::resource('contribution-channels', ContributionChannelController::class)->except(['show']);
    Route::get('contributions/by-date/{date}', [ContributionController::class, 'byDate'])->name('contributions.by-date');
    Route::resource('contributions', ContributionController::class);
    Route::get('contributions-bulk', [ContributionController::class, 'bulk'])->name('contributions.bulk');
    Route::post('contributions-bulk', [ContributionController::class, 'bulkStore'])->name('contributions.bulk.store');

    Route::resource('bank-accounts', BankAccountController::class);
    Route::resource('cash-account', CashAccountController::class)->only(['index', 'show']);
    Route::post('bank-accounts/{bank_account}/transactions', [BankAccountController::class, 'storeTransaction'])->name('bank-accounts.transactions');

    Route::resource('loan-products', LoanProductController::class)->except(['show']);
    Route::resource('loan-applications', LoanApplicationController::class);
    Route::post('loan-applications/{loan_application}/transition', [LoanApplicationController::class, 'transition'])->name('loan-applications.transition');
    Route::resource('loans', LoanController::class)->only(['index', 'show']);
    Route::post('loans/{loan}/repayments', [LoanRepaymentController::class, 'store'])->name('loans.repayments');

    Route::resource('fine-types', FineTypeController::class)->except(['show']);
    Route::resource('fines', FineController::class);
    Route::post('fines/{fine}/pay', [FineController::class, 'pay'])->name('fines.pay');

    Route::get('welfare', [WelfareController::class, 'index'])->name('welfare.index');
    Route::post('welfare/contributions', [WelfareController::class, 'storeContribution'])->name('welfare.contributions');
    Route::post('welfare/disbursements', [WelfareController::class, 'storeDisbursement'])->name('welfare.disbursements');

    Route::get('shares', [ShareController::class, 'index'])->name('shares.index');
    Route::post('shares/purchases', [ShareController::class, 'storePurchase'])->name('shares.purchases');

    Route::resource('meetings', MeetingController::class);
    Route::resource('expense-categories', ExpenseCategoryController::class)->except(['show']);
    Route::resource('expenses', ExpenseController::class);

    Route::get('reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('reports/{type}', [ReportController::class, 'show'])->name('reports.show');
    Route::get('reports/{type}/export', [ReportController::class, 'export'])->name('reports.export');

    Route::get('dividends', [DividendController::class, 'index'])->name('dividends.index');
    Route::post('dividends', [DividendController::class, 'store'])->name('dividends.store');

    Route::get('notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('notifications/{id}/read', [NotificationController::class, 'markRead'])->name('notifications.read');

    Route::resource('sms-templates', SmsTemplateController::class)->except(['show']);
    Route::get('sms-messages', [SmsMessageController::class, 'index'])->name('sms-messages.index');
    Route::get('sms-messages/create', [SmsMessageController::class, 'create'])->name('sms-messages.create');
    Route::post('sms-messages', [SmsMessageController::class, 'store'])->name('sms-messages.store');
    Route::post('sms-messages/preview', [SmsMessageController::class, 'preview'])->name('sms-messages.preview');

    Route::resource('support-tickets', SupportTicketController::class)->only(['index', 'create', 'store', 'show']);

    Route::get('subscription/renew', [SubscriptionRenewalController::class, 'show'])->name('subscription.renew');
});

Route::middleware(['auth', 'verified', 'tenant', 'subscription'])->group(function () {
    Route::post('subscription/renew', [SubscriptionRenewalController::class, 'renew'])->name('subscription.renew.store');
    Route::get('subscription/renew/status/{payment}', [SubscriptionRenewalController::class, 'status'])->name('subscription.renew.status');
});
