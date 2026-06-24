<?php

namespace App\Features\Expenses\Models;

use App\Models\Concerns\BelongsToGroup;
use App\Models\Concerns\HasSqid;
use Database\Factories\ExpenseCategoryFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'group_id', 'name', 'status',
])]
/**
 * Eloquent model for expense category.
 */
class ExpenseCategory extends Model
{
    /** @use HasFactory<ExpenseCategoryFactory> */
    use BelongsToGroup, HasFactory, HasSqid;

    /**
     * Expenses.
     */
    public function expenses(): HasMany
    {
        return $this->hasMany(Expense::class);
    }
}
