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
        $authUser = Account::factory()->create();
        $this->actingAs($authUser);

        $waitingList = WaitingList::factory()->create();
        $account = Account::factory()->create();
        $staffAccount = Account::factory()->create();

        $waitingListAccount = new WaitingListAccount(['added_by' => $staffAccount->id]);
        $waitingListAccount->account_id = $account->id;
        $waitingList->waitingListAccounts()->save($waitingListAccount);

        Log::shouldReceive('channel')->with('audit')->andReturnSelf();
        Log::shouldReceive('info')->once()->withArgs(fn ($msg, $ctx) => $msg === 'Account added to waiting list'
            && isset($ctx['account_id'], $ctx['waiting_list_id'], $ctx['staff_id'])
            && $ctx['account_id'] === $account->id
            && $ctx['waiting_list_id'] === $waitingList->id
            && $ctx['staff_id'] === $staffAccount->id
            && $ctx['actor_id'] === $authUser->id
        );

        $event = new AccountAddedToWaitingList($account, $waitingList, $staffAccount, $waitingListAccount);

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
        $this->markTestSkipped('Documented gap: see FIXME in WaitingListEventSubscriber.php');
    }
}
