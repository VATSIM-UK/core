<?php

declare(strict_types=1);

namespace Tests\Feature\Training\Seminar;

use App\Enums\TheoryExamReminderStatus;
use App\Models\Mship\Account;
use App\Models\Training\WaitingList;
use App\Models\Training\WaitingList\WaitingListTheoryReminder;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Event;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CheckSeminarTheoryExamRemindersCommandTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        Event::fake();
    }

    private function createReminderAtWaitingListAccount(WaitingList $waitingList, \DateTimeInterface $nextCheckAt): WaitingListTheoryReminder
    {
        $student = Account::factory()->create();
        $waitingListAccount = $waitingList->addToWaitingList($student, $this->privacc);

        return WaitingListTheoryReminder::create([
            'waiting_list_account_id' => $waitingListAccount->id,
            'account_id' => $student->id,
            'status' => TheoryExamReminderStatus::Pending->value,
            'reminded_at' => now()->subDays(8),
            'next_check_at' => $nextCheckAt,
        ]);
    }

    #[Test]
    public function it_removes_students_who_have_not_attempted_the_exam_again(): void
    {
        $waitingList = WaitingList::factory()->create([
            'department' => WaitingList::ATC_DEPARTMENT,
            'cts_theory_exam_level' => 'S1',
        ]);

        $reminder = $this->createReminderAtWaitingListAccount($waitingList, now()->subDay());

        $this->artisan('training:check-seminar-theory-exam-reminders')->assertSuccessful();

        $this->assertNotNull($reminder->waitingListAccount()->withTrashed()->first()->deleted_at);
        $this->assertDatabaseHas('training_waiting_list_theory_reminders', [
            'id' => $reminder->id,
            'status' => TheoryExamReminderStatus::Removed->value,
        ]);
    }

    #[Test]
    public function it_leaves_reminders_that_are_not_yet_due_alone(): void
    {
        $waitingList = WaitingList::factory()->create([
            'department' => WaitingList::ATC_DEPARTMENT,
            'cts_theory_exam_level' => 'S1',
        ]);

        $reminder = $this->createReminderAtWaitingListAccount($waitingList, now()->addDay());

        $this->artisan('training:check-seminar-theory-exam-reminders')->assertSuccessful();

        $this->assertDatabaseHas('training_waiting_list_theory_reminders', [
            'id' => $reminder->id,
            'status' => TheoryExamReminderStatus::Pending->value,
        ]);
        $this->assertNull($reminder->waitingListAccount()->withTrashed()->first()->deleted_at);
    }
}
