<?php

namespace App\Jobs\Cts;

use App\Jobs\ExternalServices\IssueSecondaryForumGroup;
use App\Repositories\Cts\MembershipRepository;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SyncTGMembersToForumGroups implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $rtsId;

    protected $forumGroup;

    public function __construct(int $rtsId, int $forumGroup)
    {
        $this->rtsId = $rtsId;
        $this->forumGroup = $forumGroup;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $membershipRepository = new MembershipRepository;

        $members = $membershipRepository->getMembersOf($this->rtsId);

        foreach ($members as $member) {
            IssueSecondaryForumGroup::dispatch($member->cid, $this->forumGroup);
        }
    }
}
