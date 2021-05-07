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

        $schedule->command('teaman:runner', ['-v'])
            ->everyMinute()
            ->withoutOverlapping();

        $schedule->command('networkdata:download')
            ->everyTwoMinutes()
            ->withoutOverlapping(5)
            ->graceTimeInMinutes(10);

        $schedule->command('horizon:snapshot')
            ->everyFiveMinutes()
            ->withoutOverlapping();

        $schedule->command('visit-transfer:cleanup')
            ->everyTenMinutes();

        // === By Hour === //

        $schedule->command('members:certupdate')
            ->hourlyAt(30)
            ->graceTimeInMinutes(15);

        $schedule->command('sync:cts-roles')
            ->hourlyAt(15)
            ->graceTimeInMinutes(15);

        $schedule->command('members:certimport')
            ->everyTwoHours()
            ->graceTimeInMinutes(15);

        // === By Day === //

        $schedule->command('telescope:prune')
            ->dailyAt('03:30');

        // $schedule->command('DivMembers:CertUpdate')
        //    ->dailyAt('05:00');

        $schedule->command('schedule-monitor:clean')
            ->dailyAt('08:00');

        $schedule->command('members:certimport', ['--full'])
            ->twiceDaily(4, 15)
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
