<?php

declare(strict_types=1);

namespace Tests\Feature\Training\Seminar;

use App\Enums\SeminarInvitationStatus;
use App\Models\Mship\Account;
use App\Models\Training\Seminar\Seminar;
use App\Models\Training\Seminar\SeminarInvitation;
use App\Models\Training\WaitingList;
use App\Models\Training\WaitingList\WaitingListTheoryReminder;
use App\Services\Training\SeminarStatisticsService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Event;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class SeminarStatisticsServiceTest extends TestCase
{
    use DatabaseTransactions;

    private Seminar $seminar;

    protected function setUp(): void
    {
        parent::setUp();

        Event::fake();

        $waitingList = WaitingList::factory()->create([
            'department' => WaitingList::ATC_DEPARTMENT,
            'cts_theory_exam_level' => 'S1',
        ]);

        $this->seminar = Seminar::factory()->create([
            'waiting_list_id' => $waitingList->id,
            'capacity' => 12,
            'created_by' => $this->privacc->id,
        ]);
    }

    #[Test]
    public function it_returns_zeroed_statistics_when_the_seminar_has_no_activity(): void
    {
        $statistics = app(SeminarStatisticsService::class)->statisticsFor($this->seminar);

        $this->assertSame([
            'invitations_sent' => 0,
            'confirmed_attendees' => 0,
            'pending_responses' => 0,
            'declined_invitations' => 0,
            'expired' => 0,
            'cannot_attend' => 0,
            'not_interested' => 0,
            'removed_from_waiting_list' => 0,
            'theory_reminders' => 0,
        ], $statistics);
    }

    #[Test]
    public function it_counts_invitations_by_outcome(): void
    {
        $this->createInvitations(SeminarInvitationStatus::Sent, 2);
        $this->createInvitations(SeminarInvitationStatus::Attending, 2);
        $this->createInvitations(SeminarInvitationStatus::NotInterested, 1);
        $this->createInvitations(SeminarInvitationStatus::CannotAttend, 1);
        $this->createInvitations(SeminarInvitationStatus::RemovedNoResponse, 2);
        $this->createInvitations(SeminarInvitationStatus::RemovedTwoCannotAttend, 1);
        $this->createInvitations(SeminarInvitationStatus::ShortNoticeNoResponse, 1);

        $statistics = app(SeminarStatisticsService::class)->statisticsFor($this->seminar);

        $this->assertSame(10, $statistics['invitations_sent']);
        $this->assertSame(2, $statistics['pending_responses']);
        $this->assertSame(6, $statistics['declined_invitations']);
        $this->assertSame(3, $statistics['expired']);
        $this->assertSame(2, $statistics['cannot_attend']);
        $this->assertSame(1, $statistics['not_interested']);
        $this->assertSame(4, $statistics['removed_from_waiting_list']);
    }

    #[Test]
    public function it_counts_attendees_separately_from_invitation_statuses(): void
    {
        $this->createInvitations(SeminarInvitationStatus::Attending, 2);

        $this->seminar->attendees()->create([
            'account_id' => Account::factory()->create()->id,
            'added_at' => now(),
        ]);

        $statistics = app(SeminarStatisticsService::class)->statisticsFor($this->seminar);

        $this->assertSame(1, $statistics['confirmed_attendees']);
    }

    #[Test]
    public function it_only_counts_theory_reminders_issued_for_this_seminar(): void
    {
        $otherSeminar = Seminar::factory()->create([
            'waiting_list_id' => $this->seminar->waiting_list_id,
            'created_by' => $this->privacc->id,
        ]);

        $waitingList = $this->seminar->waitingList;

        foreach ([[$this->seminar, 2], [$otherSeminar, 3]] as [$seminar, $count]) {
            for ($i = 0; $i < $count; $i++) {
                $waitingListAccount = $waitingList->addToWaitingList(
                    Account::factory()->create(),
                    $this->privacc,
                );

                WaitingListTheoryReminder::create([
                    'waiting_list_account_id' => $waitingListAccount->id,
                    'account_id' => $waitingListAccount->account_id,
                    'seminar_id' => $seminar->id,
                    'reminded_at' => now(),
                ]);
            }
        }

        $statistics = app(SeminarStatisticsService::class)->statisticsFor($this->seminar);

        $this->assertSame(2, $statistics['theory_reminders']);
    }

    private function createInvitations(SeminarInvitationStatus $status, int $count): void
    {
        SeminarInvitation::factory()->count($count)->create([
            'seminar_id' => $this->seminar->id,
            'status' => $status,
        ]);
    }
}
