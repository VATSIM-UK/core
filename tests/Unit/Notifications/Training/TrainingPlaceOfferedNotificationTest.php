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
use App\Notifications\Training\TrainingPlaceOffered;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class TrainingPlaceOfferedNotificationTest extends TestCase
{
    use DatabaseTransactions;

    private Account $account;
    private TrainingPlaceOffer $offer;

    protected function setUp(): void
    {
        parent::setUp();

        Event::fake();

        $ctsMember = Member::factory()->create();
        $this->account = Account::factory()->create(['id' => $ctsMember->cid]);

        $waitingList = WaitingList::factory()->create(['department' => 'atc']);
        $waitingListAccount = $waitingList->addToWaitingList($this->account, Account::factory()->create());

        $position = Position::factory()->create(['callsign' => 'EGLL_APP', 'name' => 'Heathrow Approach']);
        $trainingPosition = TrainingPosition::factory()->create([
            'position_id' => $position->id,
            'cts_positions' => ['EGLL_APP'],
        ]);

        $this->offer = TrainingPlaceOffer::factory()->create([
            'waiting_list_account_id' => $waitingListAccount->id,
            'training_position_id' => $trainingPosition->id,
            'status' => TrainingPlaceOfferStatus::Pending,
            'token' => Str::random(32),
            'expires_at' => now()->addHours(84),
        ]);
    }

    #[Test]
    public function it_sends_via_mail_channel(): void
    {
        $notification = new TrainingPlaceOffered($this->offer);
        $channels = $notification->via($this->account);

        $this->assertContains('mail', $channels);
    }

    #[Test]
    public function it_generates_mail_message_with_correct_subject(): void
    {
        $notification = new TrainingPlaceOffered($this->offer);
        $mailMessage = $notification->toMail($this->account);

        $this->assertEquals('UK Training Place Offer', $mailMessage->subject);
    }

    #[Test]
    public function it_generates_mail_message_with_correct_view(): void
    {
        $notification = new TrainingPlaceOffered($this->offer);
        $mailMessage = $notification->toMail($this->account);

        $this->assertEquals('emails.training.training_place_offer', $mailMessage->view);
    }

    #[Test]
    public function it_generates_mail_message_with_correct_view_data_keys(): void
    {
        $notification = new TrainingPlaceOffered($this->offer);
        $mailMessage = $notification->toMail($this->account);

        $this->assertArrayHasKey('recipient', $mailMessage->viewData);
        $this->assertArrayHasKey('offer', $mailMessage->viewData);
        $this->assertArrayHasKey('account', $mailMessage->viewData);
        $this->assertArrayHasKey('position', $mailMessage->viewData);
        $this->assertArrayHasKey('accept_url', $mailMessage->viewData);
        $this->assertArrayHasKey('decline_url', $mailMessage->viewData);
    }

    #[Test]
    public function it_includes_accept_url_in_view_data(): void
    {
        $notification = new TrainingPlaceOffered($this->offer);

        $mailMessage = $notification->toMail($this->account);

        $this->assertStringContainsString($this->offer->token, $mailMessage->viewData['accept_url']);
        $this->assertStringContainsString('accept', $mailMessage->viewData['accept_url']);
    }

    #[Test]
    public function it_includes_decline_url_in_view_data(): void
    {
        $notification = new TrainingPlaceOffered($this->offer);

        $mailMessage = $notification->toMail($this->account);

        $this->assertStringContainsString($this->offer->token, $mailMessage->viewData['decline_url']);
        $this->assertStringContainsString('decline', $mailMessage->viewData['decline_url']);
    }
}