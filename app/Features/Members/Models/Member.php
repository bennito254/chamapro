<?php

namespace App\Features\Members\Models;

use App\Enums\MemberStatus;
use App\Features\Contributions\Models\Contribution;
use App\Features\Dividends\Models\DividendAllocation;
use App\Features\Fines\Models\Fine;
use App\Features\Loans\Models\Loan;
use App\Features\Loans\Models\LoanApplication;
use App\Features\Loans\Models\LoanGuarantor;
use App\Features\Meetings\Models\MeetingAttendee;
use App\Features\Mpesa\Models\MpesaTransaction;
use App\Features\Shares\Models\SharePurchase;
use App\Features\Welfare\Models\WelfareContribution;
use App\Features\Welfare\Models\WelfareDisbursement;
use App\Models\Concerns\BelongsToGroup;
use App\Models\Concerns\HasSqid;
use Database\Factories\MemberFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'group_id', 'membership_number', 'full_name', 'id_number', 'phone_number',
    'email', 'gender', 'date_joined', 'address', 'occupation', 'next_of_kin',
    'next_of_kin_phone', 'photo', 'status',
])]
/**
 * Eloquent model for member.
 */
class Member extends Model
{
    /** @use HasFactory<MemberFactory> */
    use BelongsToGroup, HasFactory, HasSqid;

    protected static function newFactory(): MemberFactory
    {
        return MemberFactory::new();
    }

    protected function casts(): array
    {
        return [
            'date_joined' => 'date',
            'status' => MemberStatus::class,
        ];
    }

    /**
     * Contributions.
     */
    public function contributions(): HasMany
    {
        return $this->hasMany(Contribution::class);
    }

    /**
     * Loan applications.
     */
    public function loanApplications(): HasMany
    {
        return $this->hasMany(LoanApplication::class);
    }

    /**
     * Loans.
     */
    public function loans(): HasMany
    {
        return $this->hasMany(Loan::class);
    }

    /**
     * Loan guarantors.
     */
    public function loanGuarantors(): HasMany
    {
        return $this->hasMany(LoanGuarantor::class);
    }

    /**
     * Fines.
     */
    public function fines(): HasMany
    {
        return $this->hasMany(Fine::class);
    }

    /**
     * Welfare contributions.
     */
    public function welfareContributions(): HasMany
    {
        return $this->hasMany(WelfareContribution::class);
    }

    /**
     * Welfare disbursements.
     */
    public function welfareDisbursements(): HasMany
    {
        return $this->hasMany(WelfareDisbursement::class);
    }

    /**
     * Share purchases.
     */
    public function sharePurchases(): HasMany
    {
        return $this->hasMany(SharePurchase::class);
    }

    /**
     * Meeting attendees.
     */
    public function meetingAttendees(): HasMany
    {
        return $this->hasMany(MeetingAttendee::class);
    }

    /**
     * Dividend allocations.
     */
    public function dividendAllocations(): HasMany
    {
        return $this->hasMany(DividendAllocation::class);
    }

    /**
     * Mpesa transactions.
     */
    public function mpesaTransactions(): HasMany
    {
        return $this->hasMany(MpesaTransaction::class);
    }
}
