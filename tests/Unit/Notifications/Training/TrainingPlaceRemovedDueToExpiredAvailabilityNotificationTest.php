<?php

declare(strict_types=1);

namespace Tests\Unit\Notifications\Training;

use App\Models\Atc\Position;
use App\Models\Cts\Member;
use App\Models\Mship\Account;
use App\Models\Training\TrainingPlace\AvailabilityCheck;
use App\Models\Training\TrainingPlace\AvailabilityWarning;
use App\Models\Training\TrainingPlace\TrainingPlace;
use App\Models\Training\TrainingPosition\TrainingPosition;
use App\Models\Training\WaitingList;
use App\Notifications\Training\TrainingPlaceRemovedDueToExpiredAvailability;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class TrainingPlaceRemovedDueToExpiredAvailabilityNotificationTest extends TestCase
{
    use DatabaseTransactions;

    private Account $account;

    private AvailabilityWarning $availabilityWarning;

    protected function setUp(): void
    {
        parent::setUp();

        $ctsMember = Member::factory()->create();
        $this->account = Account::factory()->create(['id' => $ctsMember->cid]);

        $waitingList = WaitingList::factory()->create(['name' => 'Test Waiting List']);
        $waitingListAccount = $waitingList->addToWaitingList($this->account, Account::factory()->create());

        $position = Position::factory()->create(['name' => 'EGLL Tower']);
        $trainingPosition = TrainingPosition::factory()->create(['position_id' => $position->id]);

        $trainingPlace = TrainingPlace::factory()->create([
            'waiting_list_account_id' => $waitingListAccount->id,
            'training_position_id' => $trainingPosition->id,
        ]);

        $availabilityCheck = AvailabilityCheck::factory()->failed()->create([
            'training_place_id' => $trainingPlace->id,
        ]);

        $this->availabilityWarning = AvailabilityWarning::factory()->pending()->create([
            'training_place_id' => $trainingPlace->id,
            'availability_check_id' => $availabilityCheck->id,
            'expires_at' => now()->subDay(),
        ]);
    }

    #[Test]
    public function it_sends_via_mail_channel(): void
    {
        $notification = new TrainingPlaceRemovedDueToExpiredAvailability($this->availabilityWarning);

        $channels = $notification->via($this->account);

        $this->assertContains('mail', $channels);
    }

    #[Test]
    public function it_generates_mail_message_with_correct_subject(): void
    {
        $notification = new TrainingPlaceRemovedDueToExpiredAvailability($this->availabilityWarning);

        $mailMessage = $notification->toMail($this->account);

        $this->assertEquals('Training Place Removed - Availability Check Expired', $mailMessage->subject);
    }

    #[Test]
    public function it_generates_mail_message_with_correct_view_data(): void
    {
        $notification = new TrainingPlaceRemovedDueToExpiredAvailability($this->availabilityWarning);

        $mailMessage = $notification->toMail($this->account);

        $this->assertEquals('emails.training.training_place_removed_expired_availability', $mailMessage->view);
        $this->assertArrayHasKey('recipient', $mailMessage->viewData);
        $this->assertArrayHasKey('waiting_list_name', $mailMessage->viewData);
        $this->assertArrayHasKey('position_name', $mailMessage->viewData);
        $this->assertArrayHasKey('removal_date', $mailMessage->viewData);
    }

    #[Test]
    public function it_includes_waiting_list_name_in_view_data(): void
    {
        $notification = new TrainingPlaceRemovedDueToExpiredAvailability($this->availabilityWarning);

        $mailMessage = $notification->toMail($this->account);

        $this->assertEquals('Test Waiting List', $mailMessage->viewData['waiting_list_name']);
    }

    #[Test]
    public function it_includes_position_name_in_view_data(): void
    {
        $notification = new TrainingPlaceRemovedDueToExpiredAvailability($this->availabilityWarning);

        $mailMessage = $notification->toMail($this->account);

        $this->assertEquals('EGLL Tower', $mailMessage->viewData['position_name']);
    }

    #[Test]
    public function it_includes_removal_date_in_view_data(): void
    {
        $notification = new TrainingPlaceRemovedDueToExpiredAvailability($this->availabilityWarning);

        $mailMessage = $notification->toMail($this->account);

        $this->assertEquals(now()->format('d M Y'), $mailMessage->viewData['removal_date']);
    }

    #[Test]
    public function it_includes_correct_recipient_in_view_data(): void
    {
        $notification = new TrainingPlaceRemovedDueToExpiredAvailability($this->availabilityWarning);

        $mailMessage = $notification->toMail($this->account);

        $this->assertEquals($this->account->id, $mailMessage->viewData['recipient']->id);
    }
}
