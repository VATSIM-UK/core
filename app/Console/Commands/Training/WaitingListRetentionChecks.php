<?php

namespace App\Console\Commands\Training;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class WaitingListRetentionChecks extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'waiting-lists:send-retention-checks';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create jobs to send retention checks to members on the waiting lists';

    /**
     * Execute the console command.
     */
    public function handle()
    {

    }
}
