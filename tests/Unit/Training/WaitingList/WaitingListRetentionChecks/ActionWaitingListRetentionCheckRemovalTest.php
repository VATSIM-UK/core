<?php

namespace Tests\Unit\Training\WaitingList\WaitingListRetentionChecks;

use App\Jobs\Training\ActionWaitingListRetentionCheckRemoval;
use App\Models\Mship\Account;
use App\Models\Training\WaitingList;
use App\Models\Training\WaitingList\WaitingListRetentionCheck;
use App\Models\VisitTransfer\Application;
use Illuminate\Support\Facades\Notification;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ActionWaitingListRetentionCheckRemovalTest extends TestCase
{
    private WaitingListRetentionCheck $retentionCheck;

    private Account $account;

    private WaitingList $waitingList;

    protected function setUp(): void
    {
        parent::setUp();

        Notification::fake();

        $this->account = Account::factory()->create();
        $this->waitingList = WaitingList::factory()->create();

        $waitingListAccount = $this->waitingList->addToWaitingList($this->account, $this->account);

        $this->retentionCheck = WaitingListRetentionCheck::factory()->create([
            'waiting_list_account_id' => $waitingListAccount->id,
            'expires_at' => now()->subDay(),
            'status' => WaitingListRetentionCheck::STATUS_PENDING,
        ]);
    }

    private function setWaitingListAsVt(bool $isVt = true): void
    {
        $this->waitingList->update([
            'feature_toggles' => ['is_vt' => $isVt],
        ]);
        $this->waitingList->refresh();
    }

    #[Test]
    public function it_cancels_accepted_vt_applications_when_the_waiting_list_is_a_vt_list(): void
    {
        $this->setWaitingListAsVt();

        $application = Application::factory()->create([
            'account_id' => $this->account->id,
            'status' => Application::STATUS_ACCEPTED,
        ]);

        (new ActionWaitingListRetentionCheckRemoval($this->retentionCheck))->handle();

        $this->assertEquals(Application::STATUS_CANCELLED, $application->fresh()->status);
    }

    #[Test]
    public function it_does_not_cancel_non_accepted_vt_applications_when_the_waiting_list_is_a_vt_list(): void
    {
        $this->setWaitingListAsVt();

        $application = Application::factory()->create([
            'account_id' => $this->account->id,
            'status' => Application::STATUS_SUBMITTED,
        ]);

        (new ActionWaitingListRetentionCheckRemoval($this->retentionCheck))->handle();

        $this->assertEquals(Application::STATUS_SUBMITTED, $application->fresh()->status);
    }

    #[Test]
    public function it_does_not_cancel_vt_applications_for_other_accounts_when_the_waiting_list_is_a_vt_list(): void
    {
        $this->setWaitingListAsVt();

        $otherAccount = Account::factory()->create();
        $application = Application::factory()->create([
            'account_id' => $otherAccount->id,
            'status' => Application::STATUS_ACCEPTED,
        ]);

        (new ActionWaitingListRetentionCheckRemoval($this->retentionCheck))->handle();

        $this->assertEquals(Application::STATUS_ACCEPTED, $application->fresh()->status);
    }

    #[Test]
    public function it_does_not_cancel_vt_applications_when_the_waiting_list_is_not_a_vt_list(): void
    {
        $this->setWaitingListAsVt(false);

        $application = Application::factory()->create([
            'account_id' => $this->account->id,
            'status' => Application::STATUS_ACCEPTED,
        ]);

        (new ActionWaitingListRetentionCheckRemoval($this->retentionCheck))->handle();

        $this->assertEquals(Application::STATUS_ACCEPTED, $application->fresh()->status);
    }
}
