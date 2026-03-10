<?php

declare(strict_types=1);

namespace Tests\Feature\Training\TrainingPlace;

use App\Console\Commands\Training\CheckForExpiredTrainingPlaceOffers;
use App\Enums\TrainingPlaceOfferStatus;
use App\Models\Atc\Position;
use App\Models\Cts\Member;
use App\Models\Mship\Account;
use App\Models\Training\TrainingPlace\TrainingPlaceOffer;
use App\Models\Training\TrainingPosition\TrainingPosition;
use App\Models\Training\WaitingList;
use App\Models\Training\WaitingList\WaitingListAccount;
use App\Notifications\Training\TrainingPlaceOffered;
use App\Notifications\Training\TrainingPlaceOfferRescinded;
use App\Notifications\Training\TrainingPlaceOfferRescindedAndRemoved;
use App\Services\Training\TrainingPlaceOfferService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Notification;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class TrainingPlaceOfferFlowTest extends TestCase
{
    use DatabaseTransactions;

    private Account $student;
    private WaitingListAccount $waitingListAccount;
    private TrainingPosition $trainingPosition;
    private TrainingPlaceOfferService $service;
    private Carbon $offerTime;

    protected function setUp(): void
    {
        parent::setUp();

        Event::fake();
        Notification::fake();

        $this->offerTime = $this->knownDate->copy()->startOfDay();
        Carbon::setTestNow($this->offerTime);

        $ctsMember = Member::factory()->create();
        $this->student = Account::factory()->create(['id' => $ctsMember->cid]);

        $waitingList = WaitingList::factory()->create(['department' => 'atc']);
        $this->waitingListAccount = $waitingList->addToWaitingList($this->student, $this->privacc);

        $position = Position::factory()->create();
        $this->trainingPosition = TrainingPosition::factory()
            ->withCtsPositions(['EGLL_APP'])
            ->create(['position_id' => $position->id]);

        $this->service = app(TrainingPlaceOfferService::class);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    #[Test]
    public function member_accepts_offer_within_window(): void
    {
        $this->actingAs($this->privacc);
        $this->service->offerTrainingPlace($this->waitingListAccount, $this->trainingPosition);

        $offer = TrainingPlaceOffer::where('waiting_list_account_id', $this->waitingListAccount->id)->firstOrFail();
        $this->assertEquals(TrainingPlaceOfferStatus::Pending, $offer->status);
        Notification::assertSentTo($this->student, TrainingPlaceOffered::class);

        Carbon::setTestNow($this->offerTime->copy()->addHours(24));

        $this->actingAs($this->student)
            ->get(route('mship.waiting-lists.training-place-offer.accept', $offer->token))
            ->assertOk()
            ->assertViewHas('result', 'accepted');

        $this->assertEquals(TrainingPlaceOfferStatus::Accepted, $offer->fresh()->status);
        $this->assertNotNull($offer->fresh()->response_at);

        $this->assertDatabaseHas('training_places', [
            'waiting_list_account_id' => $this->waitingListAccount->id,
            'training_position_id' => $this->trainingPosition->id,
        ]);
        $this->assertNotNull($this->waitingListAccount->fresh()->deleted_at);

        Carbon::setTestNow($this->offerTime->copy()->addHours(90));
        $this->artisan(CheckForExpiredTrainingPlaceOffers::class)->assertExitCode(0);
        $this->assertEquals(TrainingPlaceOfferStatus::Accepted, $offer->fresh()->status);
    }

    #[Test]
    public function member_declines_offer_within_window(): void
    {
        $this->actingAs($this->privacc);
        $this->service->offerTrainingPlace($this->waitingListAccount, $this->trainingPosition);

        $offer = TrainingPlaceOffer::where('waiting_list_account_id', $this->waitingListAccount->id)->firstOrFail();

        Carbon::setTestNow($this->offerTime->copy()->addHours(12));

        $this->actingAs($this->student)
            ->get(route('mship.waiting-lists.training-place-offer.decline', $offer->token))
            ->assertOk()
            ->assertViewHas('result', 'declined');

        $this->assertEquals(TrainingPlaceOfferStatus::Declined, $offer->fresh()->status);
        $this->assertNotNull($offer->fresh()->response_at);

        $this->assertDatabaseMissing('training_places', [
            'waiting_list_account_id' => $this->waitingListAccount->id,
        ]);
        $this->assertNotNull($this->waitingListAccount->fresh()->deleted_at);
    }

    #[Test]
    public function offer_expires_after_84_hours_and_member_is_removed(): void
    {
        $this->actingAs($this->privacc);
        $this->service->offerTrainingPlace($this->waitingListAccount, $this->trainingPosition);

        $offer = TrainingPlaceOffer::where('waiting_list_account_id', $this->waitingListAccount->id)->firstOrFail();
        $expiresAt = $offer->expires_at->copy();

        Carbon::setTestNow($this->offerTime->copy()->addHours(42));
        $this->artisan(CheckForExpiredTrainingPlaceOffers::class)->assertExitCode(0);
        $this->assertEquals(TrainingPlaceOfferStatus::Pending, $offer->fresh()->status);
        $this->assertNull($this->waitingListAccount->fresh()->deleted_at, 'Member should still be on waiting list mid-window');

        Carbon::setTestNow($expiresAt->copy()->subMinute());
        $this->artisan(CheckForExpiredTrainingPlaceOffers::class)->assertExitCode(0);
        $this->assertEquals(TrainingPlaceOfferStatus::Pending, $offer->fresh()->status);
        $this->assertNull($this->waitingListAccount->fresh()->deleted_at);

        Carbon::setTestNow($expiresAt->copy()->addMinute());
        $this->artisan(CheckForExpiredTrainingPlaceOffers::class)->assertExitCode(0);

        $this->assertEquals(TrainingPlaceOfferStatus::Expired, $offer->fresh()->status);
        $this->assertNotNull($this->waitingListAccount->fresh()->deleted_at, 'Member should be removed after offer expires');

        $this->actingAs($this->student)
            ->get(route('mship.waiting-lists.training-place-offer.accept', $offer->token))
            ->assertOk()
            ->assertViewIs('training.training-place-offer.expired');
    }

    #[Test]
    public function staff_can_rescind_offer_and_member_retains_waiting_list_position(): void
    {
        $this->actingAs($this->privacc);
        $this->service->offerTrainingPlace($this->waitingListAccount, $this->trainingPosition);
        $offer = TrainingPlaceOffer::where('waiting_list_account_id', $this->waitingListAccount->id)->firstOrFail();

        Carbon::setTestNow($this->offerTime->copy()->addHours(12));
        $this->service->rescindOffer($offer, 'Unable to accommodate at this time.');

        $this->assertEquals(TrainingPlaceOfferStatus::Rescinded, $offer->fresh()->status);
        $this->assertNull($this->waitingListAccount->fresh()->deleted_at, 'Member should remain on waiting list after rescind');
        Notification::assertSentTo($this->student, TrainingPlaceOfferRescinded::class);

        $this->actingAs($this->student)
            ->get(route('mship.waiting-lists.training-place-offer.accept', $offer->token))
            ->assertOk()
            ->assertViewIs('training.training-place-offer.already-responded');
    }

    #[Test]
    public function staff_can_rescind_offer_and_remove_member_from_waiting_list(): void
    {
        $this->actingAs($this->privacc);
        $this->service->offerTrainingPlace($this->waitingListAccount, $this->trainingPosition);
        $offer = TrainingPlaceOffer::where('waiting_list_account_id', $this->waitingListAccount->id)->firstOrFail();

        Carbon::setTestNow($this->offerTime->copy()->addHours(12));
        $this->service->rescindOfferAndRemove($offer, 'Member has been removed for inactivity.');

        $this->assertEquals(TrainingPlaceOfferStatus::Rescinded, $offer->fresh()->status);
        $this->assertNotNull($this->waitingListAccount->fresh()->deleted_at, 'Member should be removed from waiting list');
        Notification::assertSentTo($this->student, TrainingPlaceOfferRescindedAndRemoved::class);
        Notification::assertNotSentTo($this->student, TrainingPlaceOfferRescinded::class);
    }
}