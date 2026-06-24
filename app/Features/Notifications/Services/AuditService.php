<?php

declare(strict_types=1);

namespace App\Features\Notifications\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Spatie\Activitylog\Contracts\Activity;
use Spatie\Activitylog\Facades\Activity as ActivityFacade;

/**
 * Domain service for Audit.
 */
class AuditService
{
    /**
     * @param  array<string, mixed>  $properties
     */
    public function log(
        string $description,
        ?Model $subject = null,
        ?Model $causer = null,
        array $properties = [],
        ?string $logName = null,
    ): Activity {
        $logger = activity($logName);

        if ($subject) {
            $logger->performedOn($subject);
        }

        if ($causer) {
            $logger->causedBy($causer);
        }

        if ($properties !== []) {
            $logger->withProperties($properties);
        }

        return $logger->log($description);
    }

    /**
     * @param  array<string, mixed>  $old
     * @param  array<string, mixed>  $new
     */
    public function logChanges(
        Model $subject,
        array $old,
        array $new,
        ?Model $causer = null,
        ?string $logName = 'audit',
    ): ?Activity {
        $changes = [];

        foreach ($new as $key => $value) {
            if (array_key_exists($key, $old) && $old[$key] != $value) {
                $changes[$key] = ['old' => $old[$key], 'new' => $value];
            }
        }

        if ($changes === []) {
            return null;
        }

        return $this->log(
            description: 'updated',
            subject: $subject,
            causer: $causer,
            properties: ['changes' => $changes],
            logName: $logName,
        );
    }

    /**
     * Recent.
     */
    public function recent(?string $logName = null, int $limit = 50): Collection
    {
        $query = ActivityFacade::query()->latest();

        if ($logName) {
            $query->inLog($logName);
        }

        return $query->limit($limit)->get();
    }
}
