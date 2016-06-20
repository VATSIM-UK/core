<?php

namespace App\Console;

use App\Console\Commands\TestCommand;
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
        Commands\GenerateEloquentMethodPHPDoc::class,
        Commands\MembersCertImport::class,
        Commands\MembersCertUpdate::class,
        Commands\SysStatisticsDaily::class,
        Commands\SyncRTS::class,
        Commands\SyncCommunity::class,
        Commands\SyncMentors::class,
        Commands\TeamSpeakManager::class,
        Commands\TeamSpeakCleanup::class,
        Commands\TeamSpeakDaemon::class,
        Commands\TeamSpeakMapper::class,
        Commands\SlackManager::class,

        \App\Modules\NetworkData\Console\Commands\DownloadAndParse::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->command("statistics:download")->cron("*/2 * * * *")->withoutOverlapping();

        // Work the queue - the last thing that should be processed!
        $schedule->command("queue:work")->everyMinute()->withoutOverlapping();
        //-- end
    }
}
