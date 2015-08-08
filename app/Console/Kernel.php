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
        \TestCommand::class,
        \MembersCertImport::class,
        \MembersCertUpdate::class,
        \StatisticsDaily::class,
        \RebuildModelDependencies::class,
        \PostmasterParse::class,
        \PostmasterDispatch::class,
        //\SyncRTS::class,
        \SyncCommunity::class,
        \TeamspeakManager::class,
        \TeamspeakCleanup::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->command('inspire')
                 ->hourly();
    }
}
