<?php

namespace Tests\Unit\Training\TrainingPlace;

use App\Enums\TrainingPlaceOfferStatus;
use App\Models\Atc\Position;
use App\Models\Cts\Member;
use App\Models\Mship\Account;
use App\Models\Training\TrainingPlace\TrainingPlaceOffer;
use App\Models\Training\TrainingPosition\TrainingPosition;
use App\Models\Training\WaitingList;
use App\Notifications\Training\TrainingPlaceOffered;
use App\Notifications\Training\TrainingPlaceOfferRescinded;
use App\Notifications\Training\TrainingPlaceOfferRescindedAndRemoved;
use App\Services\Training\TrainingPlaceOfferService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Notification;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class TrainingPlaceOfferServiceTest extends TestCase
{
    use DatabaseTransactions;

    private TrainingPlaceOfferService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = new TrainingPlaceOfferService;
        $this->actingAs($this->privacc);

        Event::fake();
    }

    private function createWaitingListAccount(): WaitingList\WaitingListAccount
    {
        $student = Account::factory()->create();
        Member::factory()->create(['cid' => $student->id]);
        $waitingList = WaitingList::factory()->create(['department' => 'atc']);

        return $waitingList->addToWaitingList($student, $this->privacc);
    }

    private function createOffer(array $attributes = []): TrainingPlaceOffer
    {
        $waitingListAccount = $this->createWaitingListAccount();
        $position = Position::factory()->create();
        $trainingPosition = TrainingPosition::factory()
            ->withCtsPositions(['EGLL_APP'])
            ->create(['position_id' => $position->id]);

        return TrainingPlaceOffer::factory()->create(array_merge([
            'waiting_list_account_id' => $waitingListAccount->id,
            'training_position_id' => $trainingPosition->id,
            'status' => TrainingPlaceOfferStatus::Pending,
            'token' => \Illuminate\Support\Str::random(32),
            'expires_at' => now()->addHours(84),
        ], $attributes));
    }

    #[Test]
    public function it_creates_a_pending_offer_when_offering_a_training_place()
    {
        Notification::fake();

        $waitingListAccount = $this->createWaitingListAccount();
        $trainingPosition = TrainingPosition::factory()->withCtsPositions()->create();

        $this->service->offerTrainingPlace($waitingListAccount, $trainingPosition);

        $this->assertDatabaseHas('training_place_offers', [
            'waiting_list_account_id' => $waitingListAccount->id,
            'training_position_id' => $trainingPosition->id,
            'status' => TrainingPlaceOfferStatus::Pending->value,
        ]);
    }

    #[Test]
    public function it_generates_a_token_when_offering_a_training_place()
    {
        Notification::fake();

        $waitingListAccount = $this->createWaitingListAccount();
        $trainingPosition = TrainingPosition::factory()->withCtsPositions()->create();

        $this->service->offerTrainingPlace($waitingListAccount, $trainingPosition);

        $offer = TrainingPlaceOffer::where('waiting_list_account_id', $waitingListAccount->id)->first();
        $this->assertNotNull($offer->token);
    }

    #[Test]
    public function it_sends_a_notification_to_the_member_when_offering()
    {
        Notification::fake();

        $waitingListAccount = $this->createWaitingListAccount();
        $trainingPosition = TrainingPosition::factory()->withCtsPositions()->create();

        $this->service->offerTrainingPlace($waitingListAccount, $trainingPosition);

        Notification::assertSentTo($waitingListAccount->account, TrainingPlaceOffered::class);
    }

    #[Test]
    public function it_marks_offer_as_accepted()
    {
        $offer = $this->createOffer();
        $this->service->acceptOffer($offer);

        $this->assertEquals(TrainingPlaceOfferStatus::Accepted, $offer->fresh()->status);
    }

    #[Test]
    public function it_sets_response_at_when_accepting()
    {
        $offer = $this->createOffer();
        $this->service->acceptOffer($offer);

        $this->assertNotNull($offer->fresh()->response_at);
    }

    #[Test]
    public function it_creates_a_training_place_when_offer_is_accepted()
    {
        $offer = $this->createOffer();
        $this->service->acceptOffer($offer);

        $this->assertDatabaseHas('training_places', [
            'waiting_list_account_id' => $offer->waiting_list_account_id,
            'training_position_id' => $offer->training_position_id,
        ]);
    }

    #[Test]
    public function it_removes_member_from_waiting_list_when_offer_is_accepted()
    {
        $offer = $this->createOffer();
        $waitingListAccount = $offer->waitingListAccount;

        $this->service->acceptOffer($offer);

        $this->assertNotNull($waitingListAccount->fresh()->deleted_at);
    }

    #[Test]
    public function it_adds_a_note_to_the_account_when_accepting()
    {
        $offer = $this->createOffer();
        $this->service->acceptOffer($offer);

        $this->assertDatabaseHas('mship_account_note', [
            'account_id' => $offer->waitingListAccount->account_id,
        ]);
    }

    #[Test]
    public function it_marks_offer_as_declined()
    {
        $offer = $this->createOffer();
        $this->service->declineOffer($offer);

        $this->assertEquals(TrainingPlaceOfferStatus::Declined, $offer->fresh()->status);
    }

    #[Test]
    public function it_sets_response_at_when_declining()
    {
        $offer = $this->createOffer();
        $this->service->declineOffer($offer);

        $this->assertNotNull($offer->fresh()->response_at);
    }

    #[Test]
    public function it_removes_member_from_waiting_list_when_offer_is_declined()
    {
        $offer = $this->createOffer();
        $waitingListAccount = $offer->waitingListAccount;

        $this->service->declineOffer($offer);

        $this->assertNotNull($waitingListAccount->fresh()->deleted_at);
    }

    #[Test]
    public function it_does_not_create_a_training_place_when_offer_is_declined()
    {
        $offer = $this->createOffer();
        $this->service->declineOffer($offer);

        $this->assertDatabaseMissing('training_places', [
            'waiting_list_account_id' => $offer->waiting_list_account_id,
        ]);
    }

    #[Test]
    public function it_adds_a_note_to_the_account_when_declining()
    {
        $offer = $this->createOffer();
        $this->service->declineOffer($offer);

        $this->assertDatabaseHas('mship_account_note', [
            'account_id' => $offer->waitingListAccount->account_id,
        ]);
    }

    #[Test]
    public function it_marks_offer_as_rescinded()
    {
        Notification::fake();

        $offer = $this->createOffer();
        $this->service->rescindOffer($offer, 'Test reason for rescinding.');

        $this->assertEquals(TrainingPlaceOfferStatus::Rescinded, $offer->fresh()->status);
    }

    #[Test]
    public function it_sends_a_rescinded_notification_to_the_member()
    {
        Notification::fake();

        $offer = $this->createOffer();
        $this->service->rescindOffer($offer, 'Test reason for rescinding.');

        Notification::assertSentTo($offer->waitingListAccount->account, TrainingPlaceOfferRescinded::class);
    }

    #[Test]
    public function it_does_not_send_rescinded_and_removed_notification_when_only_rescinding()
    {
        Notification::fake();

        $offer = $this->createOffer();
        $this->service->rescindOffer($offer, 'Test reason.');

        Notification::assertNotSentTo($offer->waitingListAccount->account, TrainingPlaceOfferRescindedAndRemoved::class);
    }

    #[Test]
    public function it_does_not_remove_member_from_waiting_list_when_rescinding()
    {
        Notification::fake();

        $offer = $this->createOffer();
        $waitingListAccount = $offer->waitingListAccount;

        $this->service->rescindOffer($offer, 'Test reason for rescinding.');

        $this->assertNull($waitingListAccount->fresh()->deleted_at);
    }

    #[Test]
    public function it_adds_a_note_to_the_account_when_rescinding()
    {
        Notification::fake();

        $offer = $this->createOffer();
        $this->service->rescindOffer($offer, 'Test reason for rescinding.');

        $this->assertDatabaseHas('mship_account_note', [
            'account_id' => $offer->waitingListAccount->account_id,
        ]);
    }

    #[Test]
    public function it_marks_offer_as_rescinded_when_rescinding_and_removing()
    {
        Notification::fake();

        $offer = $this->createOffer();
        $this->service->rescindOfferAndRemove($offer, 'Test reason.');

        $this->assertEquals(TrainingPlaceOfferStatus::Rescinded, $offer->fresh()->status);
    }

    #[Test]
    public function it_removes_member_from_waiting_list_when_rescinding_and_removing()
    {
        Notification::fake();

        $offer = $this->createOffer();
        $waitingListAccount = $offer->waitingListAccount;

        $this->service->rescindOfferAndRemove($offer, 'Test reason.');

        $this->assertNotNull($waitingListAccount->fresh()->deleted_at);
    }

    #[Test]
    public function it_sends_rescinded_and_removed_notification_when_rescinding_and_removing()
    {
        Notification::fake();

        $offer = $this->createOffer();
        $this->service->rescindOfferAndRemove($offer, 'Test reason.');

        Notification::assertSentTo($offer->waitingListAccount->account, TrainingPlaceOfferRescindedAndRemoved::class);
    }

    #[Test]
    public function it_does_not_send_plain_rescinded_notification_when_rescinding_and_removing()
    {
        Notification::fake();

        $offer = $this->createOffer();
        $this->service->rescindOfferAndRemove($offer, 'Test reason.');

        Notification::assertNotSentTo($offer->waitingListAccount->account, TrainingPlaceOfferRescinded::class);
    }

    #[Test]
    public function it_adds_a_note_to_the_account_when_rescinding_and_removing()
    {
        Notification::fake();

        $offer = $this->createOffer();
        $this->service->rescindOfferAndRemove($offer, 'Test reason.');

        $this->assertDatabaseHas('mship_account_note', [
            'account_id' => $offer->waitingListAccount->account_id,
        ]);
    }

    #[Test]
    public function it_marks_offer_as_expired()
    {
        $offer = $this->createOffer(['expires_at' => now()->subHour()]);
        $this->service->expireOffer($offer);

        $this->assertEquals(TrainingPlaceOfferStatus::Expired, $offer->fresh()->status);
    }

    #[Test]
    public function it_removes_member_from_waiting_list_when_offer_expires()
    {
        $offer = $this->createOffer(['expires_at' => now()->subHour()]);
        $waitingListAccount = $offer->waitingListAccount;

        $this->service->expireOffer($offer);

        $this->assertNotNull($waitingListAccount->fresh()->deleted_at);
    }

    #[Test]
    public function it_adds_a_note_to_the_account_when_expiring()
    {
        $offer = $this->createOffer(['expires_at' => now()->subHour()]);
        $this->service->expireOffer($offer);

        $this->assertDatabaseHas('mship_account_note', [
            'account_id' => $offer->waitingListAccount->account_id,
        ]);
    }
}
