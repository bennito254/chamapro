<?php

namespace App\Features\Loans\Models;

use App\Features\Members\Models\Member;
use App\Models\Concerns\BelongsToGroup;
use App\Models\Concerns\HasSqid;
use Database\Factories\LoanGuarantorFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'group_id', 'loan_application_id', 'member_id', 'guaranteed_amount',
])]
/**
 * Eloquent model for loan guarantor.
 */
class LoanGuarantor extends Model
{
    /** @use HasFactory<LoanGuarantorFactory> */
    use BelongsToGroup, HasFactory, HasSqid;

    protected function casts(): array
    {
        return [
            'guaranteed_amount' => 'decimal:2',
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
}
