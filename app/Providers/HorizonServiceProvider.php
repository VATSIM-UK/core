<?php

namespace App\Providers;

use App\Libraries\Discord;
use Bugsnag\BugsnagLaravel\Facades\Bugsnag;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Laravel\Horizon\Events\LongWaitDetected;
use Laravel\Horizon\Events\MasterSupervisorOutOfMemory;
use Laravel\Horizon\Horizon;
use Laravel\Horizon\HorizonApplicationServiceProvider;

class HorizonServiceProvider extends HorizonApplicationServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();

        Horizon::routeMailNotificationsTo('privileged-access@vatsim.uk');

        Event::listen(function (MasterSupervisorOutOfMemory $event) {
            $this->reportMasterOutOfMemory($event);
        });

        Event::listen(function (LongWaitDetected $event) {
            $this->reportLongWait($event);
        });
    }

    protected function reportMasterOutOfMemory(MasterSupervisorOutOfMemory $event): void
    {
        if (! Cache::add('horizon:master-out-of-memory', true, now()->addMinutes(10))) {
            return;
        }

        $master = $event->master;
        $memoryMb = (int) ceil($master->memoryUsage());
        $limitMb = (int) config('horizon.memory_limit');

        Bugsnag::notifyError(
            'Horizon master out of memory',
            "Horizon master {$master->name} exceeded its memory limit ({$memoryMb}/{$limitMb}MB).",
            function ($report) use ($master, $memoryMb, $limitMb) {
                $report->setSeverity('error');
                $report->setMetaData([
                    'horizon' => [
                        'master' => $master->name,
                        'memory_mb' => $memoryMb,
                        'memory_limit_mb' => $limitMb,
                    ],
                ]);
            }
        );

        $this->sendCriticalAlert("Horizon master {$master->name} exceeded its memory limit ({$memoryMb}/{$limitMb}MB).");
    }

    protected function reportLongWait(LongWaitDetected $event): void
    {
        $throttleKey = 'horizon:long-wait:'.$event->connection.':'.$event->queue;

        if (! Cache::add($throttleKey, true, now()->addMinutes(10))) {
            return;
        }

        $message = "The \"{$event->queue}\" queue on the \"{$event->connection}\" connection has a wait time of {$event->seconds} seconds.";

        Bugsnag::notifyError(
            'Horizon long queue wait',
            $message,
            function ($report) use ($event) {
                $report->setSeverity('error');
                $report->setMetaData([
                    'horizon' => [
                        'connection' => $event->connection,
                        'queue' => $event->queue,
                        'seconds' => $event->seconds,
                    ],
                ]);
            }
        );

        $this->sendCriticalAlert($message);
    }

    protected function sendCriticalAlert(string $message): void
    {
        $channelId = config('services.discord.critical_alerts_channel_id');

        if (! $channelId) {
            return;
        }

        $roleId = config('services.discord.technology_role_id');

        if ($roleId) {
            $message = "<@&{$roleId}> {$message}";
        }

        try {
            app(Discord::class)->sendMessageToChannel($channelId, [
                'content' => $message,
                'allowed_mentions' => [
                    'parse' => [],
                    'roles' => $roleId ? [$roleId] : [],
                ],
            ]);
        } catch (\Throwable $e) {
            Log::warning('Failed to send Horizon critical Discord alert', [
                'exception' => $e,
            ]);
        }
    }

    /**
     * Register the Horizon gate.
     *
     * This gate determines who can access Horizon in non-local environments.
     *
     * @return void
     */
    protected function gate()
    {
        Gate::define('viewHorizon', function ($user) {
            return $user->can('horizon.access');
        });
    }
}
