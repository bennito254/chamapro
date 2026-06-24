<?php

namespace App\Features\Shares\Models;

use App\Models\Concerns\BelongsToGroup;
use App\Models\Concerns\HasSqid;
use Database\Factories\ShareSettingFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'group_id', 'share_value',
])]
/**
 * Eloquent model for share setting.
 */
class ShareSetting extends Model
{
    /** @use HasFactory<ShareSettingFactory> */
    use BelongsToGroup, HasFactory, HasSqid;

    protected function casts(): array
    {
        return [
            'share_value' => 'decimal:2',
        ];
    }
}
