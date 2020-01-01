<?php

namespace App\Console;

use App\Libraries\Slack;
use Closure;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    private $failureEmail = 'privileged-access@vatsim-uk.co.uk';

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
        // $schedule->command('sync:tg-forum-groups')->dailyAt('04:00');

        $schedule->command('sys:statistics:daily')
            ->dailyAt('00:01')
            ->onFailure($this->failureMessage('Syncing of daily statistics has failed (sync:statistics:daily)'))
            ->emailOutputOnFailure($this->failureEmail);

        $schedule->command('sync:helpdesk')
            ->dailyAt('23:30')
            ->onFailure($this->failureMessage('Syncing of core to helpdesk has failed (sync:helpdesk)'))
            ->emailOutputOnFailure($this->failureEmail);

        $schedule->command('sync:community')
            ->dailyAt('00:01')
            ->onFailure($this->failureMessage('Syncing of community has failed (sync:community)'))
            ->emailOutputOnFailure($this->failureEmail);

        $schedule->command('members:certimport', ['--full'])
            ->twiceDaily(2, 14)
            ->runInBackground()
            ->onFailure($this->failureMessage('Full import of cert has failed (members:certimport --full)'))
            ->emailOutputOnFailure($this->failureEmail);

        $schedule->command('members:certimport')
            ->cron('30 */2 * * *') // every second hour
            ->runInBackground()
            ->onFailure($this->failureMessage('Two-hourly import of cert has failed (members:certimport)'))
            ->emailOutputOnFailure($this->failureEmail);

        $schedule->command('members:certupdate', ['--type=daily', 5000])
            ->dailyAt('00:45')
            ->runInBackground()
            ->onFailure($this->failureMessage('Daily cert import has failed (members:certupdate --type=daily 5000)'))
            ->emailOutputOnFailure($this->failureEmail);

        $schedule->command('members:certupdate', ['--type=weekly', 5000])
            ->weeklyOn(1, '01:15')
            ->runInBackground()
            ->onFailure($this->failureMessage('Weekly monday cert import has failed (members:certupdate --type=weekly 5000)'))
            ->emailOutputOnFailure($this->failureEmail);

        $schedule->command('members:certupdate', ['--type=monthly', 5000])
            ->monthlyOn(1, '01:45')
            ->runInBackground()
            ->onFailure($this->failureMessage('Weekly monday cert import has failed (members:certupdate --type=weekly 5000)'))
            ->emailOutputOnFailure($this->failureEmail);

        $schedule->command('members:certupdate', ['--type=all', 1000])
            ->monthlyOn(1, '01:45')
            ->runInBackground()
            ->onFailure($this->failureMessage('Weekly monday cert import has failed (members:certupdate --type=weekly 5000)'))
            ->emailOutputOnFailure($this->failureEmail);

        $schedule->command('visit-transfer:cleanup')
            ->everyMinute()
            ->runInBackground()
            ->onFailure($this->failureMessage('Cleaning up visit transfer applications has failed (visit-transfer:cleanup)'))
            ->emailOutputOnFailure($this->failureEmail);

        $schedule->command('visittransfer:statistics:daily')
            ->everyMinute()
            ->runInBackground()
            ->onFailure($this->failureMessage('Daily statistics for visit-transfer has failed (visittransfer:statistics:daily)'))
            ->emailOutputOnFailure($this->failureEmail);

        $schedule->command('teaman:runner', ['-v'])
            ->everyMinute()
            ->runInBackground()
            ->onFailure($this->failureMessage('TeamSpeak runner has failed (teaman:runner)'))
            ->emailOutputOnFailure($this->failureEmail);

        $schedule->command('networkdata:download')
            ->cron('*/2 * * * *') // every second minute
            ->runInBackground()
            ->onFailure($this->failureMessage('Download of network data has failed (networkdata:download'))
            ->emailOutputOnFailure($this->failureEmail);

        $schedule->command('slack:manager')
            ->hourly()
            ->runInBackground()
            ->onFailure($this->failureMessage('Slack manager has failed (slack:manager)'))
            ->emailOutputOnFailure($this->failureEmail);
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

    private function failureMessage(string $message): Closure
    {
        return function () use (&$message) {
            Slack::sendToWebServices("Scheduled Task Failure - @channel - {$message}");
        };
    }
}
