<?php

namespace App\Features\Loans\Models;

use App\Enums\InterestType;
use App\Enums\LoanStatus;
use App\Features\Members\Models\Member;
use App\Models\Concerns\BelongsToGroup;
use App\Models\Concerns\HasSqid;
use App\Models\User;
use Database\Factories\LoanFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'group_id', 'loan_application_id', 'member_id', 'loan_product_id',
    'product_name', 'interest_type', 'interest_value', 'repayment_period',
    'grace_period', 'principal_amount', 'interest_amount', 'total_amount',
    'outstanding_balance', 'disbursement_date', 'due_date', 'status', 'disbursed_by',
])]
/**
 * Eloquent model for loan.
 */
class Loan extends Model
{
    /** @use HasFactory<LoanFactory> */
    use BelongsToGroup, HasFactory, HasSqid;

    protected function casts(): array
    {
        return [
            'interest_type' => InterestType::class,
            'interest_value' => 'decimal:2',
            'principal_amount' => 'decimal:2',
            'interest_amount' => 'decimal:2',
            'total_amount' => 'decimal:2',
            'outstanding_balance' => 'decimal:2',
            'disbursement_date' => 'date',
            'due_date' => 'date',
            'status' => LoanStatus::class,
        ];
    }

    /**
     * Loan application.
     */
    public function loanApplication(): BelongsTo
    {
        return $this->belongsTo(LoanApplication::class);
    }

    /**
     * Member.
     */
    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }

    /**
     * Loan product.
     */
    public function loanProduct(): BelongsTo
    {
        return $this->belongsTo(LoanProduct::class);
    }

    /**
     * Disbursed by.
     */
    public function disbursedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'disbursed_by');
    }

    /**
     * Repayments.
     */
    public function repayments(): HasMany
    {
        return $this->hasMany(LoanRepayment::class);
    }
}
