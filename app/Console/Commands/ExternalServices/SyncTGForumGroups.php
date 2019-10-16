<?php

namespace App\Console\Commands\ExternalServices;

use App\Jobs\TGForumGroups\SyncPilots;
use App\Jobs\TGForumGroups\SyncTG1;
use App\Jobs\TGForumGroups\SyncTG2;
use App\Jobs\TGForumGroups\SyncTGE;
use App\Jobs\TGForumGroups\SyncTGNC;
use Illuminate\Console\Command;

class SyncTGForumGroups extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sync:tg-forum-groups';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This command will sync members (of all types) of each TG into the relevant forum group.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        SyncPilots::dispatch();
        SyncTG1::dispatch();
        SyncTG2::dispatch();
        SyncTGE::dispatch();
        SyncTGNC::dispatch();

        $this->info('Done');
    }
}
