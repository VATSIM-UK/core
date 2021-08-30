<?php

namespace Tests\Unit\Training\TrainingPlace;

use Tests\TestCase;
use App\Models\Mship\Account;
use Illuminate\Support\Facades\Notification;
use App\Services\Training\OfferTrainingPlace;
use App\Models\Training\TrainingPlace\TrainingPosition;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class OfferTrainingPlaceServiceTest extends TestCase
{
    use DatabaseTransactions;

    public function setUp() : void
    {
        parent::setUp();

        Notification::fake();
    }

    /** @test */
    public function itShouldCreateNewOffer()
    {
        $trainingPosition = TrainingPosition::factory()->create();
        $offeringAccount = factory(Account::class)->create();

        handleService(new OfferTrainingPlace(
            $trainingPosition,
            $this->user,
            $offeringAccount,
        ));

        $this->assertDatabaseHas('training_place_offers', [
            'account_id' => $this->user->id,
            'offered_by' => $offeringAccount->id
        ]);
    }

    /** @test */
    public function itShouldDispatchTheRightNotificationToTheOfferedUser()
    {
        $trainingPosition = TrainingPosition::factory()->create();
        $offeringAccount = factory(Account::class)->create();

        handleService(new OfferTrainingPlace(
            $trainingPosition,
            $this->user,
            $offeringAccount,
        ));

        Notification::assertSentTo([$this->user], TrainingPlaceOffer::class);
    }
}
