<?php

namespace App\Console;

use Bugsnag\BugsnagLaravel\Commands\DeployCommand as BugsnagDeployCommand;
use Carbon\Carbon;
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
        $schedule->command('networkdata:download')
            ->everyTwoMinutes()
            ->withoutOverlapping(5)
            ->graceTimeInMinutes(10);

        $schedule->command('horizon:snapshot')
            ->everyFiveMinutes()
            ->withoutOverlapping()
            ->doNotMonitor();

        $schedule->command('visit-transfer:cleanup')
            ->doNotMonitor()
            ->everyTenMinutes();

        // === By Hour === //
        $schedule->command('sync:cts-roles')
            ->hourlyAt(15)
            ->graceTimeInMinutes(15);

        $schedule->command('roster:gander')
            ->hourlyAt(25)
            ->graceTimeInMinutes(5);

        $schedule->command('roster:check-new-s1-exams')
            ->hourlyAt(30)
            ->graceTimeInMinutes(5);

        // === By Day === //
        $schedule->command('telescope:prune')
            ->dailyAt('03:30')
            ->doNotMonitor();

        $schedule->command('model:prune', ['--model' => MonitoredScheduledTaskLogItem::class])
            ->dailyAt('08:00')
            ->doNotMonitor();

        $schedule->command('waiting-lists:check-eligibility')
            ->dailyAt('08:30')
            ->doNotMonitor();

        $schedule->command('import:division-members')
            ->twiceDaily(2, 14)
            ->graceTimeInMinutes(15);

        $schedule->command('waiting-lists:create-retention-checks')
            ->dailyAt('07:00')
            ->graceTimeInMinutes(15);

        $schedule->command('waiting-lists:check-for-expired-retention-checks')
            ->dailyAt('07:30')
            ->graceTimeInMinutes(15);

        // === By Quarter === //
        $schedule->command('roster:update', [
            Carbon::now()->subMonths(3),
            Carbon::now(),
        ])->quarterly()
            ->doNotMonitor();
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
