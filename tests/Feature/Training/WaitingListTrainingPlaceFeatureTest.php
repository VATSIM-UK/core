<?php

namespace Tests\Feature\Training;

use App\Events\Training\TrainingPlaceOffered;
use App\Models\Mship\Account;
use App\Models\NetworkData\Atc;
use App\Models\Training\TrainingPlace\TrainingPosition;
use App\Models\Training\WaitingList;
use App\Models\Training\WaitingList\WaitingListStatus;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class WaitingListTrainingPlaceFeatureTest extends TestCase
{
    use DatabaseTransactions;

    private $waitingList;
    private $account;

    public function setUp(): void
    {
        parent::setUp();

        $this->markNovaTest();

        $this->waitingList = factory(WaitingList::class)->create();

        $this->account = factory(Account::class)->create();

        // add them to the waiting list
        $this->waitingList->addToWaitingList($this->account, $this->privacc);

        // stop nova middleware to test the endpoints in isolation
        Route::middlewareGroup('nova', []);
    }

    public function testTrainingPlaceCanBeOfferedToStudentEligibleOnWaitingList()
    {
        // create a training position with no assigned training places
        $trainingPosition = TrainingPosition::factory()->create(['waiting_list_id' => $this->waitingList->id, 'places' => 1]);

        // find the active status for a waiting list account
        $status = WaitingListStatus::find(WaitingListStatus::DEFAULT_STATUS);

        // finds the account in the waiting list
        $waitingListAccount = $this->waitingList->accounts()->findOrFail($this->account->id)->pivot;
        // adds the status to make account eligible.
        $waitingListAccount->addStatus($status);

        // create valid network data to pass ATC hour check
        factory(Atc::class)->create(['minutes_online' => 721, 'account_id' => $this->account->id, 'disconnected_at' => now()]);

        Event::fakeFor(function () use ($trainingPosition) {
            $this->actingAs($this->privacc)
                ->post("nova-vendor/waiting-lists-manager/waitingLists/{$this->waitingList->id}/position/{$trainingPosition->id}/offer",
                    [
                        'account_id' => $this->account->id,
                    ]
                )
                ->assertStatus(201);

            Event::assertDispatched(TrainingPlaceOffered::class, function ($event) use ($trainingPosition) {
                return $event->getTrainingPlaceOffer()->trainingPosition->id == $trainingPosition->id
                    && $event->getTrainingPlaceOffer()->account->id == $this->account->id;
            });
        });
    }

    /** @test */
    public function testTrainingPlaceCannotBeOfferedToIneligibleAccount()
    {
        $this->withoutExceptionHandling();
        // create a training position with no assigned training places
        $trainingPosition = TrainingPosition::factory()->create(['waiting_list_id' => $this->waitingList->id, 'places' => 1]);

        // create an account without ATC hours and no valid status.
        $differentAccountWithoutHours = factory(Account::class)->create();

        $this->waitingList->addToWaitingList($differentAccountWithoutHours, $this->privacc);

        Event::fakeFor(function () use ($trainingPosition) {
            $this->actingAs($this->privacc)
            ->post("nova-vendor/waiting-lists-manager/waitingLists/{$this->waitingList->id}/position/{$trainingPosition->id}/offer",
                [
                    'account_id' => $this->account->id,
                ]
            )
            ->assertStatus(403);

            Event::assertNotDispatched(TrainingPlaceOffered::class);
        });
    }

    /** @test */
    public function testReturns400StatusCodeWhenInvalidAccountGivenInBody()
    {
        $trainingPosition = TrainingPosition::factory()->create(['waiting_list_id' => $this->waitingList->id, 'places' => 1]);

        $this->actingAs($this->privacc)
            ->post("nova-vendor/waiting-lists-manager/waitingLists/{$this->waitingList->id}/position/{$trainingPosition->id}/offer", [
                'account_id' => 0,
            ])
            ->assertStatus(400);
    }
}
