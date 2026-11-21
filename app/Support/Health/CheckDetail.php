<?php

namespace App\Support\Health;

use Carbon\Carbon;
use Spatie\Health\ResultStores\StoredCheckResults\StoredCheckResult;
use Throwable;

final class CheckDetail
{
    private const SCHEDULE_HEARTBEAT_CACHE_KEY = 'health:checks:schedule:latestHeartbeatAt';

    public static function for(StoredCheckResult $result): string
    {
        return match ($result->name) {
            'database', 'database_cts', 'redis' => match ($result->status) {
                'ok' => 'connected',
                'warning' => 'degraded',
                default => 'unreachable',
            },
            'horizon' => strtolower($result->shortSummary) ?: $result->status,
            'disk' => isset($result->meta['disk_space_used_percentage'])
                ? $result->meta['disk_space_used_percentage'].'% used'
                : $result->status,
            'schedule' => self::scheduleDetail(),
            'scheduled_tasks' => $result->shortSummary ?: $result->status,
            default => $result->status,
        };
    }

    private static function scheduleDetail(): string
    {
        try {
            $heartbeat = cache()->store(config('cache.default'))->get(self::SCHEDULE_HEARTBEAT_CACHE_KEY);
        } catch (Throwable) {
            $heartbeat = null;
        }

        if (! $heartbeat) {
            return 'no heartbeat yet';
        }

        $minutes = (int) round(Carbon::createFromTimestamp($heartbeat)->diffInMinutes(now()));

        return $minutes < 1 ? 'last ran just now' : "last ran {$minutes}m ago";
    }
}
