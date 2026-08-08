<?php

declare(strict_types=1);

namespace Tests\Unit\Notifications\Training;

use App\Enums\TrainingPlaceOfferStatus;
use App\Models\Cts\Member;
use App\Models\Mship\Account;
use App\Models\Mship\Qualification;
use App\Models\Training\TrainingPlace\AvailabilityCheck;
use App\Models\Training\TrainingPlace\AvailabilityWarning;
use App\Models\Training\TrainingPlace\TrainingPlace;
use App\Models\Training\TrainingPlace\TrainingPlaceOffer;
use App\Models\Training\WaitingList;
use App\Notifications\DiscordNotificationChannel;
use App\Notifications\Training\AvailabilityWarningCreated;
use App\Notifications\Training\TrainingPlaceOfferDeclined;
use App\Notifications\Training\TrainingPlaceOffered;
use App\Notifications\Training\TrainingPlaceOfferRescinded;
use App\Notifications\Training\TrainingPlaceOfferRescindedAndRemoved;
use App\Notifications\Training\TrainingPlaceRemovedDueToExpiredAvailability;
use App\Notifications\Training\TrainingPlaceRemovedDueToFourthAvailabilityFailure;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class PilotTrainingPlaceNotificationTest extends TestCase
{
    use DatabaseTransactions;

    private Account $account;

    private Qualification $qualification;

    private WaitingList $waitingList;

    private TrainingPlaceOffer $offer;

    private TrainingPlace $trainingPlace;

    private AvailabilityWarning $availabilityWarning;

    protected function setUp(): void
    {
        parent::setUp();

        Event::fake();

        config([
            'training.discord.pilot_training_team_channel_id' => 'pilot-channel-123',
            'training.availability_warning_days.pilot' => 7,
        ]);

        $ctsMember = Member::factory()->create();
        $this->account = Account::factory()->create(['id' => $ctsMember->cid]);

        $this->qualification = Qualification::firstWhere('code', 'PPL')
            ?? Qualification::factory()->create([
                'code' => 'PPL',
                'type' => 'pilot',
                'name_long' => 'Private Pilot Licence',
            ]);

        $this->waitingList = WaitingList::factory()->create([
            'name' => 'P1 Waiting List',
            'department' => WaitingList::PILOT_DEPARTMENT,
        ]);
        $waitingListAccount = $this->waitingList->addToWaitingList($this->account, Account::factory()->create());

        $this->offer = TrainingPlaceOffer::factory()
            ->forQualification($this->qualification)
            ->create([
                'waiting_list_account_id' => $waitingListAccount->id,
                'status' => TrainingPlaceOfferStatus::Pending,
                'token' => Str::random(32),
                'expires_at' => now()->addHours(84),
            ]);

        $this->trainingPlace = TrainingPlace::withoutEvents(fn () => TrainingPlace::factory()
            ->forQualification($this->qualification)
            ->create([
                'waiting_list_account_id' => $waitingListAccount->id,
                'account_id' => $this->account->id,
            ]));

        $availabilityCheck = AvailabilityCheck::factory()->failed()->create([
            'training_place_id' => $this->trainingPlace->id,
        ]);

        $this->availabilityWarning = AvailabilityWarning::factory()->pending()->create([
            'training_place_id' => $this->trainingPlace->id,
            'availability_check_id' => $availabilityCheck->id,
            'expires_at' => now()->addDays(7),
        ]);
    }

    #[Test]
    public function it_renders_the_pilot_offer_view_with_course_name_and_no_atc_wording(): void
    {
        $mail = (new TrainingPlaceOffered($this->offer))->toMail($this->account);
        $html = $this->renderMail($mail);

        $this->assertEquals('emails.training.training_place_offer_pilot', $mail->view);
        $this->assertSame($this->offer->display_name, $mail->viewData['course_name']);
        $this->assertStringContainsString($this->offer->display_name, $html);
        $this->assertStringContainsString('Pilot Training Handbook', $html);
        $this->assertStringContainsString('three to five months', $html);
        $this->assertStringNotContainsString('defer', strtolower($html));
        $this->assertStringNotContainsString('ATC Training', $html);
        $this->assertStringNotContainsString('ATC Training Policy', $html);
        $this->assertStringNotContainsString('ATC Training Handbook', $html);
    }

    #[Test]
    public function it_renders_the_pilot_offer_rescinded_view(): void
    {
        $reason = 'Mentor capacity was withdrawn for this course.';
        $mail = (new TrainingPlaceOfferRescinded($this->offer, $reason))->toMail($this->account);
        $html = $this->renderMail($mail);

        $this->assertEquals('emails.training.training_place_offer_rescinded_pilot', $mail->view);
        $this->assertStringContainsString($this->offer->display_name, $html);
        $this->assertStringContainsString($reason, $html);
        $this->assertStringContainsString('Pilot Training Team', $html);
        $this->assertStringNotContainsString('ATC Training Team', $html);
    }

    #[Test]
    public function it_renders_the_pilot_offer_rescinded_and_removed_view_without_roster_wording(): void
    {
        $mail = (new TrainingPlaceOfferRescindedAndRemoved($this->offer, 'Removed by staff'))->toMail($this->account);
        $html = $this->renderMail($mail);

        $this->assertEquals('emails.training.training_place_offer_rescinded_and_removed_pilot', $mail->view);
        $this->assertStringContainsString('P1 Waiting List', $html);
        $this->assertStringContainsString('Pilot Training Team', $html);
        $this->assertStringContainsString('home member of the VATSIM UK Division', $html);
        $this->assertStringNotContainsString('UK ATC Roster', $html);
        $this->assertStringNotContainsString('ATC Training', $html);
    }

    #[Test]
    public function it_renders_the_pilot_expired_availability_removal_view(): void
    {
        $mail = (new TrainingPlaceRemovedDueToExpiredAvailability($this->availabilityWarning))->toMail($this->account);
        $html = $this->renderMail($mail);

        $this->assertEquals('emails.training.training_place_removed_expired_availability_pilot', $mail->view);
        $this->assertStringContainsString('seven-day period', $html);
        $this->assertStringContainsString('Pilot Training Policy', $html);
        $this->assertStringContainsString(now()->format('d M Y'), $html);
        $this->assertStringNotContainsString('ATC Training Policy', $html);
        $this->assertStringNotContainsString('five-day', $html);
    }

    #[Test]
    public function it_renders_the_pilot_fourth_failure_removal_view(): void
    {
        $mail = (new TrainingPlaceRemovedDueToFourthAvailabilityFailure($this->availabilityWarning))->toMail($this->account);
        $html = $this->renderMail($mail);

        $this->assertEquals('emails.training.training_place_removed_fourth_availability_failure_pilot', $mail->view);
        $this->assertStringContainsString('seven-day period', $html);
        $this->assertStringContainsString('Pilot Training Policy', $html);
        $this->assertStringNotContainsString('ATC Training Policy', $html);
        $this->assertStringNotContainsString('five-day', $html);
    }

    #[Test]
    public function it_renders_the_pilot_availability_warning_view_with_seven_day_window(): void
    {
        $mail = (new AvailabilityWarningCreated($this->availabilityWarning))->toMail($this->account);
        $html = $this->renderMail($mail);

        $this->assertEquals('emails.training.availability_warning_pilot', $mail->view);
        $this->assertSame('7 days', $mail->viewData['availability_window']);
        $this->assertStringContainsString('7 days of this email', $html);
        $this->assertStringContainsString('Pilot Training team', $html);
        $this->assertStringNotContainsString('ATC Training team', $html);
        $this->assertStringNotContainsString('after five days', $html);
    }

    #[Test]
    public function it_routes_pilot_discord_notifications_to_the_configured_pilot_channel(): void
    {
        $declined = new TrainingPlaceOfferDeclined($this->offer);
        $expired = new TrainingPlaceRemovedDueToExpiredAvailability($this->availabilityWarning);
        $fourth = new TrainingPlaceRemovedDueToFourthAvailabilityFailure($this->availabilityWarning);

        $this->assertSame('pilot-channel-123', $declined->getChannel());
        $this->assertSame('pilot-channel-123', $expired->getChannel());
        $this->assertSame('pilot-channel-123', $fourth->getChannel());

        $this->assertContains(DiscordNotificationChannel::class, $declined->via($this->account));
        $this->assertContains(DiscordNotificationChannel::class, $expired->via($this->account));
        $this->assertContains(DiscordNotificationChannel::class, $fourth->via($this->account));

        $discord = $declined->toDiscord($this->account);
        $this->assertStringContainsString($this->offer->display_name, $discord['embeds'][0]['description']);
    }

    private function renderMail(\Illuminate\Notifications\Messages\MailMessage $mail): string
    {
        return view($mail->view, array_merge($mail->viewData, [
            'subject' => $mail->subject,
        ]))->render();
    }
}
