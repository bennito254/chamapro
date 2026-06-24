<?php

namespace App\Features\Admin\Models;

use App\Models\Concerns\HasSqid;
use Database\Factories\SmsProviderFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'name', 'driver', 'credentials', 'is_default', 'status',
])]
/**
 * Service provider for Sms.
 */
class SmsProvider extends Model
{
    /** @use HasFactory<SmsProviderFactory> */
    use HasFactory, HasSqid;

    protected function casts(): array
    {
        return [
            'credentials' => 'encrypted:array',
            'is_default' => 'boolean',
        ];
    }
}
