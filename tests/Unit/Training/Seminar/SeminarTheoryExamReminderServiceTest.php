<?php

declare(strict_types=1);

namespace Tests\Unit\Training\Seminar;

use App\Enums\TheoryExamReminderStatus;
use App\Models\Cts\Member;
use App\Models\Cts\TheoryResult;
use App\Models\Mship\Account;
use App\Models\Training\Seminar\Seminar;
use App\Models\Training\WaitingList;
use App\Models\Training\WaitingList\RemovalReason;
use App\Models\Training\WaitingList\WaitingListAccount;
use App\Models\Training\WaitingList\WaitingListTheoryReminder;
use App\Notifications\Training\SeminarTheoryExamReminderNotification;
use App\Services\Training\SeminarTheoryExamReminderService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Notification;
use Mockery;
use PHPUnit\Framework\Attributes\Test;
use RuntimeException;
use Tests\TestCase;

class SeminarTheoryExamReminderServiceTest extends TestCase
{
    use DatabaseTransactions;

    private SeminarTheoryExamReminderService $service;

    private WaitingList $waitingList;

    private Seminar $seminar;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = app(SeminarTheoryExamReminderService::class);
        Event::fake();

        $this->waitingList = WaitingList::factory()->create([
            'department' => WaitingList::ATC_DEPARTMENT,
            'cts_theory_exam_level' => 'S1',
        ]);

