<?php

namespace Tests\Feature\Training;

use App\Events\Training\AccountAddedToWaitingList;
use App\Events\Training\AccountChangedStatusInWaitingList;
use App\Events\Training\AccountDemotedInWaitingList;
use App\Events\Training\AccountPromotedInWaitingList;
use App\Events\Training\AccountRemovedFromWaitingList;
use App\Models\Mship\Account;
use App\Models\Training\WaitingList;
use App\Models\Training\WaitingListFlag;
use App\Services\Training\AddToWaitingList;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class WaitingListFeatureTest extends TestCase
{
    use DatabaseTransactions;

    private $waitingList;

    public function setUp()
    {
        parent::setUp();

        $this->waitingList = factory(WaitingList::class)->create();

        Route::middlewareGroup('nova', []);
    }

    /** @test * */
    public function testStudentCanBeAddedToWaitingList()
    {
        $account = factory(Account::class)->create();

        Event::fakeFor(function () use ($account) {
            $this->actingAs($this->privacc)->post(route('training.waitingList.store', $this->waitingList), [
                'account_id' => $account->id,
            ])->assertRedirect(route('training.waitingList.show', $this->waitingList))
                ->assertSessionHas('success', 'Account Added to Waiting List');

            Event::assertDispatched(AccountAddedToWaitingList::class, function ($event) use ($account) {
                return $event->account->id === $account->id && $event->waitingList->id === $this->waitingList->id;
            });
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
        $this->markNovaTest();

        [$account, $account2, $account3] = factory(Account::class, 3)->create();

        $this->waitingList->addToWaitingList($account, $this->privacc);
        $this->waitingList->addToWaitingList($account2, $this->privacc);
        $this->waitingList->addToWaitingList($account3, $this->privacc);

        Event::fakeFor(function () use ($account, $account2, $account3) {
            $this->actingAs($this->privacc)->post("nova-vendor/waiting-lists-manager/accounts/{$this->waitingList->id}/promote", [
                'account_id' => $account2->id,
            ])->assertSuccessful();

            Event::assertDispatched(AccountPromotedInWaitingList::class, function ($event) use ($account2) {
                return $event->account->id === $account2->id && $event->waitingList->id === $this->waitingList->id;
            });
        });
    }

    /** @test * */
    public function testAStudentCanBeDemoted()
    {
        $this->markNovaTest();

        [$account, $account2, $account3] = factory(Account::class, 3)->create();

        $this->waitingList->addToWaitingList($account, $this->privacc);
        $this->waitingList->addToWaitingList($account2, $this->privacc);
        $this->waitingList->addToWaitingList($account3, $this->privacc);

        Event::fakeFor(function () use ($account) {
            $this->actingAs($this->privacc)->post("nova-vendor/waiting-lists-manager/accounts/{$this->waitingList->id}/demote", [
                'account_id' => $account->id,
            ])->assertSuccessful();

            Event::assertDispatched(AccountDemotedInWaitingList::class, function ($event) use ($account) {
                return $event->account->id === $account->id && $event->waitingList->id === $this->waitingList->id;
            });
        });

    }

    /** @test **/
    public function testAStudentCanBeRemoved()
    {
        $account = factory(Account::class)->create();

        $this->waitingList->addToWaitingList($account, $this->privacc);

        Event::fakeFor(function () use ($account) {
            $this->actingAs($this->privacc)->post(route('training.waitingList.remove', $this->waitingList), [
                'account_id' => $account->id,
            ])->assertRedirect(route('training.waitingList.show', $this->waitingList))
                ->assertSessionHas('success', 'Student removed from Waiting List');

            Event::assertDispatched(AccountRemovedFromWaitingList::class, function ($event) use ($account) {
                return $event->account->id === $account->id && $event->waitingList->id === $this->waitingList->id;
            });
        });
    }

    /** @test **/
    public function testAStudentCanHaveTheirStatusChangedToDeferred()
    {
        $account = factory(Account::class)->create();

        $this->waitingList->addToWaitingList($account, $this->privacc);

        Event::fakeFor(function () use ($account) {
            $this->actingAs($this->privacc)->patch("nova-vendor/waiting-lists-manager/accounts/{$this->waitingList->id}/defer", [
                'account_id' => $account->id,
            ])->assertSuccessful();

            Event::assertDispatched(AccountChangedStatusInWaitingList::class, function ($event) use ($account) {
                return $event->account->id === $account->id && $event->waitingList->id === $this->waitingList->id;
            });
        });
    }

    /** @test **/
    public function testAStudentCanHaveTheirStatusChangedToActive()
    {
        $account = factory(Account::class)->create();

        $this->waitingList->addToWaitingList($account, $this->privacc);

        Event::fakeFor(function () use ($account) {
            $this->actingAs($this->privacc)->patch("nova-vendor/waiting-lists-manager/accounts/{$this->waitingList->id}/active", [
                'account_id' => $account->id,
            ])->assertSuccessful();

            Event::assertDispatched(AccountChangedStatusInWaitingList::class, function ($event) use ($account) {
                return $event->account->id === $account->id && $event->waitingList->id === $this->waitingList->id;
            });
        });
    }

    /** @test */
    public function testAStudentCanHaveAFlagToggledAboutThem()
    {
        $account = factory(Account::class)->create();
        $flag = factory(WaitingListFlag::class)->create();
        $this->waitingList->addFlag($flag);

        // required due to event firing propagating flags
        handleService(new AddToWaitingList($this->waitingList, $account, $this->privacc));
        $waitingListAccount = $this->waitingList->accounts->find($account->id)->pivot;

        $this->actingAs($this->privacc)->patch("nova-vendor/waiting-lists-manager/flag/{$waitingListAccount->flags->first()->pivot->id}/toggle")->assertSuccessful();
        $this->assertTrue($waitingListAccount->flags->first()->pivot->value);
    }
}
