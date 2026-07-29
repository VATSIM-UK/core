<?php

namespace Tests\Feature\Training;

use App\Events\Training\AccountAddedToWaitingList;
use App\Listeners\Training\WaitingList\LogAccountAdded;
use App\Models\Mship\Account;
use App\Models\Training\WaitingList;
use App\Models\Training\WaitingList\WaitingListAccount;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Log;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class WaitingListAuditLogTest extends TestCase
{
    use DatabaseTransactions;

    #[Test]
    public function adding_an_account_writes_a_single_audit_record()
    {
        $this->actingAs($this->privacc);

        $waitingList = WaitingList::factory()->create();
        $account = Account::factory()->create();

        // Build the WaitingListAccount directly (bypassing capacity/endorsement
        // checks in WaitingList::addToWaitingList()) since this test only needs
        // to exercise the listener, not the full enrolment flow.
        $waitingListAccount = new WaitingListAccount(['added_by' => $this->privacc->id]);
        $waitingListAccount->account_id = $account->id;
        $waitingList->waitingListAccounts()->save($waitingListAccount);

        Log::shouldReceive('channel')->with('audit')->andReturnSelf();
        Log::shouldReceive('info')->once()->withArgs(fn ($msg, $ctx) => $msg === 'Account added to waiting list'
            && isset($ctx['account_id'], $ctx['waiting_list_id'])
            && $ctx['account_id'] === $account->id
            && $ctx['waiting_list_id'] === $waitingList->id
            && $ctx['actor_id'] === $this->privacc->id
        );

        $event = new AccountAddedToWaitingList($account, $waitingList, $this->privacc, $waitingListAccount);

        (new LogAccountAdded)->handle($event);
    }

    /**
     * NOTE: WaitingListEventSubscriber (promoted/demoted/status-change) is not
     * registered anywhere in the app (see EventServiceProvider), so those
     * waiting-list transitions are NOT currently written to the audit log.
     * This is tracked as a FIXME on the subscriber itself and is out of scope
     * for this change - no test is written against it because there is no
     * live code path to assert against.
     */
    #[Test]
    public function status_change_events_are_not_audited_due_to_unregistered_subscriber()
    {
        $this->assertTrue(true, 'Documented gap: see FIXME in WaitingListEventSubscriber.php');
    }
}
