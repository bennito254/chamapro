<?php

namespace App\Features\Expenses\Models;

use App\Models\Concerns\BelongsToGroup;
use App\Models\Concerns\HasSqid;
use App\Models\User;
use Database\Factories\ExpenseFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'group_id', 'expense_category_id', 'amount', 'date', 'payee', 'notes', 'recorded_by',
])]
/**
 * Eloquent model for expense.
 */
class Expense extends Model
{
    /** @use HasFactory<ExpenseFactory> */
    use BelongsToGroup, HasFactory, HasSqid;

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'date' => 'date',
        ];
    }

    /**
     * Expense category.
     */
    public function expenseCategory(): BelongsTo
    {
        return $this->belongsTo(ExpenseCategory::class);
    }

    /**
     * Recorded by.
     */
    public function recordedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }
}
