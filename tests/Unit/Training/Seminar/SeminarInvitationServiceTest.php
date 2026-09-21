<?php

declare(strict_types=1);

namespace Tests\Unit\Training\Seminar;

use App\Enums\SeminarInvitationStatus;
use App\Models\Cts\Member;
use App\Models\Cts\TheoryResult;
use App\Models\Mship\Account;
use App\Models\Training\Seminar\Seminar;
use App\Models\Training\Seminar\SeminarAttendee;
use App\Models\Training\Seminar\SeminarInvitation;
use App\Models\Training\WaitingList;
use App\Models\Training\WaitingList\WaitingListAccount;
use App\Notifications\Training\SeminarInvitationNotification;
use App\Services\Training\SeminarInvitationService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Notification;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class SeminarInvitationServiceTest extends TestCase
{
    use DatabaseTransactions;

    private SeminarInvitationService $service;

    private Seminar $seminar;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = new SeminarInvitationService;
        Event::fake();
    }

    private function setUpSeminar(int $capacity = 10, bool $automatic = false, int $expiryDays = 7): void
    {
        $waitingList = WaitingList::factory()->create([
            'department' => 'atc',
            'cts_theory_exam_level' => 'S1',
        ]);

        $this->seminar = Seminar::factory()->create([
            'waiting_list_id' => $waitingList->id,
            'capacity' => $capacity,
            'invitation_expiry_days' => $expiryDays,
            'automatic_invitations_enabled' => $automatic,
            'created_by' => $this->privacc->id,
        ]);
    }

    private function addToWaitingList(bool $theoryExamPassed = true): WaitingListAccount
    {
        $student = Account::factory()->create();
        $waitingListAccount = $this->seminar->waitingList->addToWaitingList($student, $this->privacc);

        if ($theoryExamPassed) {
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
        }

        return $waitingListAccount;
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
            'invitation_expiry_days' => 7,
            'automatic_invitations_enabled' => false,
            'created_by' => $this->privacc->id,
        ]);

        $seminar2 = Seminar::factory()->create([
            'waiting_list_id' => $waitingList->id,
            'capacity' => 5,
            'invitation_expiry_days' => 7,
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
}
