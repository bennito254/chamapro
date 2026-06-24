<?php

namespace App\Features\Contributions\Models;

use App\Models\Concerns\BelongsToGroup;
use App\Models\Concerns\HasSqid;
use Database\Factories\ContributionChannelFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'group_id', 'name', 'is_system', 'status',
])]
/**
 * Eloquent model for contribution channel.
 */
class ContributionChannel extends Model
{
    /** @use HasFactory<ContributionChannelFactory> */
    use BelongsToGroup, HasFactory, HasSqid;

    protected function casts(): array
    {
        return [
            'is_system' => 'boolean',
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
