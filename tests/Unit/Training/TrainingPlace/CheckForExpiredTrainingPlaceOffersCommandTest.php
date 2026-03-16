<?php

namespace Tests\Unit\Training\TrainingPlace;

use App\Console\Commands\Training\CheckForExpiredTrainingPlaceOffers;
use App\Enums\TrainingPlaceOfferStatus;
use App\Models\Cts\Member;
use App\Models\Mship\Account;
use App\Models\Training\TrainingPlace\TrainingPlaceOffer;
use App\Models\Training\TrainingPosition\TrainingPosition;
use App\Models\Training\WaitingList;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CheckForExpiredTrainingPlaceOffersCommandTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs($this->privacc);

        Event::fake();
    }

    private function createOffer(array $attributes = []): TrainingPlaceOffer
    {
        $student = Account::factory()->create();
        Member::factory()->create(['cid' => $student->id]);
        $waitingList = WaitingList::factory()->create(['department' => 'atc']);
        $waitingListAccount = $waitingList->addToWaitingList($student, $this->privacc);
        $trainingPosition = TrainingPosition::factory()->withCtsPositions()->create();

        return TrainingPlaceOffer::factory()->create(array_merge([
            'waiting_list_account_id' => $waitingListAccount->id,
            'training_position_id' => $trainingPosition->id,
            'status' => TrainingPlaceOfferStatus::Pending,
            'token' => Str::random(32),
            'expires_at' => now()->subHour(),
        ], $attributes));
    }

    #[Test]
    public function it_expires_pending_offers_past_their_expiry()
    {
        $expiredOffer = $this->createOffer(['expires_at' => now()->subHour()]);

        $this->artisan(CheckForExpiredTrainingPlaceOffers::class)->assertExitCode(0);
        $this->assertEquals(TrainingPlaceOfferStatus::Expired, $expiredOffer->fresh()->status);
    }

    #[Test]
    public function it_does_not_expire_offers_that_have_not_yet_expired()
    {
        $activeOffer = $this->createOffer(['expires_at' => now()->addHour()]);

        $this->artisan(CheckForExpiredTrainingPlaceOffers::class)->assertExitCode(0);
        $this->assertEquals(TrainingPlaceOfferStatus::Pending, $activeOffer->fresh()->status);
    }
}
