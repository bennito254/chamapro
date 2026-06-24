<?php

namespace App\Features\Loans\Models;

use App\Models\Concerns\BelongsToGroup;
use App\Models\Concerns\HasSqid;
use App\Models\User;
use Database\Factories\LoanRepaymentFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'group_id', 'loan_id', 'amount', 'principal_paid', 'interest_paid',
    'balance_after', 'date', 'method', 'reference_number', 'notes', 'recorded_by',
])]
/**
 * Eloquent model for loan repayment.
 */
class LoanRepayment extends Model
{
    /** @use HasFactory<LoanRepaymentFactory> */
    use BelongsToGroup, HasFactory, HasSqid;

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'principal_paid' => 'decimal:2',
            'interest_paid' => 'decimal:2',
            'balance_after' => 'decimal:2',
            'date' => 'date',
        ];
    }

    /**
     * Loan.
     */
    public function loan(): BelongsTo
    {
        return $this->belongsTo(Loan::class);
    }

    /**
     * Recorded by.
     */
    public function recordedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }
}
