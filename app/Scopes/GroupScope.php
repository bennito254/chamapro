<?php

namespace App\Scopes;

use App\Support\GroupContext;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

/**
 * Eloquent global scope for Group.
 */
class GroupScope implements Scope
{
    /**
     * Apply.
     */
    public function apply(Builder $builder, Model $model): void
    {
        if (! app()->bound(GroupContext::class)) {
            return;
        }

        $groupContext = app(GroupContext::class);

        if ($groupContext->isActive()) {
            $builder->where($model->getTable().'.group_id', $groupContext->id());
        }
    }
}
