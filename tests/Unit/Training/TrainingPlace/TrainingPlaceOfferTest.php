<?php

namespace Tests\Unit\Training\TrainingPlace;

use App\Enums\TrainingPlaceOfferStatus;
use App\Models\Cts\Member;
use App\Models\Mship\Account;
use App\Models\Training\TrainingPlace\TrainingPlaceOffer;
use App\Models\Training\TrainingPosition\TrainingPosition;
use App\Models\Training\WaitingList;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class TrainingPlaceOfferTest extends TestCase
{
    use DatabaseTransactions;

    private function createOffer(array $attributes = []): TrainingPlaceOffer
    {
        $student = Account::factory()->create();
        Member::factory()->create(['cid' => $student->id]);

        $waitingList = WaitingList::factory()->create(['department' => 'atc']);
        $waitingListAccount = $waitingList->addToWaitingList($student, $this->privacc);

        $trainingPosition = TrainingPosition::factory()->create();

        return TrainingPlaceOffer::factory()->create(array_merge([
            'waiting_list_account_id' => $waitingListAccount->id,
            'training_position_id' => $trainingPosition->id,
            'status' => TrainingPlaceOfferStatus::Pending,
            'token' => \Illuminate\Support\Str::random(32),
            'expires_at' => now()->addHours(84),
        ], $attributes));
    }

    #[Test]
    public function it_returns_expired_pending_offers()
    {
        $expiredOffer = $this->createOffer([
            'status' => TrainingPlaceOfferStatus::Pending,
            'expires_at' => now()->subHour(),
        ]);

        $nonExpiredOffer = $this->createOffer([
            'status' => TrainingPlaceOfferStatus::Pending,
            'expires_at' => now()->addHour(),
        ]);

        $results = TrainingPlaceOffer::getExpiredOffers(now());

        $this->assertTrue($results->contains($expiredOffer));
        $this->assertFalse($results->contains($nonExpiredOffer));
    }

    #[Test]
    public function it_does_not_return_already_expired_status_offers_in_expired_query()
    {
        $alreadyExpired = $this->createOffer([
            'status' => TrainingPlaceOfferStatus::Expired,
            'expires_at' => now()->subHour(),
        ]);

        $results = TrainingPlaceOffer::getExpiredOffers(now());

        $this->assertFalse($results->contains($alreadyExpired));
    }
}
