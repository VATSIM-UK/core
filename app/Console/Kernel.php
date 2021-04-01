<?php

namespace App\Console;

use App\Console\Commands\Deployment\HerokuPostDeploy;
use Bugsnag\BugsnagLaravel\Commands\DeployCommand;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    private $failureEmail = 'privileged-access@vatsim.uk';

    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        DeployCommand::class,
        HerokuPostDeploy::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // === By Minute === //

        $schedule->command('visit-transfer:cleanup')
            ->everyMinute()
            ->withoutOverlapping();

        $schedule->command('teaman:runner', ['-v'])
            ->everyMinute()
            ->withoutOverlapping();

        $schedule->command('networkdata:download')
            ->cron('*/2 * * * *') // every second minute
            ->graceTimeInMinutes(10)
            ->withoutOverlapping();

        $schedule->command('horizon:snapshot')
            ->everyFiveMinutes()
            ->withoutOverlapping();

        // === By Hour === //

        $schedule->command('members:certupdate')
            ->hourly()
            ->graceTimeInMinutes(15);

        $schedule->command('members:certimport')
            ->cron('30 */2 * * *') // every second hour
            ->graceTimeInMinutes(15);

        // === By Day === //

        $schedule->command('telescope:prune')
            ->daily();

        $schedule->command('sync:community')
            ->dailyAt('00:01')
            ->graceTimeInMinutes(30);

        $schedule->command('DivMembers:CertUpdate')
            ->dailyAt('05:00');

        $schedule->command('schedule-monitor:sync')
            ->dailyAt('07:00');

        $schedule->command('schedule-monitor:clean')
            ->dailyAt('08:00');

        $schedule->command('members:certimport', ['--full'])
            ->twiceDaily(2, 14)
            ->graceTimeInMinutes(30);
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
}
