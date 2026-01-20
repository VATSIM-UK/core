<?php

namespace Tests\Unit\Notifications\Training;

use App\Models\Cts\Member;
use App\Models\Mship\Account;
use App\Models\Training\TrainingPlace\AvailabilityCheck;
use App\Models\Training\TrainingPlace\AvailabilityWarning;
use App\Models\Training\TrainingPlace\TrainingPlace;
use App\Models\Training\WaitingList;
use App\Notifications\Training\AvailabilityWarningCreated;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AvailabilityWarningCreatedNotificationTest extends TestCase
{
    use DatabaseTransactions;

    private Account $account;

    private AvailabilityWarning $availabilityWarning;

    protected function setUp(): void
    {
        parent::setUp();

        // Create CTS member and account
        $ctsMember = Member::factory()->create();
        $this->account = Account::factory()->create(['id' => $ctsMember->cid]);

        // Create a waiting list and add the account to it
        $waitingList = WaitingList::factory()->create(['name' => 'Test Waiting List']);
        $waitingListAccount = $waitingList->addToWaitingList($this->account, Account::factory()->create());

        // Create a training place
        $trainingPlace = TrainingPlace::factory()->create([
            'waiting_list_account_id' => $waitingListAccount->id,
        ]);

        // Create an availability check and warning
        $availabilityCheck = AvailabilityCheck::factory()->failed()->create([
            'training_place_id' => $trainingPlace->id,
        ]);

        $this->availabilityWarning = AvailabilityWarning::factory()->pending()->create([
            'training_place_id' => $trainingPlace->id,
            'availability_check_id' => $availabilityCheck->id,
            'expires_at' => now()->addDays(5),
        ]);
    }

    #[Test]
    public function it_sends_via_mail_channel(): void
    {
        $notification = new AvailabilityWarningCreated($this->availabilityWarning);

        $channels = $notification->via($this->account);

        $this->assertContains('mail', $channels);
    }

    #[Test]
    public function it_generates_mail_message_with_correct_subject(): void
    {
        $notification = new AvailabilityWarningCreated($this->availabilityWarning);

        $mailMessage = $notification->toMail($this->account);

        $this->assertEquals('Action Required: Update Your Availability', $mailMessage->subject);
    }

    #[Test]
    public function it_generates_mail_message_with_correct_view_data(): void
    {
        $notification = new AvailabilityWarningCreated($this->availabilityWarning);

        $mailMessage = $notification->toMail($this->account);

        $this->assertEquals('emails.training.availability_warning', $mailMessage->view);
        $this->assertArrayHasKey('recipient', $mailMessage->viewData);
        $this->assertArrayHasKey('waiting_list_name', $mailMessage->viewData);
        $this->assertArrayHasKey('expires_at', $mailMessage->viewData);
        $this->assertArrayHasKey('days_to_expire', $mailMessage->viewData);
    }

    #[Test]
    public function it_includes_waiting_list_name_in_view_data(): void
    {
        $notification = new AvailabilityWarningCreated($this->availabilityWarning);

        $mailMessage = $notification->toMail($this->account);

        $this->assertEquals('Test Waiting List', $mailMessage->viewData['waiting_list_name']);
    }

    #[Test]
    public function it_includes_expiry_date_in_view_data(): void
    {
        $notification = new AvailabilityWarningCreated($this->availabilityWarning);

        $mailMessage = $notification->toMail($this->account);

        $this->assertEquals(
            $this->availabilityWarning->expires_at->format('Y-m-d H:i:s'),
            $mailMessage->viewData['expires_at']->format('Y-m-d H:i:s')
        );
    }

    #[Test]
    public function it_calculates_days_to_expire_correctly(): void
    {
        $notification = new AvailabilityWarningCreated($this->availabilityWarning);

        $mailMessage = $notification->toMail($this->account);

        $expectedDays = now()->diffInDays($this->availabilityWarning->expires_at, false);
        $this->assertEquals($expectedDays, $mailMessage->viewData['days_to_expire']);
    }

    #[Test]
    public function it_includes_correct_recipient_in_view_data(): void
    {
        $notification = new AvailabilityWarningCreated($this->availabilityWarning);

        $mailMessage = $notification->toMail($this->account);

        $this->assertEquals($this->account->id, $mailMessage->viewData['recipient']->id);
    }
}
