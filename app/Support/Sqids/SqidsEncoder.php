<?php

namespace App\Support\Sqids;

use InvalidArgumentException;
use Sqids\Sqids;

/**
 * Application class for sqids encoder.
 */
class SqidsEncoder
{
    private Sqids $sqids;

    /** @var array<class-string, int> */
    private array $models;

    /**
     * Create a new instance.
     */
    public function __construct()
    {
        $alphabet = config('sqids.alphabet');
        $minLength = (int) config('sqids.min_length', 8);

        $this->sqids = new Sqids(
            alphabet: ($alphabet !== null && $alphabet !== '') ? $alphabet : Sqids::DEFAULT_ALPHABET,
            minLength: $minLength,
        );

        $this->models = config('sqids.models', []);
    }

    /**
     * @param  class-string  $modelClass
     */
    public function encodeForModel(string $modelClass, int $id): string
    {
        $typeId = $this->typeIdForModel($modelClass);

        return $this->sqids->encode([$typeId, $id]);
    }

    /**
     * @param  class-string  $modelClass
     */
    public function decodeForModel(string $modelClass, string $sqid): ?int
    {
        $numbers = $this->sqids->decode($sqid);

        if (count($numbers) !== 2) {
            return null;
        }

        [$typeId, $id] = $numbers;

        if ($this->typeIdForModel($modelClass) !== $typeId) {
            return null;
        }

        return $id;
    }

    /**
     * @param  class-string  $modelClass
     */
    private function typeIdForModel(string $modelClass): int
    {
        if (! isset($this->models[$modelClass])) {
            throw new InvalidArgumentException("Model [{$modelClass}] is not registered in config/sqids.php.");
        }

        return $this->models[$modelClass];
    }
}
