<?php

namespace App\Features\Contributions\Models;

use App\Enums\AmountType;
use App\Enums\ContributionFrequency;
use App\Models\Concerns\BelongsToGroup;
use App\Models\Concerns\HasSqid;
use Database\Factories\ContributionTypeFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'group_id', 'name', 'description', 'default_amount', 'amount_type',
    'frequency', 'save_to_bank', 'status',
])]
/**
 * Eloquent model for contribution type.
 */
class ContributionType extends Model
{
    /** @use HasFactory<ContributionTypeFactory> */
    use BelongsToGroup, HasFactory, HasSqid;

    protected function casts(): array
    {
        return [
            'default_amount' => 'decimal:2',
            'amount_type' => AmountType::class,
            'frequency' => ContributionFrequency::class,
            'save_to_bank' => 'boolean',
        ];
    }

    /**
     * Contributions.
     */
    public function contributions(): HasMany
    {
        return $this->hasMany(Contribution::class);
    }
}
