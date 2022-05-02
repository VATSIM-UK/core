<?php

namespace App\Console\Commands\ExternalServices;

use App\Jobs\Cts\SyncTGMembersToForumGroups;
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
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        SyncTGMembersToForumGroups::dispatch(13, 2498); // Pilots
        SyncTGMembersToForumGroups::dispatch(14, 2496); // TGNC
        SyncTGMembersToForumGroups::dispatch(17, 2497); // TGE
        SyncTGMembersToForumGroups::dispatch(12, 2499); // TG Heathrow
        SyncTGMembersToForumGroups::dispatch(18, 2494); // TG TWR
        SyncTGMembersToForumGroups::dispatch(19, 2495); // TG APP
    }
}
