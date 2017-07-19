<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        Commands\Development\GenerateEloquentMethodPHPDoc::class,
        Commands\Members\ImportMembers::class,
        Commands\Members\UpdateMembers::class,
        Commands\Members\MemberStatistics::class,
        Commands\ExternalServices\SyncRTS::class,
        Commands\ExternalServices\SyncCommunity::class,
        Commands\ExternalServices\SyncHelpdesk::class,
        Commands\ExternalServices\SyncMentors::class,
        Commands\ExternalServices\SyncMoodle::class,
        Commands\TeamSpeak\TeamSpeakManager::class,
        Commands\TeamSpeak\TeamSpeakCleanup::class,
        Commands\TeamSpeak\TeamSpeakDaemon::class,
        Commands\TeamSpeak\TeamSpeakMapper::class,
        Commands\ExternalServices\ManageSlack::class,
        Commands\Development\TestEmails::class,
        Commands\NetworkData\ProcessNetworkData::class,
        Commands\NetworkData\NetworkStatistics::class,
        Commands\VisitTransfer\VisitTransferStatistics::class,
        Commands\VisitTransfer\ApplicationsCleanup::class,
        Commands\Feedback\GenerateFeedbackSummary::class,

        /* Third Party */
        \Bugsnag\BugsnagLaravel\Commands\DeployCommand::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        //        $schedule->command("statistics:download")->cron("*/2 * * * *")->withoutOverlapping();

        // Work the queue - the last thing that should be processed!
        $schedule->command('queue:work')->everyMinute()->withoutOverlapping();
        //-- end
    }

    /**
     * Register the Closure based commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        require base_path('routes/console.php');
    }
}
