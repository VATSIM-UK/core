<?php

namespace Tests\Feature\Training\TrainingPlace;

use App\Enums\TrainingPlaceOfferStatus;
use App\Models\Cts\Member;
use App\Models\Mship\Account;
use App\Models\Training\TrainingPlace\TrainingPlaceOffer;
use App\Models\Training\TrainingPosition\TrainingPosition;
use App\Models\Training\WaitingList;
use App\Services\Training\TrainingPlaceService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Illuminate\Support\Facades\Event;
use App\Models\Atc\Position;

class TrainingPlaceOfferControllerTest extends TestCase
{
    use DatabaseTransactions;

    private Account $student;
    private TrainingPlaceOffer $offer;

    protected function setUp(): void
    {
        parent::setUp();

        Event::fake();

        $this->student = Account::factory()->create();
        Member::factory()->create(['cid' => $this->student->id]);

        $waitingList = WaitingList::factory()->create(['department' => 'atc']);
        $waitingListAccount = $waitingList->addToWaitingList($this->student, $this->privacc);
        
        $position = Position::factory()->create();
        $trainingPosition = TrainingPosition::factory()
            ->withCtsPositions(['EGLL_APP'])
            ->create(['position_id' => $position->id]);

        $this->offer = TrainingPlaceOffer::factory()->create([
            'waiting_list_account_id' => $waitingListAccount->id,
            'training_position_id' => $trainingPosition->id,
            'status' => TrainingPlaceOfferStatus::Pending,
            'token' => Str::random(32),
            'expires_at' => now()->addHours(84),
        ]);
    }

    #[Test]
    public function member_can_accept_their_offer()
    {
        $this->actingAs($this->student)
            ->get(route('mship.waiting-lists.training-place-offer.accept', $this->offer->token))
            ->assertOk()
            ->assertViewIs('training.training-place-offer.result');

        $this->assertEquals(TrainingPlaceOfferStatus::Accepted, $this->offer->fresh()->status);
    }

    #[Test]
    public function accept_shows_accepted_result_view()
    {
        $this->actingAs($this->student)
            ->get(route('mship.waiting-lists.training-place-offer.accept', $this->offer->token))
            ->assertOk()
            ->assertViewHas('result', 'accepted');
    }

    #[Test]
    public function another_member_cannot_accept_someone_elses_offer()
    {
        $otherMember = Account::factory()->create();
        Member::factory()->create(['cid' => $otherMember->id]);

        $this->actingAs($otherMember)
            ->get(route('mship.waiting-lists.training-place-offer.accept', $this->offer->token))
            ->assertForbidden();
    }

    #[Test]
    public function member_can_decline_their_offer()
    {
        $this->actingAs($this->student)
            ->get(route('mship.waiting-lists.training-place-offer.decline', $this->offer->token))
            ->assertOk()
            ->assertViewIs('training.training-place-offer.result');

        $this->assertEquals(TrainingPlaceOfferStatus::Declined, $this->offer->fresh()->status);
    }

    #[Test]
    public function decline_shows_declined_result_view()
    {
        $this->actingAs($this->student)
            ->get(route('mship.waiting-lists.training-place-offer.decline', $this->offer->token))
            ->assertOk()
            ->assertViewHas('result', 'declined');
    }

    #[Test]
    public function another_member_cannot_decline_someone_elses_offer()
    {
        $otherMember = Account::factory()->create();
        Member::factory()->create(['cid' => $otherMember->id]);

        $this->actingAs($otherMember)
            ->get(route('mship.waiting-lists.training-place-offer.decline', $this->offer->token))
            ->assertForbidden();
    }


    #[Test]
    public function declining_removes_member_from_waiting_list()
    {
        $waitingListAccount = $this->offer->waitingListAccount;

        $this->actingAs($this->student)
            ->get(route('mship.waiting-lists.training-place-offer.decline', $this->offer->token));

        $this->assertNotNull($waitingListAccount->fresh()->deleted_at);
    }
}