<?php

namespace App\Http\Controllers\Health;

use App\Support\Health\CheckDetail;
use DateTimeInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Artisan;
use Spatie\Health\ResultStores\ResultStore;
use Spatie\Health\ResultStores\StoredCheckResults\StoredCheckResult;
use Throwable;

class HealthController
{
    public function up(): JsonResponse
    {
        return response()
            ->json(['status' => 'ok'])
            ->header('Cache-Control', 'no-store');
    }

    public function __invoke(): JsonResponse
    {
        try {
            Artisan::call('health:check', ['--no-notification' => true]);
            $results = app(ResultStore::class)->latestResults();
        } catch (Throwable) {
            $results = null;
        }

        $checks = $results?->storedCheckResults
            ->map(fn (StoredCheckResult $result) => [
                'name' => $result->name,
                'status' => $result->status,
                'detail' => CheckDetail::for($result),
            ])
            ->values()
            ->all() ?? [];

        $statuses = array_column($checks, 'status');

        $status = match (true) {
            $results === null,
            in_array('failed', $statuses, true),
            in_array('crashed', $statuses, true) => 'failing',
            in_array('warning', $statuses, true) => 'warning',
            default => 'ok',
        };

        return response()
            ->json([
                'status' => $status,
                'finished_at' => ($results?->finishedAt ?? now())->format(DateTimeInterface::ATOM),
                'checks' => $checks,
            ], $status === 'failing' ? 503 : 200)
            ->header('Cache-Control', 'no-store');
    }
}
