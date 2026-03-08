<?php

declare(strict_types=1);

namespace Tests\Unit\Notifications\Training;

use App\Enums\TrainingPlaceOfferStatus;
use App\Models\Atc\Position;
use App\Models\Cts\Member;
use App\Models\Mship\Account;
use App\Models\Training\TrainingPlace\TrainingPlaceOffer;
use App\Models\Training\TrainingPosition\TrainingPosition;
use App\Models\Training\WaitingList;
use App\Notifications\Training\TrainingPlaceOfferRescindedAndRemoved;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class TrainingPlaceOfferRescindedAndRemovedNotificationTest extends TestCase
{
    use DatabaseTransactions;

    private Account $account;
    private TrainingPlaceOffer $offer;
    private WaitingList $waitingList;
    private string $reason = 'No longer able to accommodate a new student at this time.';

    protected function setUp(): void
    {
        parent::setUp();

        Event::fake();

        $ctsMember = Member::factory()->create();
        $this->account = Account::factory()->create(['id' => $ctsMember->cid]);

        $this->waitingList = WaitingList::factory()->create(['department' => 'atc']);
        $waitingListAccount = $this->waitingList->addToWaitingList($this->account, Account::factory()->create());

        $position = Position::factory()->create(['callsign' => 'EGLL_APP', 'name' => 'Heathrow Approach']);
        $trainingPosition = TrainingPosition::factory()->create([
            'position_id' => $position->id,
            'cts_positions' => ['EGLL_APP'],
        ]);

        $this->offer = TrainingPlaceOffer::factory()->create([
            'waiting_list_account_id' => $waitingListAccount->id,
            'training_position_id' => $trainingPosition->id,
            'status' => TrainingPlaceOfferStatus::Rescinded,
            'token' => Str::random(32),
            'expires_at' => now()->addHours(84),
        ]);
    }

    #[Test]
    public function it_sends_via_mail_channel(): void
    {
        $notification = new TrainingPlaceOfferRescindedAndRemoved($this->offer, $this->reason);
        $channels = $notification->via($this->account);

        $this->assertContains('mail', $channels);
    }

    #[Test]
    public function it_generates_mail_message_with_correct_subject(): void
    {
        $notification = new TrainingPlaceOfferRescindedAndRemoved($this->offer, $this->reason);
        $mailMessage = $notification->toMail($this->account);

        $this->assertEquals('UK Training Place Offer Rescinded', $mailMessage->subject);
    }

    #[Test]
    public function it_generates_mail_message_with_correct_view(): void
    {
        $notification = new TrainingPlaceOfferRescindedAndRemoved($this->offer, $this->reason);
        $mailMessage = $notification->toMail($this->account);

        $this->assertEquals('emails.training.training_place_offer_rescinded_and_removed', $mailMessage->view);
    }

    #[Test]
    public function it_generates_mail_message_with_correct_view_data_keys(): void
    {
        $notification = new TrainingPlaceOfferRescindedAndRemoved($this->offer, $this->reason);

        $mailMessage = $notification->toMail($this->account);

        $this->assertArrayHasKey('recipient', $mailMessage->viewData);
        $this->assertArrayHasKey('account', $mailMessage->viewData);
        $this->assertArrayHasKey('waiting_list', $mailMessage->viewData);
    }
}