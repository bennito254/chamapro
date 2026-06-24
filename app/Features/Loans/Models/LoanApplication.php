<?php

namespace App\Features\Loans\Models;

use App\Enums\LoanApplicationStatus;
use App\Features\Members\Models\Member;
use App\Models\Concerns\BelongsToGroup;
use App\Models\Concerns\HasSqid;
use App\Models\User;
use Database\Factories\LoanApplicationFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

#[Fillable([
    'group_id', 'member_id', 'loan_product_id', 'requested_amount', 'purpose',
    'status', 'review_notes', 'reviewed_by',
])]
/**
 * Eloquent model for loan application.
 */
class LoanApplication extends Model
{
    /** @use HasFactory<LoanApplicationFactory> */
    use BelongsToGroup, HasFactory, HasSqid;

    protected function casts(): array
    {
        return [
            'requested_amount' => 'decimal:2',
            'status' => LoanApplicationStatus::class,
        ];
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
     * Reviewed by.
     */
    public function reviewedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    /**
     * Guarantors.
     */
    public function guarantors(): HasMany
    {
        return $this->hasMany(LoanGuarantor::class);
    }

    /**
     * Loan.
     */
    public function loan(): HasOne
    {
        return $this->hasOne(Loan::class);
    }
}
