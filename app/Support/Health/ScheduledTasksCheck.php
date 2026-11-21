<?php

namespace App\Support\Health;

use Spatie\Health\Checks\Check;
use Spatie\Health\Checks\Result;
use Spatie\ScheduleMonitor\Models\MonitoredScheduledTask;

class ScheduledTasksCheck extends Check
{
    public function run(): Result
    {
        $failed = MonitoredScheduledTask::query()
            ->whereNotNull('last_failed_at')
            ->where(function ($query) {
                $query->whereNull('last_finished_at')
                    ->orWhereColumn('last_failed_at', '>', 'last_finished_at');
            })
            ->orderBy('last_failed_at')
            ->pluck('name');

        if ($failed->isEmpty()) {
            return Result::make()->ok()->shortSummary('all ok');
        }

        return Result::make()
            ->failed('Scheduled tasks failed: '.$failed->implode(', '))
            ->shortSummary($failed->count().' failed: '.$failed->implode(', '));
    }
}
