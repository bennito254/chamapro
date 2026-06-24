<?php

namespace App\Features\Fines\Models;

use App\Models\Concerns\BelongsToGroup;
use App\Models\Concerns\HasSqid;
use Database\Factories\FineTypeFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'group_id', 'name', 'default_amount', 'status',
])]
/**
 * Eloquent model for fine type.
 */
class FineType extends Model
{
    /** @use HasFactory<FineTypeFactory> */
    use BelongsToGroup, HasFactory, HasSqid;

    protected function casts(): array
    {
        return [
            'default_amount' => 'decimal:2',
        ];
    }

    /**
     * Fines.
     */
    public function fines(): HasMany
    {
        return $this->hasMany(Fine::class);
    }
}
