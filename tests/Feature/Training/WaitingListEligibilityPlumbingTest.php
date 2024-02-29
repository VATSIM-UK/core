<?php

namespace Tests\Feature\Training;

use App\Events\Training\AccountAddedToWaitingList;
use App\Events\Training\FlagAddedToWaitingList;
use App\Jobs\Training\UpdateAccountWaitingListEligibility;
use App\Models\Training\WaitingList;
use App\Models\Training\WaitingList\WaitingListFlag;
use App\Models\Training\WaitingList\WaitingListStatus;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class WaitingListEligibilityPlumbingTest extends TestCase
{
    use DatabaseTransactions;

    public function test_checks_eligibility_after_adding_to_waiting_list()
    {
        Bus::fake();

        $this->actingAs($this->privacc);

        $waitingList = factory(WaitingList::class)->create();
        $waitingList->addToWaitingList($this->user, $this->privacc);

        Bus::assertDispatched(UpdateAccountWaitingListEligibility::class, function ($job) {
            return $job->account->id === $this->user->id;
        });
    }

    public function test_checks_eligibility_after_marking_manual_flag()
    {
        Bus::fake();

        $this->actingAs($this->privacc);

        Event::fakeFor(function () {
            $waitingList = factory(WaitingList::class)->create();

            $flag = factory(WaitingListFlag::class)->create([
                'list_id' => $waitingList->id,
                'default_value' => false,
            ]);
            $waitingList->addFlag($flag);
            $waitingList->addToWaitingList($this->user, $this->privacc);

            $waitingListAccount = $waitingList->fresh()->accounts->first()->pivot;

            // manually populate the flag as we are
            // deliberately supressing the added to waiting list event
            $waitingListAccount->addFlag($flag);

            $waitingListAccount->fresh()->markFlag($flag);

            Bus::assertDispatched(UpdateAccountWaitingListEligibility::class, function ($job) {
                return $job->account->id === $this->user->id;
            });
        }, [AccountAddedToWaitingList::class, FlagAddedToWaitingList::class]);
    }

    public function test_checks_eligibility_after_unmarking_manual_flag()
    {
        Bus::fake();

        $this->actingAs($this->privacc);

        Event::fakeFor(function () {
            $waitingList = factory(WaitingList::class)->create();

            $flag = factory(WaitingListFlag::class)->create([
                'list_id' => $waitingList->id,
                'default_value' => false,
            ]);
            $waitingList->addFlag($flag);
            $waitingList->addToWaitingList($this->user, $this->privacc);

            $waitingListAccount = $waitingList->fresh()->accounts->first()->pivot;

            // manually populate the status and flag as we are
            // deliberately supressing the added to waiting list event
            $waitingListAccount->addStatus(WaitingListStatus::find(WaitingListStatus::DEFAULT_STATUS));
            $waitingListAccount->addFlag($flag);

            // force a marked flag.
            $waitingListAccount->flags()->get()->find($flag->id)->pivot->update([
                'marked_at' => now(),
            ]);

            $waitingListAccount->fresh()->unMarkFlag($flag);

            Bus::assertDispatched(UpdateAccountWaitingListEligibility::class, function ($job) {
                return $job->account->id === $this->user->id;
            });
        }, [AccountAddedToWaitingList::class, FlagAddedToWaitingList::class]);
    }

    public function test_checks_eligibility_when_command_run_for_user()
    {
        Event::fakeFor(function () {
            $waitingList = factory(WaitingList::class)->create();
            $waitingList->addToWaitingList($this->user, $this->privacc);

            Bus::fake();

            $this->artisan('waiting-lists:check-eligibility', [
                'account' => $this->user->id,
            ]);

            Bus::assertDispatched(UpdateAccountWaitingListEligibility::class, function ($job) {
                return $job->account->id === $this->user->id;
            });
        }, [AccountAddedToWaitingList::class, FlagAddedToWaitingList::class]);
    }

    public function test_checks_eligibility_for_each_member_of_list()
    {
        Event::fakeFor(function () {
            $waitingListA = factory(WaitingList::class)->create();
            $waitingListA->addToWaitingList($this->user, $this->privacc);

            $waitingListB = factory(WaitingList::class)->create();
            $waitingListB->addToWaitingList($this->user, $this->privacc);

            Bus::fake();

            $this->artisan('waiting-lists:check-eligibility');

            Bus::assertDispatched(UpdateAccountWaitingListEligibility::class, function ($job) {
                return $job->account->id === $this->user->id;
            });

            Bus::assertDispatched(UpdateAccountWaitingListEligibility::class, function ($job) {
                return $job->account->id === $this->user->id;
            });
        }, [AccountAddedToWaitingList::class, FlagAddedToWaitingList::class]);
    }

    public function test_checks_elgibility_following_new_flag_added_to_list()
    {
        Event::fakeFor(function () {
            $waitingList = factory(WaitingList::class)->create();
            $waitingList->addToWaitingList($this->user, $this->privacc);

            Bus::fake();

            $flag = factory(WaitingListFlag::class)->create([
                'list_id' => $waitingList->id,
                'default_value' => false,
            ]);
            $waitingList->addFlag($flag);

            Bus::assertDispatched(UpdateAccountWaitingListEligibility::class, function ($job) {
                return $job->account->id === $this->user->id;
            });
        }, [AccountAddedToWaitingList::class]);
    }
}
