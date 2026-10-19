<?php

declare(strict_types=1);

namespace Tests\Unit\Training\Seminar;

use App\Enums\SeminarInvitationStatus;
use App\Enums\SeminarTheoryReminderStatus;
use App\Models\Cts\Member;
use App\Models\Cts\TheoryResult;
use App\Models\Mship\Account;
use App\Models\Training\Seminar\Seminar;
use App\Models\Training\Seminar\SeminarAttendee;
use App\Models\Training\Seminar\SeminarInvitation;
use App\Models\Training\WaitingList;
use App\Models\Training\WaitingList\RemovalReason;
use App\Models\Training\WaitingList\WaitingListAccount;
use App\Models\Training\WaitingList\WaitingListTheoryReminder;
use App\Notifications\Training\SeminarInvitationNotification;
use App\Notifications\Training\SeminarTheoryExamReminderNotification;
use App\Services\Training\SeminarInvitationService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Notifications\ChannelManager;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use PHPUnit\Framework\Attributes\Test;
use RuntimeException;
use Tests\TestCase;

class SeminarInvitationServiceTest extends TestCase
{
    use DatabaseTransactions;

    private SeminarInvitationService $service;

    private Seminar $seminar;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = app(SeminarInvitationService::class);
        Event::fake();
    }

    private function setUpSeminar(int $capacity = 10, bool $automatic = false, int $expiryHours = 168): void
    {
        $waitingList = WaitingList::factory()->create([
            'department' => 'atc',
            'cts_theory_exam_level' => 'S1',
        ]);

        $this->seminar = Seminar::factory()->create([
            'waiting_list_id' => $waitingList->id,
            'capacity' => $capacity,
            'invitation_expiry_hours' => $expiryHours,
            'automatic_invitations_enabled' => $automatic,
            'created_by' => $this->privacc->id,
        ]);
    }

    private function addToWaitingList(bool $theoryExamPassed = true): WaitingListAccount
    {
        $student = Account::factory()->create();
        $waitingListAccount = $this->seminar->waitingList->addToWaitingList($student, $this->privacc);

        if ($theoryExamPassed) {
            $this->passTheoryExam($waitingListAccount);
        }

        return $waitingListAccount;
    }

    private function passTheoryExam(WaitingListAccount $waitingListAccount): void
    {
        $member = Member::factory()->create(['cid' => $waitingListAccount->account_id]);

        TheoryResult::create([
            'exam' => 'S1',
            'student_id' => $member->id,
            'pass' => 1,
            'questions' => 40,
            'time_mins' => 60,
            'passmark' => 75,
            'correct' => 35,
            'started' => now()->subDay(),
            'submitted' => 1,
            'submitted_time' => now()->subDay(),
        ]);
    }

    /**
     * Simulate a student who was reminded instead of invited when an earlier seminar was filled.
     */
    private function remindAtAnEarlierSeminar(WaitingListAccount $waitingListAccount): WaitingListTheoryReminder
    {
        $earlierSeminar = Seminar::factory()->create([
            'waiting_list_id' => $this->seminar->waiting_list_id,
            'capacity' => 5,
            'created_by' => $this->privacc->id,
        ]);

        return WaitingListTheoryReminder::create([
            'waiting_list_account_id' => $waitingListAccount->id,
            'account_id' => $waitingListAccount->account_id,
            'seminar_id' => $earlierSeminar->id,
            'reminded_at' => now()->subDays(7),
            'status' => SeminarTheoryReminderStatus::Pending,
        ]);
    }

    #[Test]
    public function top_up_returns_0_when_automatic_invitations_disabled(): void
    {
        $this->setUpSeminar(10, false);

        $result = $this->service->topUpAutomaticInvitations($this->seminar);

        $this->assertSame(0, $result);
    }

    #[Test]
    public function top_up_returns_0_when_seminar_is_closed(): void
    {
        $this->setUpSeminar(10, true);
        $this->seminar->update(['closed_at' => now()]);
        $this->seminar->refresh();

        $result = $this->service->topUpAutomaticInvitations($this->seminar);

        $this->assertSame(0, $result);
    }

    #[Test]
    public function top_up_returns_0_when_no_spaces_remaining(): void
    {
        $this->setUpSeminar(1, true);
        $student = Account::factory()->create();
        $this->seminar->attendees()->create([
            'account_id' => $student->id,
            'added_at' => now(),
        ]);
        $this->seminar->refresh();

        $result = $this->service->topUpAutomaticInvitations($this->seminar);

        $this->assertSame(0, $result);
    }

    #[Test]
    public function top_up_invites_eligible_students_up_to_spaces_remaining(): void
    {
        $this->setUpSeminar(3, true);
        $account1 = $this->addToWaitingList(true);
        $account2 = $this->addToWaitingList(true);
        $account3 = $this->addToWaitingList(true);
        $this->seminar = $this->seminar->fresh();

        $result = $this->service->topUpAutomaticInvitations($this->seminar);

        $this->assertSame(3, $result);
        $this->assertDatabaseHas('training_seminar_invitations', [
            'seminar_id' => $this->seminar->id,
            'account_id' => $account1->account_id,
            'status' => SeminarInvitationStatus::Sent->value,
        ]);
        $this->assertDatabaseHas('training_seminar_invitations', [
            'seminar_id' => $this->seminar->id,
            'account_id' => $account2->account_id,
            'status' => SeminarInvitationStatus::Sent->value,
        ]);
        $this->assertDatabaseHas('training_seminar_invitations', [
            'seminar_id' => $this->seminar->id,
            'account_id' => $account3->account_id,
            'status' => SeminarInvitationStatus::Sent->value,
        ]);
    }

    #[Test]
    public function top_up_skips_students_who_have_not_passed_theory_exam(): void
    {
        $this->setUpSeminar(5, true);
        $this->addToWaitingList(false);
        $theoryPassed = $this->addToWaitingList(true);
        $this->seminar = $this->seminar->fresh();

        $result = $this->service->topUpAutomaticInvitations($this->seminar);

        $this->assertSame(1, $result);
        $this->assertDatabaseHas('training_seminar_invitations', [
            'seminar_id' => $this->seminar->id,
            'account_id' => $theoryPassed->account_id,
        ]);
    }

    #[Test]
    public function top_up_skips_students_already_invited(): void
    {
        $this->setUpSeminar(5, true);
        $alreadyInvited = $this->addToWaitingList(true);

        SeminarInvitation::factory()->create([
            'seminar_id' => $this->seminar->id,
            'account_id' => $alreadyInvited->account_id,
            'waiting_list_account_id' => $alreadyInvited->id,
        ]);

        $newInvite = $this->addToWaitingList(true);
        $this->seminar = $this->seminar->fresh();

        $result = $this->service->topUpAutomaticInvitations($this->seminar);

        $this->assertSame(1, $result);
        $this->assertDatabaseHas('training_seminar_invitations', [
            'seminar_id' => $this->seminar->id,
            'account_id' => $newInvite->account_id,
        ]);
    }

    #[Test]
    public function invite_next_invites_up_to_target_count(): void
    {
        $this->setUpSeminar(10);
        $account1 = $this->addToWaitingList(true);
        $account2 = $this->addToWaitingList(true);
        $account3 = $this->addToWaitingList(true);
        $this->seminar = $this->seminar->fresh();

        $result = $this->service->inviteNextEligible($this->seminar, 2);

        $this->assertSame(2, $result);
        $this->assertDatabaseHas('training_seminar_invitations', [
            'seminar_id' => $this->seminar->id,
            'account_id' => $account1->account_id,
        ]);
        $this->assertDatabaseHas('training_seminar_invitations', [
            'seminar_id' => $this->seminar->id,
            'account_id' => $account2->account_id,
        ]);
        $this->assertDatabaseMissing('training_seminar_invitations', [
            'seminar_id' => $this->seminar->id,
            'account_id' => $account3->account_id,
        ]);
    }

    #[Test]
    public function invite_next_skips_non_theory_passed_students(): void
    {
        $this->setUpSeminar(10);
        $this->addToWaitingList(false);
        $account2 = $this->addToWaitingList(true);
        $this->seminar = $this->seminar->fresh();

        $result = $this->service->inviteNextEligible($this->seminar, 1);

        $this->assertSame(1, $result);
        $this->assertDatabaseHas('training_seminar_invitations', [
            'seminar_id' => $this->seminar->id,
            'account_id' => $account2->account_id,
        ]);
    }

    #[Test]
    public function invite_next_skips_already_invited_students(): void
    {
        $this->setUpSeminar(10);
        $account1 = $this->addToWaitingList(true);

        SeminarInvitation::factory()->create([
            'seminar_id' => $this->seminar->id,
            'account_id' => $account1->account_id,
        ]);

        $account2 = $this->addToWaitingList(true);
        $this->seminar = $this->seminar->fresh();

        $result = $this->service->inviteNextEligible($this->seminar, 1);

        $this->assertSame(1, $result);
        $this->assertDatabaseHas('training_seminar_invitations', [
            'seminar_id' => $this->seminar->id,
            'account_id' => $account2->account_id,
        ]);
    }

    #[Test]
    public function invite_next_does_not_over_invite(): void
    {
        $this->setUpSeminar(10);
        $this->addToWaitingList(true);
        $this->addToWaitingList(true);
        $this->seminar = $this->seminar->fresh();

        $result = $this->service->inviteNextEligible($this->seminar, 1);

        $this->assertSame(1, $result);
        $this->assertCount(1, $this->seminar->fresh()->invitations);
    }

    #[Test]
    public function create_invitation_throws_when_seminar_is_closed(): void
    {
        $this->setUpSeminar(5);
        $this->seminar->update(['closed_at' => now()]);
        $this->seminar->refresh();
        $account = Account::factory()->create();

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Seminar admissions are closed.');

        $this->service->createInvitation($this->seminar, $account);
    }

    #[Test]
    public function create_invitation_throws_when_within_thirty_minutes_of_seminar_start(): void
    {
        $this->setUpSeminar(5);
        $start = now()->addMinutes(20);
        $this->seminar->update([
            'date' => $start->format('Y-m-d'),
            'from' => $start->format('H:i:s'),
        ]);
        $this->seminar = $this->seminar->fresh();
        $account = Account::factory()->create();

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Seminar admissions are closed.');

        $this->service->createInvitation($this->seminar, $account);
    }

    #[Test]
    public function top_up_returns_0_when_within_thirty_minutes_of_seminar_start(): void
    {
        $this->setUpSeminar(10, true);
        $start = now()->addMinutes(20);
        $this->seminar->update([
            'date' => $start->format('Y-m-d'),
            'from' => $start->format('H:i:s'),
        ]);
        $this->seminar = $this->seminar->fresh();

        $result = $this->service->topUpAutomaticInvitations($this->seminar);

        $this->assertSame(0, $result);
    }

    #[Test]
    public function create_invitation_throws_when_at_capacity(): void
    {
        $this->setUpSeminar(1);
        $student = Account::factory()->create();
        $this->seminar->attendees()->create([
            'account_id' => $student->id,
            'added_at' => now(),
        ]);
        $this->seminar->refresh();

        $student2 = Account::factory()->create();

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Seminar has reached its invite capacity.');

        $this->service->createInvitation($this->seminar, $student2);
    }

    #[Test]
    public function create_invitation_creates_invitation_with_correct_attributes(): void
    {
        Notification::fake();

        $this->setUpSeminar(5);
        $waitingListAccount = $this->addToWaitingList(true);
        $account = Account::find($waitingListAccount->account_id);
        $this->seminar = $this->seminar->fresh();

        $invitation = $this->service->createInvitation($this->seminar, $account, $waitingListAccount->id);

        $this->assertDatabaseHas('training_seminar_invitations', [
            'id' => $invitation->id,
            'seminar_id' => $this->seminar->id,
            'account_id' => $account->id,
            'waiting_list_account_id' => $waitingListAccount->id,
            'status' => SeminarInvitationStatus::Sent->value,
        ]);
        $this->assertNotNull($invitation->sent_at);
        $this->assertNotNull($invitation->expires_at);
    }

    #[Test]
    public function create_invitation_uses_hours_for_expiry(): void
    {
        Notification::fake();

        $this->setUpSeminar(5, false, 48);
        $account = Account::factory()->create();
        $this->seminar = $this->seminar->fresh();

        $invitation = $this->service->createInvitation($this->seminar, $account);

        $this->assertEqualsWithDelta(
            48,
            $invitation->sent_at->diffInHours($invitation->expires_at),
            0.01
        );
    }

    #[Test]
    public function create_invitation_caps_expiry_at_thirty_minutes_before_seminar_start(): void
    {
        Notification::fake();

        $this->setUpSeminar(5, false, 168);
        $start = now()->addDays(3)->setTime(10, 0);
        $this->seminar->update([
            'date' => $start->format('Y-m-d'),
            'from' => '10:00',
        ]);
        $this->seminar = $this->seminar->fresh();
        $account = Account::factory()->create();

        $invitation = $this->service->createInvitation($this->seminar, $account);

        $this->assertEquals(
            $this->seminar->admissionsCloseAt()->timestamp,
            $invitation->expires_at->timestamp
        );
        $this->assertEqualsWithDelta(
            30 * 60,
            $invitation->expires_at->diffInSeconds($this->seminar->startsAt()),
            1
        );
    }

    #[Test]
    public function create_invitation_is_automatically_short_notice_when_seminar_starts_within_threshold(): void
    {
        Notification::fake();

        $this->setUpSeminar(5);
        $this->seminar->update(['date' => now()->addDays(2)->format('Y-m-d')]);
        $this->seminar = $this->seminar->fresh();
        $account = Account::factory()->create();

        $invitation = $this->service->createInvitation($this->seminar, $account);

        $this->assertTrue($invitation->fresh()->is_short_notice);
    }

    #[Test]
    public function create_invitation_is_not_short_notice_when_seminar_is_beyond_threshold(): void
    {
        Notification::fake();

        $this->setUpSeminar(5);
        $account = Account::factory()->create();
        $this->seminar = $this->seminar->fresh();

        $invitation = $this->service->createInvitation($this->seminar, $account);

        $this->assertFalse($invitation->fresh()->is_short_notice);
    }

    #[Test]
    public function create_invitation_allows_sending_inside_old_expiry_window(): void
    {
        Notification::fake();

        $this->setUpSeminar(5);
        $this->seminar->update(['date' => now()->addDays(1)->format('Y-m-d')]);
        $this->seminar = $this->seminar->fresh();
        $account = Account::factory()->create();

        $invitation = $this->service->createInvitation($this->seminar, $account);

        $this->assertDatabaseHas('training_seminar_invitations', [
            'id' => $invitation->id,
            'is_short_notice' => true,
        ]);
    }

    #[Test]
    public function create_invitation_sends_notification_to_account(): void
    {
        Notification::fake();

        $this->setUpSeminar(5);
        $account = Account::factory()->create();
        $this->seminar = $this->seminar->fresh();

        $this->service->createInvitation($this->seminar, $account);

        Notification::assertSentTo($account, SeminarInvitationNotification::class);
    }

    #[Test]
    public function create_invitation_generates_a_unique_token(): void
    {
        $this->setUpSeminar(5);
        $account1 = Account::factory()->create();
        $account2 = Account::factory()->create();
        $this->seminar = $this->seminar->fresh();

        Notification::fake();

        $invitation1 = $this->service->createInvitation($this->seminar, $account1);
        $invitation2 = $this->service->createInvitation($this->seminar, $account2);

        $this->assertNotNull($invitation1->token);
        $this->assertNotNull($invitation2->token);
        $this->assertNotEquals($invitation1->token, $invitation2->token);
    }

    #[Test]
    public function accept_updates_status_to_attending(): void
    {
        $invitation = SeminarInvitation::factory()->create();

        $this->service->accept($invitation);

        $this->assertEquals(SeminarInvitationStatus::Attending, $invitation->fresh()->status);
    }

    #[Test]
    public function accept_sets_responded_at_timestamp(): void
    {
        $invitation = SeminarInvitation::factory()->create();

        $this->service->accept($invitation);

        $this->assertNotNull($invitation->fresh()->responded_at);
    }

    #[Test]
    public function accept_creates_seminar_attendee_record(): void
    {
        $invitation = SeminarInvitation::factory()->create();

        $this->service->accept($invitation);

        $this->assertDatabaseHas('training_seminar_attendees', [
            'seminar_id' => $invitation->seminar_id,
            'account_id' => $invitation->account_id,
            'invitation_id' => $invitation->id,
        ]);
    }

    #[Test]
    public function accept_does_not_create_duplicate_attendee_records(): void
    {
        $invitation = SeminarInvitation::factory()->create();

        $this->service->accept($invitation);
        $this->service->accept($invitation->fresh());

        $attendees = SeminarAttendee::where('seminar_id', $invitation->seminar_id)
            ->where('account_id', $invitation->account_id)
            ->get();

        $this->assertCount(1, $attendees);
    }

    #[Test]
    public function mark_not_interested_updates_status(): void
    {
        $invitation = SeminarInvitation::factory()->create();

        $this->service->markNotInterested($invitation);

        $this->assertEquals(SeminarInvitationStatus::NotInterested, $invitation->fresh()->status);
    }

    #[Test]
    public function mark_not_interested_sets_responded_at(): void
    {
        $invitation = SeminarInvitation::factory()->create();

        $this->service->markNotInterested($invitation);

        $this->assertNotNull($invitation->fresh()->responded_at);
    }

    #[Test]
    public function mark_not_interested_removes_from_waiting_list(): void
    {
        $this->setUpSeminar(5);
        $waitingListAccount = $this->addToWaitingList(true);
        $account = Account::find($waitingListAccount->account_id);

        Notification::fake();

        $invitation = $this->service->createInvitation($this->seminar, $account, $waitingListAccount->id);

        $this->assertNull($waitingListAccount->fresh()->deleted_at);

        $this->service->markNotInterested($invitation);

        $this->assertNotNull($waitingListAccount->fresh()->deleted_at);
    }

    #[Test]
    public function mark_cannot_attend_updates_status(): void
    {
        $invitation = SeminarInvitation::factory()->create();

        $this->service->markCannotAttend($invitation);

        $this->assertEquals(SeminarInvitationStatus::CannotAttend, $invitation->fresh()->status);
    }

    #[Test]
    public function mark_cannot_attend_sets_responded_at(): void
    {
        $invitation = SeminarInvitation::factory()->create();

        $this->service->markCannotAttend($invitation);

        $this->assertNotNull($invitation->fresh()->responded_at);
    }

    #[Test]
    public function mark_cannot_attend_does_not_remove_from_waiting_list_on_first_miss(): void
    {
        $this->setUpSeminar(5);
        $waitingListAccount = $this->addToWaitingList(true);
        $account = Account::find($waitingListAccount->account_id);

        Notification::fake();

        $invitation = $this->service->createInvitation($this->seminar, $account, $waitingListAccount->id);

        $this->service->markCannotAttend($invitation);

        $this->assertNull($waitingListAccount->fresh()->deleted_at);
        $this->assertEquals(
            SeminarInvitationStatus::CannotAttend,
            $invitation->fresh()->status
        );
    }

    #[Test]
    public function mark_cannot_attend_removes_on_second_miss_and_updates_status(): void
    {
        $waitingList = WaitingList::factory()->create([
            'department' => 'atc',
            'cts_theory_exam_level' => 'S1',
        ]);

        $seminar1 = Seminar::factory()->create([
            'waiting_list_id' => $waitingList->id,
            'capacity' => 5,
            'invitation_expiry_hours' => 168,
            'automatic_invitations_enabled' => false,
            'created_by' => $this->privacc->id,
        ]);

        $seminar2 = Seminar::factory()->create([
            'waiting_list_id' => $waitingList->id,
            'capacity' => 5,
            'invitation_expiry_hours' => 168,
            'automatic_invitations_enabled' => false,
            'created_by' => $this->privacc->id,
        ]);

        $student = Account::factory()->create();
        $waitingListAccount = $waitingList->addToWaitingList($student, $this->privacc);

        $member = Member::factory()->create(['cid' => $student->id]);
        TheoryResult::create([
            'exam' => 'S1',
            'student_id' => $member->id,
            'pass' => 1,
            'questions' => 40,
            'time_mins' => 60,
            'passmark' => 75,
            'correct' => 35,
            'started' => now()->subDay(),
            'submitted' => 1,
            'submitted_time' => now()->subDay(),
        ]);

        Notification::fake();

        $invitation1 = $this->service->createInvitation($seminar1, $student, $waitingListAccount->id);
        $this->service->markCannotAttend($invitation1);

        $invitation2 = $this->service->createInvitation($seminar2, $student, $waitingListAccount->id);
        $this->service->markCannotAttend($invitation2);

        $this->assertNotNull($waitingListAccount->fresh()->deleted_at);
        $this->assertEquals(
            SeminarInvitationStatus::RemovedTwoCannotAttend,
            $invitation2->fresh()->status
        );
    }

    #[Test]
    public function expire_unresponded_expires_past_due_sent_invitations(): void
    {
        SeminarInvitation::factory()->create([
            'status' => SeminarInvitationStatus::Sent,
            'expires_at' => now()->subDay(),
        ]);

        $this->service->expireUnrespondedInvitations();

        $this->assertDatabaseHas('training_seminar_invitations', [
            'status' => SeminarInvitationStatus::RemovedNoResponse->value,
        ]);
    }

    #[Test]
    public function expire_unresponded_sets_responded_at(): void
    {
        $invitation = SeminarInvitation::factory()->create([
            'status' => SeminarInvitationStatus::Sent,
            'expires_at' => now()->subDay(),
            'responded_at' => null,
        ]);

        $this->service->expireUnrespondedInvitations();

        $this->assertNotNull($invitation->fresh()->responded_at);
    }

    #[Test]
    public function expire_unresponded_removes_from_waiting_list(): void
    {
        $this->setUpSeminar(5);
        $waitingListAccount = $this->addToWaitingList(true);
        $account = Account::find($waitingListAccount->account_id);

        Notification::fake();

        $invitation = $this->service->createInvitation($this->seminar, $account, $waitingListAccount->id);
        $invitation->update(['expires_at' => now()->subDay()]);

        $this->service->expireUnrespondedInvitations();

        $this->assertNotNull($waitingListAccount->fresh()->deleted_at);
    }

    #[Test]
    public function expire_unresponded_does_not_touch_non_sent_invitations(): void
    {
        SeminarInvitation::factory()->attending()->create([
            'expires_at' => now()->subDay(),
        ]);
        SeminarInvitation::factory()->notInterested()->create([
            'expires_at' => now()->subDay(),
        ]);

        $count = $this->service->expireUnrespondedInvitations();

        $this->assertSame(0, $count);
    }

    #[Test]
    public function expire_unresponded_does_not_expire_future_expiry_invitations(): void
    {
        SeminarInvitation::factory()->create([
            'status' => SeminarInvitationStatus::Sent,
            'expires_at' => now()->addDays(7),
        ]);

        $count = $this->service->expireUnrespondedInvitations();

        $this->assertSame(0, $count);
    }

    #[Test]
    public function expire_unresponded_handles_mixed_statuses_correctly(): void
    {
        SeminarInvitation::factory()->create([
            'status' => SeminarInvitationStatus::Sent,
            'expires_at' => now()->subDay(),
        ]);
        SeminarInvitation::factory()->attending()->create([
            'expires_at' => now()->subDay(),
        ]);
        SeminarInvitation::factory()->create([
            'status' => SeminarInvitationStatus::Sent,
            'expires_at' => now()->addDays(7),
        ]);

        $count = $this->service->expireUnrespondedInvitations();

        $this->assertSame(1, $count);
    }

    #[Test]
    public function expire_unresponded_does_not_throw_when_invitation_has_no_waiting_list_account(): void
    {
        SeminarInvitation::factory()->create([
            'status' => SeminarInvitationStatus::Sent,
            'expires_at' => now()->subDay(),
            'waiting_list_account_id' => null,
        ]);

        $count = $this->service->expireUnrespondedInvitations();

        $this->assertSame(1, $count);
    }

    #[Test]
    public function expire_unresponded_closes_short_notice_invitations_without_a_penalty(): void
    {
        $this->setUpSeminar(5);
        $waitingListAccount = $this->addToWaitingList(true);
        $account = Account::find($waitingListAccount->account_id);

        Notification::fake();

        $invitation = $this->service->createInvitation($this->seminar, $account, $waitingListAccount->id);
        $invitation->update(['is_short_notice' => true, 'expires_at' => now()->subDay()]);

        $count = $this->service->expireUnrespondedInvitations();

        $this->assertSame(1, $count);
        $this->assertEquals(SeminarInvitationStatus::ShortNoticeNoResponse, $invitation->fresh()->status);
        $this->assertNotNull($invitation->fresh()->responded_at);
        $this->assertNull($waitingListAccount->fresh()->deleted_at);
    }

    #[Test]
    public function expire_unresponded_short_notice_invitation_frees_a_place(): void
    {
        $this->setUpSeminar(5);
        $waitingListAccount = $this->addToWaitingList(true);
        $account = Account::find($waitingListAccount->account_id);

        Notification::fake();

        $invitation = $this->service->createInvitation($this->seminar, $account, $waitingListAccount->id);
        $invitation->update(['is_short_notice' => true, 'expires_at' => now()->subDay()]);

        $this->assertSame(4, $this->seminar->fresh()->spacesRemaining());

        $this->service->expireUnrespondedInvitations();

        $this->assertSame(5, $this->seminar->fresh()->spacesRemaining());
    }

    #[Test]
    public function expire_unresponded_does_not_close_future_short_notice_invitations(): void
    {
        $this->setUpSeminar(5);
        $waitingListAccount = $this->addToWaitingList(true);
        $account = Account::find($waitingListAccount->account_id);

        Notification::fake();

        $invitation = $this->service->createInvitation($this->seminar, $account, $waitingListAccount->id);
        $invitation->update(['is_short_notice' => true, 'expires_at' => now()->addDay()]);

        $count = $this->service->expireUnrespondedInvitations();

        $this->assertSame(0, $count);
        $this->assertEquals(SeminarInvitationStatus::Sent, $invitation->fresh()->status);
    }

    #[Test]
    public function mark_cannot_attend_does_not_count_short_notice_invitations_towards_limit(): void
    {
        $waitingList = WaitingList::factory()->create([
            'department' => 'atc',
            'cts_theory_exam_level' => 'S1',
        ]);
        $student = Account::factory()->create();
        $waitingListAccount = $waitingList->addToWaitingList($student, $this->privacc);

        $seminar1 = Seminar::factory()->create([
            'waiting_list_id' => $waitingList->id,
            'capacity' => 5,
            'created_by' => $this->privacc->id,
        ]);

        $seminar2 = Seminar::factory()->create([
            'waiting_list_id' => $waitingList->id,
            'capacity' => 5,
            'created_by' => $this->privacc->id,
        ]);

        $invitation1 = SeminarInvitation::factory()->shortNotice()->create([
            'seminar_id' => $seminar1->id,
            'account_id' => $student->id,
            'waiting_list_account_id' => $waitingListAccount->id,
        ]);

        $invitation2 = SeminarInvitation::factory()->shortNotice()->create([
            'seminar_id' => $seminar2->id,
            'account_id' => $student->id,
            'waiting_list_account_id' => $waitingListAccount->id,
        ]);

        $this->service->markCannotAttend($invitation1);
        $this->service->markCannotAttend($invitation2);

        $this->assertSame(0, $student->cannotAttendSeminarCountForWaitingList($waitingList));
        $this->assertNull($waitingListAccount->fresh()->deleted_at);
        $this->assertEquals(SeminarInvitationStatus::CannotAttend, $invitation2->fresh()->status);
    }

    #[Test]
    public function create_invitation_throws_when_the_account_was_reminded_for_this_seminar(): void
    {
        $this->setUpSeminar(5, true);
        $waitingListAccount = $this->addToWaitingList(false);
        $this->seminar = $this->seminar->fresh();

        $this->service->topUpAutomaticInvitations($this->seminar);
        $this->assertTrue($waitingListAccount->fresh()->wasRemindedForSeminar($this->seminar->id));

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('This student was sent a theory exam reminder for this seminar and cannot be invited.');

        $this->service->createInvitation(
            $this->seminar->fresh(),
            $waitingListAccount->account,
            $waitingListAccount->id
        );
    }

    #[Test]
    public function create_invitation_does_not_use_a_waiting_list_account_belonging_to_another_account(): void
    {
        $this->setUpSeminar(5, true);
        $remindedMembership = $this->addToWaitingList(false);
        $this->seminar = $this->seminar->fresh();

        $this->service->topUpAutomaticInvitations($this->seminar);
        $this->assertTrue($remindedMembership->fresh()->wasRemindedForSeminar($this->seminar->id));

        // Someone else's reminded membership must neither block this invite nor be linked to it.
        $otherStudent = Account::factory()->create();

        $invitation = $this->service->createInvitation(
            $this->seminar->fresh(),
            $otherStudent,
            $remindedMembership->id
        );

        $this->assertSame($otherStudent->id, $invitation->account_id);
        $this->assertNull($invitation->waiting_list_account_id);
    }

    #[Test]
    public function top_up_reminds_students_who_have_not_passed_the_theory_exam(): void
    {
        $this->setUpSeminar(5, true);
        $waitingListAccount = $this->addToWaitingList(false);
        $this->seminar = $this->seminar->fresh();

        $this->service->topUpAutomaticInvitations($this->seminar);

        $this->assertDatabaseHas('training_waiting_list_theory_reminders', [
            'waiting_list_account_id' => $waitingListAccount->id,
            'account_id' => $waitingListAccount->account_id,
            'seminar_id' => $this->seminar->id,
            'status' => SeminarTheoryReminderStatus::Pending->value,
            'actioned_seminar_id' => null,
        ]);

        Notification::assertSentTo(
            Account::find($waitingListAccount->account_id),
            SeminarTheoryExamReminderNotification::class
        );
    }

    #[Test]
    public function top_up_does_not_remind_or_remove_a_student_twice_for_the_same_seminar(): void
    {
        $this->setUpSeminar(5, true);
        $waitingListAccount = $this->addToWaitingList(false);
        $this->seminar = $this->seminar->fresh();

        $this->service->topUpAutomaticInvitations($this->seminar);
        $this->service->topUpAutomaticInvitations($this->seminar->fresh());

        $this->assertSame(
            1,
            WaitingListTheoryReminder::query()->where('waiting_list_account_id', $waitingListAccount->id)->count()
        );
        $this->assertNull($waitingListAccount->fresh()->deleted_at);
        Notification::assertSentTimes(SeminarTheoryExamReminderNotification::class, 1);
    }

    #[Test]
    public function top_up_rolls_back_the_reminder_when_the_notification_fails(): void
    {
        $this->setUpSeminar(5, true);
        $waitingListAccount = $this->addToWaitingList(false);
        $this->seminar = $this->seminar->fresh();

        // Send for real so that the mail failure aborts the reminder transaction.
        $this->app->forgetInstance(ChannelManager::class);
        Notification::clearResolvedInstance(ChannelManager::class);
        Mail::shouldReceive('mailer')->andThrow(new RuntimeException('Mail failed'));

        // The failure is logged and swallowed rather than cancelling the rest of the batch.
        $this->assertSame(0, $this->service->topUpAutomaticInvitations($this->seminar));

        $this->assertSame(
            0,
            WaitingListTheoryReminder::query()->where('waiting_list_account_id', $waitingListAccount->id)->count()
        );
    }

    #[Test]
    public function top_up_still_processes_students_after_a_notification_failure(): void
    {
        $this->setUpSeminar(5, true);

        $failing = $this->addToWaitingList(false);

        // Students are processed oldest first, so keep their join times apart to fix the order.
        Carbon::setTestNow(now()->addMinute());
        $later = $this->addToWaitingList(false);
        Carbon::setTestNow();

        // This student ignored a reminder from an earlier seminar, so they are removed without any email.
        $this->remindAtAnEarlierSeminar($later);
        $this->seminar = $this->seminar->fresh();

        // Send for real so that the mail failure aborts the first student's reminder transaction.
        $this->app->forgetInstance(ChannelManager::class);
        Notification::clearResolvedInstance(ChannelManager::class);
        Mail::shouldReceive('mailer')->andThrow(new RuntimeException('Mail failed'));

        $this->service->topUpAutomaticInvitations($this->seminar);

        $this->assertSame(
            0,
            WaitingListTheoryReminder::query()->where('waiting_list_account_id', $failing->id)->count()
        );

        // The student behind the failing one was still dealt with.
        $this->assertSame(
            RemovalReason::SeminarTheoryExamNotPassed,
            $later->fresh()->removal_type
        );
    }

    #[Test]
    public function top_up_still_processes_students_after_an_invitation_notification_failure(): void
    {
        $this->setUpSeminar(5, true);

        $failing = $this->addToWaitingList(true);

        // Students are processed oldest first, so keep their join times apart to fix the order.
        Carbon::setTestNow(now()->addMinute());
        $later = $this->addToWaitingList(false);
        Carbon::setTestNow();

        // This student ignored a reminder from an earlier seminar, so they are removed without any email.
        $this->remindAtAnEarlierSeminar($later);
        $this->seminar = $this->seminar->fresh();

        // Send for real so that the mail failure aborts the first student's invitation transaction.
        $this->app->forgetInstance(ChannelManager::class);
        Notification::clearResolvedInstance(ChannelManager::class);
        Mail::shouldReceive('mailer')->andThrow(new RuntimeException('Mail failed'));

        $this->assertSame(0, $this->service->topUpAutomaticInvitations($this->seminar));

        // The failed invitation was rolled back, so the student is invited again on the next run.
        $this->assertDatabaseMissing('training_seminar_invitations', [
            'seminar_id' => $this->seminar->id,
            'account_id' => $failing->account_id,
        ]);

        // The student behind the failing one was still dealt with.
        $this->assertSame(
            RemovalReason::SeminarTheoryExamNotPassed,
            $later->fresh()->removal_type
        );
    }

    #[Test]
    public function top_up_does_not_remove_a_student_when_the_reminder_has_no_seminar(): void
    {
        $this->setUpSeminar(5, true);
        $waitingListAccount = $this->addToWaitingList(false);

        // A reminder whose seminar has since been deleted has no seminar to compare against.
        $this->remindAtAnEarlierSeminar($waitingListAccount)->update(['seminar_id' => null]);
        $this->seminar = $this->seminar->fresh();

        $this->service->topUpAutomaticInvitations($this->seminar);

        $this->assertNull($waitingListAccount->fresh()->deleted_at);
        $this->assertSame(
            1,
            WaitingListTheoryReminder::query()->where('waiting_list_account_id', $waitingListAccount->id)->count()
        );
    }

    #[Test]
    public function top_up_removes_students_who_were_reminded_for_an_earlier_seminar_and_still_have_not_passed(): void
    {
        $this->setUpSeminar(5, true);
        $waitingListAccount = $this->addToWaitingList(false);
        $this->remindAtAnEarlierSeminar($waitingListAccount);
        $this->seminar = $this->seminar->fresh();

        $result = $this->service->topUpAutomaticInvitations($this->seminar);

        $this->assertSame(0, $result);
        $this->assertDatabaseMissing('training_seminar_invitations', [
            'seminar_id' => $this->seminar->id,
            'account_id' => $waitingListAccount->account_id,
        ]);

        $removed = $waitingListAccount->fresh();
        $this->assertNotNull($removed->deleted_at);
        $this->assertEquals(RemovalReason::SeminarTheoryExamNotPassed, $removed->removal_type);

        // The reminder records that it was actioned at this seminar so statistics can count it.
        $this->assertDatabaseHas('training_waiting_list_theory_reminders', [
            'waiting_list_account_id' => $waitingListAccount->id,
            'status' => SeminarTheoryReminderStatus::Removed->value,
            'actioned_seminar_id' => $this->seminar->id,
        ]);

        // The member's account is left with a note explaining the removal.
        $this->assertDatabaseHas('mship_account_note', [
            'account_id' => $waitingListAccount->account_id,
            'content' => 'Member was reminded on '
                .now()->subDays(7)->format('d/m/Y')
                .' that the S1 theory exam must be passed before the next seminar, and has been removed from the waiting list as it has still not been passed.',
        ]);
    }

    #[Test]
    public function top_up_invites_students_who_were_reminded_for_an_earlier_seminar_and_have_now_passed(): void
    {
        $this->setUpSeminar(5, true);
        $waitingListAccount = $this->addToWaitingList(false);
        $this->remindAtAnEarlierSeminar($waitingListAccount);
        $this->passTheoryExam($waitingListAccount);
        $this->seminar = $this->seminar->fresh();

        $result = $this->service->topUpAutomaticInvitations($this->seminar);

        $this->assertSame(1, $result);
        $this->assertDatabaseHas('training_seminar_invitations', [
            'seminar_id' => $this->seminar->id,
            'account_id' => $waitingListAccount->account_id,
            'status' => SeminarInvitationStatus::Sent->value,
        ]);
        $this->assertNull($waitingListAccount->fresh()->deleted_at);
    }

    #[Test]
    public function top_up_keeps_reminded_students_out_of_the_seminar_they_were_reminded_for(): void
    {
        $this->setUpSeminar(5, true);
        $waitingListAccount = $this->addToWaitingList(false);
        $this->seminar = $this->seminar->fresh();

        $this->service->topUpAutomaticInvitations($this->seminar);

        // Passing the exam afterwards does not earn them a place at this seminar.
        $this->passTheoryExam($waitingListAccount);
        $this->service->topUpAutomaticInvitations($this->seminar->fresh());

        $this->assertDatabaseMissing('training_seminar_invitations', [
            'seminar_id' => $this->seminar->id,
            'account_id' => $waitingListAccount->account_id,
        ]);
        $this->assertNull($waitingListAccount->fresh()->deleted_at);
    }
}
