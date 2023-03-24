<?php

namespace App\Console;

use App\Console\Commands\Deployment\HerokuPostDeploy;
use Bugsnag\BugsnagLaravel\Commands\DeployCommand as BugsnagDeployCommand;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Spatie\ScheduleMonitor\Models\MonitoredScheduledTaskLogItem;

class Kernel extends ConsoleKernel
{
    private $failureEmail = 'privileged-access@vatsim.uk';

    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        HerokuPostDeploy::class,
        BugsnagDeployCommand::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // === By Minute === //

        $schedule->command('teaman:runner', ['-v'])
            ->everyMinute()
            ->withoutOverlapping();

        $schedule->command('networkdata:download')
            ->everyTwoMinutes()
            ->withoutOverlapping(5)
            ->graceTimeInMinutes(10);

        $schedule->command('horizon:snapshot')
            ->everyFiveMinutes()
            ->withoutOverlapping()
            ->doNotMonitor();

        $schedule->command('visit-transfer:cleanup')
            ->everyTenMinutes();

        // === By Hour === //

        $schedule->command('members:certupdate')
            ->hourlyAt(10)
            ->graceTimeInMinutes(15);

        $schedule->command('sync:cts-roles')
            ->hourlyAt(15)
            ->graceTimeInMinutes(15);

        $schedule->command('waitinglists:processpendingremovals')
            ->hourlyAt(30)
            ->graceTimeInMinutes(15);

        // === By Day === //

        $schedule->command('telescope:prune')
            ->dailyAt('03:30')
            ->doNotMonitor();

        $schedule->command('sync:tg-forum-groups')
            ->dailyAt('04:30');

        $schedule->command('model:prune', ['--model' => MonitoredScheduledTaskLogItem::class])
            ->dailyAt('08:00');

        $schedule->command('waitinglists:checkmembersmeetactivityrules')
            ->twiceDaily(2, 14);
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');
    }

    protected function bootstrappers()
    {
        return array_merge(
            [\Bugsnag\BugsnagLaravel\OomBootstrapper::class],
            parent::bootstrappers(),
        );
    }
}
