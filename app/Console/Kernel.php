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
        $schedule->command('telescope:prune')->daily();

        // === By Minute === //

        $schedule->command('visit-transfer:cleanup')
            ->everyMinute()
            ->runInBackground()
            ->withoutOverlapping();

        $schedule->command('visittransfer:statistics:daily')
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

        // === By Hour === //

        $schedule->command('members:certimport')
            ->cron('30 */2 * * *') // every second hour
            ->runInBackground();

        $schedule->command('slack:manager')
            ->hourly()
            ->runInBackground();

        // === By Day ===

        $schedule->command('sys:statistics:daily')
            ->dailyAt('00:01');

        $schedule->command('sync:community')
            ->dailyAt('00:01');

        $schedule->command('members:certupdate', ['--type=daily', 5000])
            ->dailyAt('00:45')
            ->runInBackground();

        $schedule->command('sync:tg-forum-groups')
            ->dailyAt('04:00');

        $schedule->command('members:certimport', ['--full'])
            ->twiceDaily(2, 14)
            ->runInBackground();

        // === By Week === //

        $schedule->command('members:certupdate', ['--type=weekly', 5000])
            ->weeklyOn(1, '01:15')
            ->runInBackground();

        // === By Month === //
        $schedule->command('members:certupdate', ['--type=monthly', 5000])
            ->monthlyOn(1, '01:45')
            ->runInBackground();

        $schedule->command('members:certupdate', ['--type=all', 1000])
            ->monthlyOn(1, '01:45')
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
