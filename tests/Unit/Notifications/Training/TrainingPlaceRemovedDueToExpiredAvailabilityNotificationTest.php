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
use App\Notifications\DiscordNotificationChannel;
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
        $trainingPosition = TrainingPosition::factory()->create([
            'position_id' => $position->id,
            'cts_positions' => [],
            'training_team_discord_channel_id' => null, // Default state
        ]);

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

        $this->assertEquals('Attention: Your Training Place Has Been Removed - Availability Check Expired', $mailMessage->subject);
    }

    #[Test]
    public function it_generates_mail_message_with_correct_view_data(): void
    {
        $notification = new TrainingPlaceRemovedDueToExpiredAvailability($this->availabilityWarning);

        $mailMessage = $notification->toMail($this->account);

        $this->assertEquals('emails.training.training_place_removed_expired_availability', $mailMessage->view);
        $this->assertArrayHasKey('recipient', $mailMessage->viewData);
        $this->assertArrayHasKey('training_place_position_name', $mailMessage->viewData);
        $this->assertArrayHasKey('removal_date', $mailMessage->viewData);
    }

    #[Test]
    public function it_includes_position_name_in_view_data(): void
    {
        $notification = new TrainingPlaceRemovedDueToExpiredAvailability($this->availabilityWarning);

        $mailMessage = $notification->toMail($this->account);

        $this->assertEquals('EGLL Tower', $mailMessage->viewData['training_place_position_name']);
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

    #[Test]
    public function it_includes_discord_channel_in_via_if_channel_id_is_set(): void
    {
        $this->availabilityWarning->trainingPlace->trainingPosition->update([
            'training_team_discord_channel_id' => '123456789',
        ]);

        $notification = new TrainingPlaceRemovedDueToExpiredAvailability($this->availabilityWarning);

        $channels = $notification->via($this->account);
        $this->assertContains(DiscordNotificationChannel::class, $channels);
    }

    #[Test]
    public function it_omits_discord_channel_in_via_if_channel_id_is_null_or_empty(): void
    {
        $this->availabilityWarning->trainingPlace->trainingPosition->update([
            'training_team_discord_channel_id' => null,
        ]);

        $notification = new TrainingPlaceRemovedDueToExpiredAvailability($this->availabilityWarning);

        $channels = $notification->via($this->account);
        $this->assertNotContains(DiscordNotificationChannel::class, $channels);
    }

    #[Test]
    public function it_generates_valid_discord_message_payload(): void
    {
        $notification = new TrainingPlaceRemovedDueToExpiredAvailability($this->availabilityWarning);

        $discordData = $notification->toDiscord($this->account);

        $this->assertNull($discordData['content']);
        $this->assertCount(1, $discordData['embeds']);

        $embed = $discordData['embeds'][0];

        $this->assertEquals('Training Place Automatically Removed', $embed['title']);

        $this->assertStringContainsString($this->account->name, $embed['description']);
        $this->assertStringContainsString((string) $this->account->id, $embed['description']);
        $this->assertStringContainsString('failed to resolve a pending availability check', $embed['description']);
        $this->assertCount(1, $embed['fields']);
        $this->assertEquals('Warning Timeline', $embed['fields'][0]['name']);

        $expectedTimeline = '**Issued:** '.$this->availabilityWarning->created_at->format('d/m/Y')."\n".
                            '**Expired:** '.$this->availabilityWarning->expires_at->format('d/m/Y');

        $this->assertEquals($expectedTimeline, $embed['fields'][0]['value']);
        $this->assertFalse($embed['fields'][0]['inline']);
    }
}