        $this->seminar = Seminar::factory()->create([
            'waiting_list_id' => $this->waitingList->id,
            'capacity' => 5,
            'created_by' => $this->privacc->id,
        ]);
    }

    private function addStudentToWaitingList(): WaitingListAccount
    {
        return $this->waitingList->addToWaitingList(Account::factory()->create(), $this->privacc);
    }

    private function createTheoryResult(WaitingListAccount $waitingListAccount, bool $pass, Carbon $started): TheoryResult
    {
        $member = Member::query()->where('cid', $waitingListAccount->account_id)->first() ?? Member::factory()->create(['cid' => $waitingListAccount->account_id]);

        return TheoryResult::create([
            'exam' => 'S1',
            'student_id' => $member->id,
            'pass' => $pass ? 1 : 0,
            'questions' => 40,
            'time_mins' => 60,
            'passmark' => 75,
            'correct' => $pass ? 35 : 10,
            'started' => $started,
            'submitted' => 1,
            'submitted_time' => $started,
        ]);
    }

    private function createDueReminder(
        WaitingListAccount $waitingListAccount,
        ?Carbon $lastAttemptAt = null,
        ?Carbon $nextCheckAt = null,
    ): WaitingListTheoryReminder {
        return WaitingListTheoryReminder::create([
            'waiting_list_account_id' => $waitingListAccount->id,
            'account_id' => $waitingListAccount->account_id,
            'seminar_id' => $this->seminar->id,
            'status' => TheoryExamReminderStatus::Pending->value,
            'reminded_at' => now()->subDays(8),
            'last_attempt_at' => $lastAttemptAt,
            'next_check_at' => $nextCheckAt ?? now()->subDay(),
        ]);
    }

    #[Test]
    public function it_creates_a_pending_reminder_using_the_configured_completion_window(): void
    {
        $waitingListAccount = $this->addStudentToWaitingList();

        $reminder = $this->service->sendReminder($waitingListAccount, $this->seminar);

        $this->assertNotNull($reminder);
        $this->assertSame(TheoryExamReminderStatus::Pending, $reminder->status);
        $this->assertSame($this->seminar->id, $reminder->seminar_id);
        $this->assertEqualsWithDelta(
            WaitingListTheoryReminder::COMPLETION_WINDOW_DAYS,
            $reminder->reminded_at->diffInDays($reminder->next_check_at),
            0.01
        );
    }

    #[Test]
    public function it_sends_a_reminder_notification_to_the_student(): void
    {
        $waitingListAccount = $this->addStudentToWaitingList();

        $reminder = $this->service->sendReminder($waitingListAccount, $this->seminar);

        Notification::assertSentTo(
            $waitingListAccount->account,
            SeminarTheoryExamReminderNotification::class,
            fn ($notification) => $notification->reminder->is($reminder)
        );
    }

    #[Test]
    public function it_does_not_send_a_second_reminder_and_leaves_the_window_untouched(): void
    {
        $waitingListAccount = $this->addStudentToWaitingList();

        $reminder = $this->service->sendReminder($waitingListAccount, $this->seminar);
        $firstCheckAt = $reminder->next_check_at;

        $this->travel(2)->days();

        $second = $this->service->sendReminder($waitingListAccount, $this->seminar);

        $this->assertNull($second);
        $this->assertSame(1, WaitingListTheoryReminder::query()->count());
        $this->assertEquals($firstCheckAt->timestamp, $reminder->fresh()->next_check_at->timestamp);
        Notification::assertSentTimes(SeminarTheoryExamReminderNotification::class, 1);
    }

    #[Test]
    public function it_does_not_remind_students_when_the_waiting_list_has_no_exam_level(): void
    {
        $this->waitingList->forceFill(['cts_theory_exam_level' => null])->save();
        $waitingListAccount = $this->addStudentToWaitingList();

        $reminder = $this->service->sendReminder($waitingListAccount, $this->seminar);

        $this->assertNull($reminder);
        $this->assertSame(0, WaitingListTheoryReminder::query()->count());
    }

    #[Test]
    public function it_records_the_students_latest_attempt_as_the_baseline(): void
    {
        $waitingListAccount = $this->addStudentToWaitingList();
        $previousAttempt = now()->subDays(3);
        $this->createTheoryResult($waitingListAccount, false, $previousAttempt);

        $reminder = $this->service->sendReminder($waitingListAccount, $this->seminar);

        $this->assertEquals($previousAttempt->timestamp, $reminder->last_attempt_at->timestamp);
    }

    #[Test]
    public function it_marks_a_reminder_as_passed_and_leaves_the_student_on_the_waiting_list(): void
    {
        $waitingListAccount = $this->addStudentToWaitingList();
        $reminder = $this->createDueReminder($waitingListAccount);

        $this->createTheoryResult($waitingListAccount, true, now()->subHour());

        $this->service->checkDueReminders();

        $this->assertSame(TheoryExamReminderStatus::Passed, $reminder->fresh()->status);
        $this->assertNotNull($reminder->fresh()->resolved_at);
        $this->assertNull($waitingListAccount->fresh()->deleted_at);
    }

    #[Test]
    public function it_checks_again_after_a_failed_attempt_instead_of_removing_the_student(): void
    {
        $waitingListAccount = $this->addStudentToWaitingList();
        $previousAttempt = now()->subDays(9);
        $this->createTheoryResult($waitingListAccount, false, $previousAttempt);
        $reminder = $this->createDueReminder($waitingListAccount, $previousAttempt);

        $failedAttempt = now()->subDays(2);
        $this->createTheoryResult($waitingListAccount, false, $failedAttempt);

        $this->service->checkDueReminders();

        $this->assertSame(TheoryExamReminderStatus::Pending, $reminder->fresh()->status);
        $this->assertEquals($failedAttempt->timestamp, $reminder->fresh()->last_attempt_at->timestamp);
        $this->assertEqualsWithDelta(
            WaitingListTheoryReminder::FAILED_ATTEMPT_RECHECK_WINDOW_DAYS,
            $failedAttempt->diffInDays($reminder->fresh()->next_check_at),
            0.01
        );
        $this->assertNull($waitingListAccount->fresh()->deleted_at);
    }

    #[Test]
    public function it_removes_the_student_when_they_have_not_attempted_again(): void
    {
        $waitingListAccount = $this->addStudentToWaitingList();
        $previousAttempt = now()->subDays(9);
        $this->createTheoryResult($waitingListAccount, false, $previousAttempt);
        $reminder = $this->createDueReminder($waitingListAccount, $previousAttempt);

        $this->service->checkDueReminders();

        $this->assertSame(TheoryExamReminderStatus::Removed, $reminder->fresh()->status);
        $this->assertNotNull($reminder->fresh()->resolved_at);
        $this->assertNotNull($waitingListAccount->fresh()->deleted_at);
        $this->assertSame(
            RemovalReason::SeminarTheoryExamNotAttempted,
            $waitingListAccount->fresh()->removal_type
        );
    }

    #[Test]
    public function it_ignores_reminders_that_are_not_yet_due(): void
    {
        $waitingListAccount = $this->addStudentToWaitingList();
        $reminder = $this->createDueReminder($waitingListAccount, null, now()->addDay());

        $this->service->checkDueReminders();

        $this->assertSame(TheoryExamReminderStatus::Pending, $reminder->fresh()->status);
    }

    #[Test]
    public function it_cancels_a_reminder_when_the_student_is_no_longer_on_the_waiting_list(): void
    {
        $waitingListAccount = $this->addStudentToWaitingList();
        $reminder = $this->createDueReminder($waitingListAccount);

        $waitingListAccount->delete();

        $this->service->checkDueReminders();

        $this->assertSame(TheoryExamReminderStatus::Cancelled, $reminder->fresh()->status);
    }

    #[Test]
    public function it_rolls_back_the_reminder_when_the_notification_fails(): void
    {
        $waitingListAccount = $this->addStudentToWaitingList();

        $account = Mockery::mock(Account::class)->makePartial();
        $account->shouldReceive('notify')->once()->andThrow(new RuntimeException('Mail failed'));
        $waitingListAccount->setRelation('account', $account);

        try {
            $this->service->sendReminder($waitingListAccount, $this->seminar);
            $this->fail('Expected the notification failure to be thrown.');
        } catch (RuntimeException $exception) {
            $this->assertSame('Mail failed', $exception->getMessage());
        }

        $this->assertSame(0, WaitingListTheoryReminder::query()->count());
    }
}
