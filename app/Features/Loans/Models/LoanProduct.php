<?php

namespace App\Features\Loans\Models;

use App\Enums\InterestType;
use App\Models\Concerns\BelongsToGroup;
use App\Models\Concerns\HasSqid;
use Database\Factories\LoanProductFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'group_id', 'name', 'description', 'max_amount', 'max_multiplier',
    'interest_type', 'interest_value', 'repayment_period', 'grace_period', 'status',
])]
/**
 * Eloquent model for loan product.
 */
class LoanProduct extends Model
{
    /** @use HasFactory<LoanProductFactory> */
    use BelongsToGroup, HasFactory, HasSqid;

    protected function casts(): array
    {
        return [
            'max_amount' => 'decimal:2',
            'max_multiplier' => 'decimal:2',
            'interest_type' => InterestType::class,
            'interest_value' => 'decimal:2',
        ];
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
}
