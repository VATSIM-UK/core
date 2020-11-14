<?php

namespace App\Console;

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
        // third-party
        \Bugsnag\BugsnagLaravel\Commands\DeployCommand::class,
        \App\Console\Commands\Deployment\HerokuPostDeploy::class,
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
            ->runInBackground()
            ->withoutOverlapping();

        $schedule->command('teaman:runner', ['-v'])
            ->everyMinute()
            ->runInBackground()
            ->withoutOverlapping();

        $schedule->command('networkdata:download')
            ->cron('*/2 * * * *') // every second minute
            ->runInBackground()
            ->withoutOverlapping();

        $schedule->command('horizon:snapshot')
            ->everyFiveMinutes()
            ->runInBackground()
            ->withoutOverlapping();

        // === By Hour === //

        $schedule->command('members:certupdate')
            ->hourly()
            ->runInBackground();

        $schedule->command('members:certimport')
            ->cron('30 */2 * * *') // every second hour
            ->runInBackground();

        // === By Day ===

        $schedule->command('telescope:prune')->daily();

        $schedule->command('sync:community')
            ->dailyAt('00:01');

        $schedule->command('sync:tg-forum-groups')
            ->dailyAt('04:00');

        $schedule->command('discord:manager')
            ->dailyAt('06:00')
            ->runInBackground();

        $schedule->command('members:certimport', ['--full'])
            ->twiceDaily(2, 14)
            ->runInBackground();
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
