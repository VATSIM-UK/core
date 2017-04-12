<?php

namespace App\Console;

use File;
use Caffeinated\Modules\Facades\Module;
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
        Commands\GenerateEloquentMethodPHPDoc::class,
        Commands\MembersCertImport::class,
        Commands\MembersCertUpdate::class,
        Commands\SysStatisticsDaily::class,
        Commands\SyncRTS::class,
        Commands\SyncCommunity::class,
        Commands\SyncMentors::class,
        Commands\SyncMoodle::class,
        Commands\TeamSpeakManager::class,
        Commands\TeamSpeakCleanup::class,
        Commands\TeamSpeakDaemon::class,
        Commands\TeamSpeakMapper::class,
        Commands\SlackManager::class,
        Commands\ApiGenerator::class,
        Commands\TestEmails::class,

        /* Third Party */
        \Bugsnag\BugsnagLaravel\Commands\DeployCommand::class,
    ];

    /**
     * Get the Artisan application instance.
     *
     * @return \Illuminate\Console\Application
     */
    protected function getArtisan()
    {
        foreach (Module::enabled() as $module) {
            $moduleCommandsFile = config('modules.path').'/'.$module['basename'].'/Console/commands.php';

            if (File::exists($moduleCommandsFile)) {
                $moduleCommands = require $moduleCommandsFile;
                $this->commands = array_merge($this->commands, $moduleCommands);
            }
        }

        return parent::getArtisan();
    }

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
