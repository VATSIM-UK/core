<?php

declare(strict_types=1);

namespace Tests\Feature\TrainingPanel\MyTraining;

use App\Livewire\Training\MyAcceptedMentoringSessionsTable;
use App\Models\Cts\Member;
use App\Models\Cts\Session;
use App\Models\Mship\Account;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Spatie\CalendarLinks\Link;
use Tests\Feature\TrainingPanel\BaseTrainingPanelTestCase;

class MyAcceptedMentoringSessionsTableTest extends BaseTrainingPanelTestCase
{
    use DatabaseTransactions;

    protected Account $studentAccount;

    protected Member $studentMember;

    protected Member $mentorMember;

    protected Session $acceptedSession;

    protected function setUp(): void
    {
        parent::setUp();

        $this->studentAccount = Account::factory()->create();
        $this->studentMember = Member::factory()->create([
            'id' => $this->studentAccount->id,
            'cid' => $this->studentAccount->id,
        ]);

        $mentorAccount = Account::factory()->create();
        $this->mentorMember = Member::factory()->create([
            'id' => $mentorAccount->id,
            'cid' => $mentorAccount->id,
        ]);

        $this->acceptedSession = Session::factory()->create([
            'student_id' => $this->studentMember->id,
            'mentor_id' => $this->mentorMember->id,
            'position' => 'EGLL_APP',
            'taken' => 1,
            'taken_date' => now()->addDays(1)->format('Y-m-d'),
            'taken_from' => '10:00:00',
            'taken_to' => '12:00:00',
            'filed' => null,
            'cancelled_datetime' => null,
            'noShow' => 0,
        ]);

        $this->studentAccount->givePermissionTo('training.access');
    }

    #[Test]
    public function member_with_training_access_can_view_the_table(): void
    {
        Livewire::actingAs($this->studentAccount)
            ->test(MyAcceptedMentoringSessionsTable::class)
            ->assertSuccessful();
    }

    #[Test]
    public function it_shows_accepted_sessions_for_the_authenticated_member(): void
    {
        Livewire::actingAs($this->studentAccount)
            ->test(MyAcceptedMentoringSessionsTable::class)
            ->assertSuccessful()
            ->assertCanSeeTableRecords([$this->acceptedSession]);
    }

    #[Test]
    public function it_excludes_sessions_for_other_students(): void
    {
        $otherAccount = Account::factory()->create();
        $otherMember = Member::factory()->create([
            'id' => $otherAccount->id,
            'cid' => $otherAccount->id,
        ]);

        $otherSession = Session::factory()->create([
            'student_id' => $otherMember->id,
            'mentor_id' => $this->mentorMember->id,
            'position' => 'EGKK_APP',
            'taken' => 1,
            'taken_date' => now()->addDays(2)->format('Y-m-d'),
            'taken_from' => '14:00:00',
            'taken_to' => '16:00:00',
            'filed' => null,
            'cancelled_datetime' => null,
            'noShow' => 0,
        ]);

        Livewire::actingAs($this->studentAccount)
            ->test(MyAcceptedMentoringSessionsTable::class)
            ->assertSuccessful()
            ->assertCanSeeTableRecords([$this->acceptedSession])
            ->assertCanNotSeeTableRecords([$otherSession]);
    }

    #[Test]
    public function it_excludes_cancelled_sessions(): void
    {
        $cancelledSession = Session::factory()->create([
            'student_id' => $this->studentMember->id,
            'mentor_id' => $this->mentorMember->id,
            'position' => 'EGLL_GND',
            'taken' => 1,
            'taken_date' => now()->addDays(3)->format('Y-m-d'),
            'taken_from' => '10:00:00',
            'taken_to' => '12:00:00',
            'filed' => null,
            'cancelled_datetime' => now(),
            'noShow' => 0,
        ]);

        Livewire::actingAs($this->studentAccount)
            ->test(MyAcceptedMentoringSessionsTable::class)
            ->assertSuccessful()
            ->assertCanSeeTableRecords([$this->acceptedSession])
            ->assertCanNotSeeTableRecords([$cancelledSession]);
    }

    #[Test]
    public function it_excludes_filed_sessions(): void
    {
        $filedSession = Session::factory()->create([
            'student_id' => $this->studentMember->id,
            'mentor_id' => $this->mentorMember->id,
            'position' => 'EGLL_TWR',
            'taken' => 1,
            'taken_date' => now()->addDays(3)->format('Y-m-d'),
            'taken_from' => '10:00:00',
            'taken_to' => '12:00:00',
            'filed' => now(),
            'cancelled_datetime' => null,
            'noShow' => 0,
        ]);

        Livewire::actingAs($this->studentAccount)
            ->test(MyAcceptedMentoringSessionsTable::class)
            ->assertSuccessful()
            ->assertCanSeeTableRecords([$this->acceptedSession])
            ->assertCanNotSeeTableRecords([$filedSession]);
    }

    #[Test]
    public function it_excludes_sessions_marked_as_no_show(): void
    {
        $noShowSession = Session::factory()->create([
            'student_id' => $this->studentMember->id,
            'mentor_id' => $this->mentorMember->id,
            'position' => 'EGLL_APP',
            'taken' => 1,
            'taken_date' => now()->addDays(3)->format('Y-m-d'),
            'taken_from' => '10:00:00',
            'taken_to' => '12:00:00',
            'filed' => null,
            'cancelled_datetime' => null,
            'noShow' => 1,
        ]);

        Livewire::actingAs($this->studentAccount)
            ->test(MyAcceptedMentoringSessionsTable::class)
            ->assertSuccessful()
            ->assertCanSeeTableRecords([$this->acceptedSession])
            ->assertCanNotSeeTableRecords([$noShowSession]);
    }

    #[Test]
    public function it_builds_calendar_link_object_with_correct_properties(): void
    {
        $method = new \ReflectionMethod(MyAcceptedMentoringSessionsTable::class, 'buildCalendarLinkObject');
        $component = new MyAcceptedMentoringSessionsTable;
        $link = $method->invoke($component, $this->acceptedSession);

        $this->assertInstanceOf(Link::class, $link);
        $this->assertSame('Mentoring Session - EGLL_APP', $link->title);
        $this->assertSame($this->acceptedSession->taken_date.' 10:00:00', $link->from->format('Y-m-d H:i:s'));
        $this->assertSame($this->acceptedSession->taken_date.' 12:00:00', $link->to->format('Y-m-d H:i:s'));
        $this->assertStringContainsString('Position: EGLL_APP', $link->description);
        $this->assertSame('EGLL_APP', $link->address);
    }

    #[Test]
    public function it_shows_empty_state_when_no_sessions_exist(): void
    {
        $emptyAccount = Account::factory()->create();
        Member::factory()->create([
            'id' => $emptyAccount->id,
            'cid' => $emptyAccount->id,
        ]);
        $emptyAccount->givePermissionTo('training.access');

        Livewire::actingAs($emptyAccount)
            ->test(MyAcceptedMentoringSessionsTable::class)
            ->assertSuccessful()
            ->assertSee('No upcoming mentoring sessions found');
    }
}
