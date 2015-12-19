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
        Commands\TestCommand::class,
        Commands\MembersCertImport::class,
        Commands\MembersCertUpdate::class,
        Commands\StatisticsDaily::class,
        Commands\SyncRTS::class,
        Commands\SyncCommunity::class,
        Commands\TeamspeakManager::class,
        Commands\TeamspeakCleanup::class,

        \App\Modules\Statistics\Console\Commands\Run::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // Work the queue - the last thing that should be processed!
        $schedule->command("queue:work")->everyMinute()->withoutOverlapping();
        //-- end
    }
}
