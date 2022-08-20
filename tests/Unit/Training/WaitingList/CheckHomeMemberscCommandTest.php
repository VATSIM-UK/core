<?php

namespace Tests\Unit\Training\WaitingList;

use App\Jobs\Training\CheckHomeMemberInWaitingList;
use App\Models\Mship\Account;
use App\Models\Training\WaitingList;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class CheckHomeMemberscCommandTest extends TestCase
{
    use DatabaseTransactions;
    /** @test */
    public function itShouldSendAJobForAllMembersOfWaitingList()
    {
        $accounts = factory(Account::class, 3)->create();
        $waitingList = factory(WaitingList::class)->create();

        $accounts->each(function ($account) use ($waitingList) {
            $waitingList->addToWaitingList($account, $this->privacc);
        });

        Queue::fake();

        $this->artisan("training:check-home-members");

        Queue::assertPushed(CheckHomeMemberInWaitingList::class, $accounts->count());
    }
}
