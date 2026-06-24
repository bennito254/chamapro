<?php

namespace App\Features\Sms\Models;

use App\Models\Concerns\BelongsToGroup;
use App\Models\Concerns\HasSqid;
use Database\Factories\SmsTemplateFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'group_id', 'name', 'body', 'status',
])]
/**
 * Eloquent model for sms template.
 */
class SmsTemplate extends Model
{
    /** @use HasFactory<SmsTemplateFactory> */
    use BelongsToGroup, HasFactory, HasSqid;

    protected static function newFactory(): SmsTemplateFactory
    {
        return SmsTemplateFactory::new();
    }

    /**
     * Messages.
     */
    public function messages(): HasMany
    {
        return $this->hasMany(SmsMessage::class);
    }
}
