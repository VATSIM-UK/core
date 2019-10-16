<?php

namespace App\Jobs\TGForumGroups;

use Alawrence\Ipboard\Exceptions\IpboardMemberIdInvalid;
use Alawrence\Ipboard\Ipboard;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

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

        // Get the IP Board id based on the CID provided
//        require_once '/srv/www/community/init.php';
//        require_once \IPS\ROOT_PATH . '/system/Db/Db.php';
//
//        $ipBoardMembers = \IPS\Db::i()->select(
//            ['member_id', 'p.field_12'],
//            'core_members',
//            'p.field_12 = 1258635'
//        );
//
//        $array = $ipBoardMembers->setKeyField('member_id')->setValueField('p.field_12');

        $ipboardMemberId = 0;

        try {
            $ipboardUser = $ipboard->getMemberById($ipboardMemberId);
        } catch (IpboardMemberIdInvalid $e) {
            // What happens if we cannot find the member?
        }

        // Get all existing secondary groups
        $currentSecondaryGroups = [];
        foreach ($ipboardUser->secondaryGroups as $secondaryGroup) {
            array_push($currentSecondaryGroups, $secondaryGroup->id);
        }

        // Get current primary group
        $currentPrimaryGroup = [$ipboardUser->primaryGroup->id];

        // If they already have the group, don't do anything else
        if (in_array($this->forumGroup, $currentPrimaryGroup) || in_array($this->forumGroup, $currentSecondaryGroups)) {
            return;
        }

        // If not, give them the group
        $newSecondaryGroups = $currentSecondaryGroups;
        array_push($newSecondaryGroups, $this->forumGroup);
        $ipboard->updateMember($ipboardMember->id, ['secondaryGroups' => $newSecondaryGroups]);
    }
}
