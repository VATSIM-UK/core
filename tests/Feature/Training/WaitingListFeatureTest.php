<?php

namespace Tests\Feature\Training;

use App\Events\Training\AccountAddedToWaitingList;
use App\Events\Training\AccountDemotedInWaitingList;
use App\Events\Training\AccountPromotedInWaitingList;
use App\Events\Training\AccountRemovedFromWaitingList;
use App\Models\Mship\Account;
use App\Models\Training\WaitingList;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class WaitingListFeatureTest extends TestCase
{
    use RefreshDatabase;

    private $waitingList;

    public function setUp()
    {
        parent::setUp();

        Event::fake();

        $this->waitingList = factory(WaitingList::class)->create();
    }

    /** @test * */
    public function testStudentCanBeAddedToWaitingList()
    {
        $account = factory(Account::class)->create();

        $this->actingAs($this->privacc)->post(route('training.waitingList.store', $this->waitingList), [
            'account_id' => $account->id,
        ])->assertRedirect(route('training.waitingList.show', $this->waitingList))
            ->assertSessionHas('success', 'Account Added to Waiting List');

        Event::assertDispatched(AccountAddedToWaitingList::class, function ($event) use ($account) {
            return $event->account->id === $account->id && $event->waitingList->id === $this->waitingList->id;
        });
    }

    /** @test * */
    public function testRedirectOnUnknownAccountId()
    {
        $this->actingAs($this->privacc)->post(route('training.waitingList.store', $this->waitingList), [
            'account_id' => 12345678,
        ])->assertRedirect(route('training.waitingList.show', $this->waitingList))
            ->assertSessionHas('error', 'Account Not Found.');

        $this->actingAs($this->privacc)->post(route('training.waitingList.remove', $this->waitingList), [
            'account_id' => 12345678,
        ])->assertRedirect(route('training.waitingList.show', $this->waitingList))
            ->assertSessionHas('error', 'Account Not Found.');
    }

    /** @test * */
    public function testAStudentCanOnlyBeInAListOnce()
    {
        $account = factory(Account::class)->create();

        $this->actingAs($this->privacc)->post(route('training.waitingList.store', $this->waitingList), [
            'account_id' => $account->id,
        ])->assertRedirect(route('training.waitingList.show', $this->waitingList))
            ->assertSessionHas('success', 'Account Added to Waiting List');

        $this->actingAs($this->privacc)->post(route('training.waitingList.store', $this->waitingList), [
            'account_id' => $account->id,
        ])->assertSessionHasErrors('account_id', 'That account is already in this waiting list');
    }

    /** @test * */
    public function testAStudentCanBePromoted()
    {
        $account = factory(Account::class)->create();

        $this->actingAs($this->privacc)->post(route('training.waitingList.manage.promote', $this->waitingList), [
            'account_id' => $account->id,
            'position' => 1,
        ])->assertRedirect(route('training.waitingList.show', $this->waitingList))
            ->assertSessionHas('success', 'Waiting list positions changed.');

        Event::assertDispatched(AccountPromotedInWaitingList::class, function ($event) use ($account) {
            return $event->account->id === $account->id && $event->waitingList->id === $this->waitingList->id;
        });
    }

    /** @test * */
    public function testAStudentCanBeDemoted()
    {
        $account = factory(Account::class)->create();

        $this->actingAs($this->privacc)->post(route('training.waitingList.manage.demote', $this->waitingList), [
            'account_id' => $account->id,
            'position' => 1,
        ])->assertRedirect(route('training.waitingList.show', $this->waitingList))
            ->assertSessionHas('success', 'Waiting list positions changed.');

        Event::assertDispatched(AccountDemotedInWaitingList::class, function ($event) use ($account) {
            return $event->account->id === $account->id && $event->waitingList->id === $this->waitingList->id;
        });
    }

    /** @test **/
    public function testAStudentCanBeRemoved()
    {
        $account = factory(Account::class)->create();

        $this->waitingList->addToWaitingList($account, $this->privacc);

        $this->actingAs($this->privacc)->post(route('training.waitingList.remove', $this->waitingList), [
            'account_id' => $account->id,
        ])->assertRedirect(route('training.waitingList.show', $this->waitingList))
            ->assertSessionHas('success', 'Student removed from Waiting List');

        Event::assertDispatched(AccountRemovedFromWaitingList::class, function ($event) use ($account) {
            return $event->account->id === $account->id && $event->waitingList->id === $this->waitingList->id;
        });
    }
}
