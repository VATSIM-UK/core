<?php

namespace App\Jobs\TGForumGroups;

use Alawrence\Ipboard\Ipboard;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Log;

class SyncToForumGroup implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $cid;
    protected $forumGroup;

    public function __construct(int $cid, int $forumGroup)
    {
        $this->cid = $cid;
        $this->forumGroup = $forumGroup;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $ipboard = new Ipboard();

        require_once config('services.community.init_file');
        require_once \IPS\ROOT_PATH . '/system/Db/Db.php';

        $members = \IPS\Db::i()->select('member_id', 'core_members', ['vatsim_cid=?', $this->cid]);

        if (count($members) != 1) {
            Log::info('Unable to sync TG Forum Groups for' . $this->cid);
        }

        $ipboardUsers = [];

        foreach ($members as $member) {
            array_push($ipboardUsers, $member);
        }

        $ipboardUser = $ipboard->getMemberById($ipboardUsers[0]);

        $currentPrimaryGroup = [$ipboardUser->primaryGroup->id];
        $currentSecondaryGroups = [];
        foreach ($ipboardUser->secondaryGroups as $secondaryGroup) {
            array_push($currentSecondaryGroups, $secondaryGroup->id);
        }

        // If they already have the group, don't do anything else
        if (in_array($this->forumGroup, $currentPrimaryGroup) || in_array($this->forumGroup, $currentSecondaryGroups)) {
            return;
        }

        // If they don't have the group, give it to them.
        $newSecondaryGroups = $currentSecondaryGroups;
        array_push($newSecondaryGroups, $this->forumGroup);
        $ipboard->updateMember($ipboardUser->id, ['secondaryGroups' => $newSecondaryGroups]);
    }
}
