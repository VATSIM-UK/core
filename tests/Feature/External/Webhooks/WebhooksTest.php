<?php

namespace Feature\External\Webhooks;

use App\Jobs\ExternalServices\VatsimNet\Webhooks\MemberChangedAction;
use App\Jobs\ExternalServices\VatsimNet\Webhooks\MemberCreatedAction;
use App\Models\Mship\Account;
use App\Models\Mship\Qualification;
use Tests\TestCase;
use Tests\Unit\Training\WaitingList\WaitingListTestHelper;

class WebhooksTest extends TestCase
{
    use WaitingListTestHelper;

    public function test_changed_action_get_dispatched()
    {
        \Queue::fake();
        \Bus::fake();

        $response = $this->post(
            route('external.vatsim-net.webhook'),
            $this->buildChangedSample(),
            $this->authHeaders()
        );

        $response->assertStatus(200);

        \Bus::assertChained([
            MemberChangedAction::class,
        ]);
    }

    public function test_created_action_get_dispatched()
    {
        \Queue::fake();
        \Bus::fake();

        $response = $this->post(
            route('external.vatsim-net.webhook'),
            $this->buildCreatedSample(),
            $this->authHeaders()
        );

        $response->assertStatus(200);

        \Bus::assertChained([
            MemberCreatedAction::class,
        ]);
    }

    public function test_actions_occur_in_correct_order()
    {
        \Queue::fake();
        \Bus::fake();

        $response = $this->post(
            route('external.vatsim-net.webhook'),
            $this->buildMultiSampleWrongOrder(),
            $this->authHeaders()
        );

        $response->assertStatus(200);

        \Bus::assertChained([
            MemberCreatedAction::class,
            MemberChangedAction::class,
        ]);
    }

    public function test_creation_handler()
    {
        $webhook = $this->buildCreatedSample();
        $accountId = $webhook['resource'];
        MemberCreatedAction::dispatchSync($accountId, $webhook['actions'][0]);

        $this->assertInstanceOf(Account::class, $account = Account::find($accountId));
        $this->assertEquals('John', $account->name_first);
        $this->assertEquals('Doe', $account->name_last);
        $this->assertEquals('tech@vatsim.net', $account->email);

        $this->assertEquals('GBR', $account->primary_state->pivot->division);
        $this->assertTrue($account->hasQualification(Qualification::code('OBS')->firstOrFail()));
        $this->assertTrue($account->hasQualification(Qualification::code('P0')->firstOrFail()));
    }

    public function test_update_handler()
    {
        $this->test_creation_handler();

        $webhook = $this->buildChangedSample();
        $accountId = $webhook['resource'];
        MemberChangedAction::dispatchSync($accountId, $webhook['actions'][0]);

        $this->assertInstanceOf(Account::class, $account = Account::find($accountId));
        $this->assertTrue($account->hasQualification(Qualification::code('S1')->firstOrFail()));
    }

    public function test_update_leave_division()
    {
        // Make sure that account is booted from waiting list when leaving division

        $this->test_creation_handler();

        $webhook = $this->buildLeavingSample();
        $accountId = $webhook['resource'];

        $this->assertInstanceOf(Account::class, $account = Account::find($accountId));
        $waitingList = $this->createList();
        $waitingList->addToWaitingList($account, $this->privacc);
        $this->assertTrue($waitingList->includesAccount($account));

        MemberChangedAction::dispatchSync($accountId, $webhook['actions'][0]);

        $this->assertInstanceOf(Account::class, $account = Account::find($accountId));
        $this->assertNotEquals('GBR', $account->primary_state->pivot->division);

        $this->assertFalse($waitingList->includesAccount($account));
    }

    private function authHeaders(): array
    {
        return [
            'Authorization' => config('services.vatsim-net.webhook.key'),
        ];
    }

    private function buildCreatedSample(): array
    {
        return [
            'resource' => 1851903,
            'actions' => [
                $this->createdAction(),
            ],
        ];
    }

    private function buildChangedSample(): array
    {
        return [
            'resource' => 1851903,
            'actions' => [
                $this->promotionAction(),
            ],
        ];
    }

    private function buildLeavingSample()
    {
        return [
            'resource' => 1851903,
            'actions' => [
                $this->leaveAction(),
            ],
        ];
    }

    private function buildMultiSampleWrongOrder()
    {
        return [
            'resource' => 1851903,
            'actions' => [
                $this->promotionAction(),
                $this->createdAction(),
            ],
        ];
    }

    private function createdAction(): array
    {
        return [
            'action' => 'member_created_action',
            'authority' => 'myVATSIM',
            'comment' => null,
            'deltas' => [
                ['field' => 'id', 'before' => null, 'after' => 1851903],
                ['field' => 'name_first', 'before' => null, 'after' => 'John'],
                ['field' => 'name_last', 'before' => null, 'after' => 'Doe'],
                ['field' => 'email', 'before' => null, 'after' => 'tech@vatsim.net'],
                ['field' => 'rating', 'before' => null, 'after' => 1],
                ['field' => 'pilotrating', 'before' => null, 'after' => 0],
                ['field' => 'susp_date', 'before' => null, 'after' => '2022-10-11T12:09:13'],
                ['field' => 'reg_date', 'before' => null, 'after' => '2022-10-11T12:09:13'],
                ['field' => 'region_id', 'before' => null, 'after' => 'EUR'],
                ['field' => 'division_id', 'before' => null, 'after' => 'GBR'],
                ['field' => 'subdivision_id', 'before' => null, 'after' => null],
                ['field' => 'lastratingchange', 'before' => null, 'after' => null],
            ],
            'timestamp' => 1865490153.562588,
        ];
    }

    private function promotionAction(): array
    {
        return [
            'action' => 'member_changed_action',
            'authority' => 'VATUK',
            'comment' => 'Promotion to S1',
            'deltas' => [
                ['field' => 'rating', 'before' => 1, 'after' => 2],
                ['field' => 'lastratingchange', 'before' => null, 'after' => '2022-10-18T17:19:34'],
            ],
            'timestamp' => 1966113574.577162,
        ];
    }

    private function leaveAction(): array
    {
        return [
            'action' => 'member_changed_action',
            'authority' => 'VATUK',
            'comment' => 'Leaving division',
            'deltas' => [
                ['field' => 'division_id', 'before' => 'GBR', 'after' => 'EUD'],
            ],
            'timestamp' => 1966113574.577162,
        ];
    }
}
