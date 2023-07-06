<?php

namespace Tests\Feature\Training;

use App\Events\Training\AccountChangedStatusInWaitingList;
use App\Events\Training\AccountNoteChanged;
use App\Models\Mship\Account;
use App\Models\Training\WaitingList;
use App\Models\Training\WaitingList\WaitingListFlag;
use App\Services\Training\AddToWaitingList;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class WaitingListFeatureTest extends TestCase
{
    use DatabaseTransactions;

    private $waitingList;

    public function setUp(): void
    {
        parent::setUp();

        $this->markNovaTest();

        $this->waitingList = factory(WaitingList::class)->create();

        Route::middlewareGroup('nova', []);
    }

    /** @test * */
    public function testAStudentCanHaveTheirStatusChangedToDeferred()
    {
        $account = Account::factory()->create();

        $this->waitingList->addToWaitingList($account, $this->privacc);

        Event::fakeFor(function () use ($account) {
            $this->actingAs($this->privacc)->patch(
                "nova-vendor/waiting-lists-manager/accounts/{$this->waitingList->id}/defer",
                [
                    'account_id' => $account->id,
                ]
            )->assertSuccessful();

            Event::assertDispatched(AccountChangedStatusInWaitingList::class, function ($event) use ($account) {
                return (int) $event->account->id === $account->id && $event->waitingList->id === $this->waitingList->id;
            });
        });
    }

    /** @test * */
    public function testAStudentCanHaveTheirStatusChangedToActive()
    {
        $account = Account::factory()->create();

        $this->waitingList->addToWaitingList($account, $this->privacc);

        Event::fakeFor(function () use ($account) {
            $this->actingAs($this->privacc)->patch(
                "nova-vendor/waiting-lists-manager/accounts/{$this->waitingList->id}/active",
                [
                    'account_id' => $account->id,
                ]
            )->assertSuccessful();

            Event::assertDispatched(AccountChangedStatusInWaitingList::class, function ($event) use ($account) {
                return (int) $event->account->id === $account->id && $event->waitingList->id === $this->waitingList->id;
            });
        });
    }

    /** @test */
    public function testAStudentCanHaveAFlagToggledAboutThem()
    {
        $account = Account::factory()->create();
        $flag = factory(WaitingListFlag::class)->create();
        $this->waitingList->addFlag($flag);

        // required due to event firing propagating flags
        handleService(new AddToWaitingList($this->waitingList, $account, $this->privacc));
        $waitingListAccount = $this->waitingList->accounts->find($account->id)->pivot;

        $this->actingAs($this->privacc)->patch("nova-vendor/waiting-lists-manager/flag/{$waitingListAccount->flags->first()->pivot->id}/toggle")->assertSuccessful();
        $this->assertTrue($waitingListAccount->flags->first()->pivot->value);
    }

    /** @test */
    public function testStudentsCanHaveNoteAddedAboutThem()
    {
        $account = Account::factory()->create();

        $this->waitingList->addToWaitingList($account, $this->privacc);

        $waitingListAccount = $this->waitingList->accounts->find($account->id)->pivot;

        Event::fakeFor(function () use ($waitingListAccount) {
            $this->actingAs($this->privacc)
                ->patch(
                    "nova-vendor/waiting-lists-manager/notes/{$waitingListAccount->id}/create",
                    ['notes' => 'This is a note']
                )
                ->assertSuccessful();

            Event::assertDispatched(AccountNoteChanged::class, function ($event) use ($waitingListAccount) {
                return $event->account->id == $waitingListAccount->account->id
                    && $event->newNoteContent == 'This is a note'
                    && $event->oldNoteContent == null;
            });
        });

        $this->assertEquals('This is a note', $waitingListAccount->fresh()->notes);
    }
}
