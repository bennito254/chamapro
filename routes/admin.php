<?php

use App\Features\Admin\Controllers\AdminDashboardController;
use App\Features\Admin\Controllers\GroupController as AdminGroupController;
use App\Features\Admin\Controllers\ImpersonationController;
use App\Features\Admin\Controllers\MpesaSettingsController;
use App\Features\Admin\Controllers\OwnerSmsController;
use App\Features\Admin\Controllers\SmsProviderController;
use App\Features\Admin\Controllers\SubscriptionController as AdminSubscriptionController;
use App\Features\Admin\Controllers\SubscriptionLogController;
use App\Features\Admin\Controllers\SubscriptionPaymentController;
use App\Features\Admin\Controllers\SubscriptionPlanController;
use App\Features\Admin\Controllers\SupportTicketController as AdminSupportTicketController;
use App\Features\Admin\Controllers\SystemSettingController;
use App\Features\Auth\Controllers\SuperAdminAuthController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest:super_admin')->group(function () {
    Route::get('login', [SuperAdminAuthController::class, 'showLogin'])->name('login');
    Route::post('login', [SuperAdminAuthController::class, 'login']);
});

Route::middleware('auth:super_admin')->group(function () {
    Route::post('logout', [SuperAdminAuthController::class, 'logout'])->name('logout');
    Route::get('dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
    Route::put('mpesa-settings', [MpesaSettingsController::class, 'update'])->name('mpesa-settings.update');

    Route::resource('groups', AdminGroupController::class);
    Route::post('groups/{group}/suspend', [AdminGroupController::class, 'suspend'])->name('groups.suspend');
    Route::post('groups/{group}/activate', [AdminGroupController::class, 'activate'])->name('groups.activate');
    Route::post('groups/{group}/extend-subscription', [AdminGroupController::class, 'extendSubscription'])->name('groups.extend-subscription');

    Route::resource('plans', SubscriptionPlanController::class)->except(['show']);
    Route::resource('subscriptions', AdminSubscriptionController::class)->only(['index', 'edit', 'update']);
    Route::get('subscription-payments', [SubscriptionPaymentController::class, 'index'])->name('subscription-payments.index');
    Route::get('subscription-logs', [SubscriptionLogController::class, 'index'])->name('subscription-logs.index');

    Route::get('owner-sms', [OwnerSmsController::class, 'create'])->name('owner-sms.create');
    Route::post('owner-sms', [OwnerSmsController::class, 'store'])->name('owner-sms.store');

    Route::resource('sms-providers', SmsProviderController::class)->except(['show']);
    Route::get('system-settings', [SystemSettingController::class, 'index'])->name('system-settings.index');
    Route::put('system-settings', [SystemSettingController::class, 'update'])->name('system-settings.update');

    Route::resource('support-tickets', AdminSupportTicketController::class)->only(['index', 'show', 'update']);
    Route::post('support-tickets/{support_ticket}/notes', [AdminSupportTicketController::class, 'addNote'])->name('support-tickets.notes');

    Route::post('impersonate/{group}', [ImpersonationController::class, 'loginAs'])->name('impersonate');
});
