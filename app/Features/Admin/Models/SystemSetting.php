<?php

namespace App\Features\Admin\Models;

use App\Models\Concerns\HasSqid;
use Database\Factories\SystemSettingFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'key', 'value',
])]
/**
 * Eloquent model for system setting.
 */
class SystemSetting extends Model
{
    /** @use HasFactory<SystemSettingFactory> */
    use HasFactory, HasSqid;
}
