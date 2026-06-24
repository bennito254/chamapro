<?php

namespace App\Providers;

use App\Features\Contributions\Models\Contribution;
use App\Features\Loans\Models\LoanApplication;
use App\Features\Members\Models\Member;
use App\Features\Sms\Models\SmsMessage;
use App\Features\Sms\Models\SmsTemplate;
use App\Policies\ContributionPolicy;
use App\Policies\LoanApplicationPolicy;
use App\Policies\MemberPolicy;
use App\Policies\SmsMessagePolicy;
use App\Policies\SmsTemplatePolicy;
use App\Support\GroupContext;
use App\Support\Sqids\SqidsEncoder;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

/**
 * Service provider for App Service.
 */
class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(GroupContext::class);
        $this->app->singleton(SqidsEncoder::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::policy(Member::class, MemberPolicy::class);
        Gate::policy(Contribution::class, ContributionPolicy::class);
        Gate::policy(LoanApplication::class, LoanApplicationPolicy::class);
        Gate::policy(SmsTemplate::class, SmsTemplatePolicy::class);
        Gate::policy(SmsMessage::class, SmsMessagePolicy::class);

        $this->configureDefaults();
    }

    /**
     * Configure default behaviors for production-ready applications.
     */
    protected function configureDefaults(): void
    {
        Date::use(CarbonImmutable::class);

        DB::prohibitDestructiveCommands(
            app()->isProduction(),
        );

        Password::defaults(fn (): ?Password => app()->isProduction()
            ? Password::min(12)
                ->mixedCase()
                ->letters()
                ->numbers()
                ->symbols()
                ->uncompromised()
            : null,
        );
    }
}
