<?php

namespace App\Features\Mpesa\Models;

use App\Models\Concerns\BelongsToGroup;
use App\Models\Concerns\HasSqid;
use Database\Factories\MpesaCallbackLogFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'group_id', 'transaction_id', 'payload', 'processed',
])]
/**
 * Eloquent model for mpesa callback log.
 */
class MpesaCallbackLog extends Model
{
    /** @use HasFactory<MpesaCallbackLogFactory> */
    use BelongsToGroup, HasFactory, HasSqid;

    protected function casts(): array
    {
        return [
            'payload' => 'array',
            'processed' => 'boolean',
        ];
    }
}
