<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        // production
        Commands\ExternalServices\ManageSlack::class,
        Commands\ExternalServices\SyncCommunity::class,
        Commands\ExternalServices\SyncHelpdesk::class,
        Commands\ExternalServices\SyncMentors::class,
        Commands\ExternalServices\SyncMoodle::class,
        Commands\ExternalServices\SyncRTS::class,
        Commands\Feedback\GenerateFeedbackSummary::class,
        Commands\Members\ImportMembers::class,
        Commands\Members\MemberStatistics::class,
        Commands\Members\UpdateMembers::class,
        Commands\NetworkData\NetworkStatistics::class,
        Commands\NetworkData\ProcessNetworkData::class,
        Commands\TeamSpeak\TeamSpeakCleanup::class,
        Commands\TeamSpeak\TeamSpeakDaemon::class,
        Commands\TeamSpeak\TeamSpeakManager::class,
        Commands\TeamSpeak\TeamSpeakMapper::class,
        Commands\VisitTransfer\ApplicationsCleanup::class,
        Commands\VisitTransfer\VisitTransferStatistics::class,

        // development
        Commands\Development\GenerateEloquentMethodPHPDoc::class,
        Commands\Development\TestEmails::class,

        // third-party
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
        //
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
