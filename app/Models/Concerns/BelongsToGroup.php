<?php

namespace App\Models\Concerns;

use App\Features\Groups\Models\Group;
use App\Scopes\GroupScope;
use App\Support\GroupContext;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Eloquent model for belongs to group.
 */
trait BelongsToGroup
{
    /**
     * Boot belongs to group.
     */
    public static function bootBelongsToGroup(): void
    {
        static::addGlobalScope(new GroupScope);

        static::creating(function ($model): void {
            if (! $model->group_id && app()->bound(GroupContext::class)) {
                $groupContext = app(GroupContext::class);
                if ($groupContext->isActive()) {
                    $model->group_id = $groupContext->id();
                }
            }
        });
    }

    /**
     * Group.
     */
    public function group(): BelongsTo
    {
        return $this->belongsTo(Group::class);
    }
}
