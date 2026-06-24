<?php

namespace App\Models\Concerns;

use App\Support\Sqids\SqidsEncoder;
use Illuminate\Database\Eloquent\Model;

/**
 * Eloquent model for has sqid.
 */
trait HasSqid
{
    /**
     * Initialize has sqid.
     */
    public function initializeHasSqid(): void
    {
        $this->appends = array_values(array_unique(array_merge($this->appends, ['sqid'])));
    }

    /**
     * Get sqid attribute.
     */
    public function getSqidAttribute(): string
    {
        $id = $this->attributes['id'] ?? null;

        if ($id === null) {
            return '';
        }

        return app(SqidsEncoder::class)->encodeForModel(static::class, (int) $id);
    }

    /**
     * Get route key name.
     */
    public function getRouteKeyName(): string
    {
        return 'sqid';
    }

    /**
     * Resolve route binding.
     *
     * @param  mixed  $value
     * @param  mixed  $field
     */
    public function resolveRouteBinding($value, $field = null): ?Model
    {
        if ($field !== null) {
            return parent::resolveRouteBinding($value, $field);
        }

        $id = app(SqidsEncoder::class)->decodeForModel(static::class, (string) $value);

        if ($id === null) {
            return null;
        }

        return $this->resolveRouteBindingQuery($this, $id, 'id')->first();
    }

    /**
     * @param  array<string, mixed>  $attributes
     * @return array<string, mixed>
     */
    protected function appendAttributesToArray(array $attributes): array
    {
        $attributes = parent::appendAttributesToArray($attributes);

        if (array_key_exists('id', $this->attributes) && ! array_key_exists('sqid', $attributes)) {
            $attributes['sqid'] = $this->sqid;
        }

        return $attributes;
    }
}
