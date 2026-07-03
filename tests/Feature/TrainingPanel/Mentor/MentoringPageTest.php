<?php

namespace Tests\Feature\TrainingPanel\Mentor;

use App\Livewire\Training\AcceptedMentoringSessionsTable;
use App\Livewire\Training\AvailabilityGantt;
use App\Models\Cts\Availability;
use App\Models\Cts\Member;
use App\Models\Cts\Session;
use App\Models\Mship\Account;
use App\Models\Training\Mentoring\MentorTrainingPosition;
use App\Models\Training\TrainingPlace\TrainingPlace;
use App\Models\Training\TrainingPosition\TrainingPosition;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\Feature\TrainingPanel\BaseTrainingPanelTestCase;

class MentoringPageTest extends BaseTrainingPanelTestCase
{
    use DatabaseTransactions;

    protected Account $mentor;

    protected Member $mentorMember;

    protected TrainingPosition $trainingPosition;

    protected function setUp(): void
    {
        parent::setUp();

        $this->mentor = Account::factory()->create();
        $this->mentorMember = Member::factory()->create([
            'id' => $this->mentor->id,
            'cid' => $this->mentor->id,
        ]);

        $this->trainingPosition = TrainingPosition::factory()->create([
            'cts_positions' => ['EGLL_APP'],
            'category' => 'S3 Training',
        ]);

        MentorTrainingPosition::create([
            'account_id' => $this->mentor->id,
            'mentorable_type' => TrainingPosition::class,
            'mentorable_id' => $this->trainingPosition->id,
            'created_by' => $this->mentor->id,
        ]);
    }

    #[Test]
    public function mentoring_page_is_accessible_to_users_with_mentor_training_positions(): void
    {
        $this->assertTrue(
            $this->mentor->mentorTrainingPositions()->exists()
        );
    }

    #[Test]
    public function mentoring_page_is_not_accessible_to_users_without_mentor_training_positions(): void
    {
        $nonMentor = Account::factory()->create();

        $this->assertFalse($nonMentor->mentorTrainingPositions()->exists());
    }

    #[Test]
    public function accepted_sessions_table_renders_successfully(): void
    {
        Livewire::actingAs($this->mentor)
            ->test(AcceptedMentoringSessionsTable::class)
            ->assertSuccessful();
    }

    #[Test]
    public function accepted_sessions_table_shows_sessions_assigned_to_the_mentor(): void
    {
        $student = Member::factory()->create();

        Session::factory()->create([
            'mentor_id' => $this->mentorMember->id,
            'student_id' => $student->id,
            'position' => 'EGLL_APP',
            'taken_date' => Carbon::tomorrow()->format('Y-m-d'),
            'taken_from' => '10:00:00',
            'filed' => null,
            'cancelled_datetime' => null,
            'noShow' => 0,
        ]);

        Livewire::actingAs($this->mentor)
            ->test(AcceptedMentoringSessionsTable::class)
            ->assertSee($student->name);
    }

    #[Test]
    public function accepted_sessions_table_does_not_show_filed_sessions(): void
    {
        $student = Member::factory()->create();

        Session::factory()->create([
            'mentor_id' => $this->mentorMember->id,
            'student_id' => $student->id,
            'position' => 'EGLL_APP',
            'taken_date' => Carbon::yesterday()->format('Y-m-d'),
            'filed' => now(),
            'cancelled_datetime' => null,
            'noShow' => 0,
        ]);

        Livewire::actingAs($this->mentor)
            ->test(AcceptedMentoringSessionsTable::class)
            ->assertDontSee($student->name);
    }

    #[Test]
    public function accepted_sessions_table_does_not_show_cancelled_sessions(): void
    {
        $student = Member::factory()->create();

        Session::factory()->create([
            'mentor_id' => $this->mentorMember->id,
            'student_id' => $student->id,
            'position' => 'EGLL_APP',
            'taken_date' => Carbon::tomorrow()->format('Y-m-d'),
            'filed' => null,
            'cancelled_datetime' => now(),
            'noShow' => 0,
        ]);

        Livewire::actingAs($this->mentor)
            ->test(AcceptedMentoringSessionsTable::class)
            ->assertDontSee($student->name);
    }

    #[Test]
    public function accepted_sessions_table_does_not_show_no_show_sessions(): void
    {
        $student = Member::factory()->create();

        Session::factory()->create([
            'mentor_id' => $this->mentorMember->id,
            'student_id' => $student->id,
            'position' => 'EGLL_APP',
            'taken_date' => Carbon::tomorrow()->format('Y-m-d'),
            'filed' => null,
            'cancelled_datetime' => null,
            'noShow' => 1,
        ]);

        Livewire::actingAs($this->mentor)
            ->test(AcceptedMentoringSessionsTable::class)
            ->assertDontSee($student->name);
    }

    #[Test]
    public function accepted_sessions_table_does_not_show_sessions_assigned_to_another_mentor(): void
    {
        $otherMentor = Member::factory()->create();
        $student = Member::factory()->create();

        Session::factory()->create([
            'mentor_id' => $otherMentor->id,
            'student_id' => $student->id,
            'position' => 'EGLL_APP',
            'taken_date' => Carbon::tomorrow()->format('Y-m-d'),
            'filed' => null,
            'cancelled_datetime' => null,
            'noShow' => 0,
        ]);

        Livewire::actingAs($this->mentor)
            ->test(AcceptedMentoringSessionsTable::class)
            ->assertDontSee($student->name);
    }

    #[Test]
    public function accepted_sessions_table_shows_empty_state_when_no_sessions(): void
    {
        Livewire::actingAs($this->mentor)
            ->test(AcceptedMentoringSessionsTable::class)
            ->assertSee('No upcoming mentoring sessions found');
    }

    #[Test]
    public function availability_gantt_renders_successfully(): void
    {
        Livewire::actingAs($this->mentor)
            ->test(AvailabilityGantt::class)
            ->assertSuccessful();
    }

    #[Test]
    public function availability_gantt_defaults_to_today(): void
    {
        $component = Livewire::actingAs($this->mentor)
            ->test(AvailabilityGantt::class);

        $this->assertSame(Carbon::today()->format('Y-m-d'), $component->instance()->date);
    }

    #[Test]
    public function previous_day_does_not_go_before_today_when_date_is_set_as_today(): void
    {
        $component = Livewire::actingAs($this->mentor)->test(AvailabilityGantt::class);

        $component->call('previousDay');

        $this->assertSame(Carbon::today()->format('Y-m-d'), $component->instance()->date);
    }

    #[Test]
    public function previous_day_decrements_the_date_when_viewing_a_future_date(): void
    {
        $component = Livewire::actingAs($this->mentor)
            ->test(AvailabilityGantt::class);

        $component->call('nextDay');
        $component->call('previousDay');

        $this->assertSame(Carbon::today()->format('Y-m-d'), $component->instance()->date);
    }

    #[Test]
    public function next_day_increments_the_date(): void
    {
        $component = Livewire::actingAs($this->mentor)
            ->test(AvailabilityGantt::class);

        $component->call('nextDay');

        $this->assertSame(Carbon::tomorrow()->format('Y-m-d'), $component->instance()->date);
    }

    #[Test]
    public function set_today_resets_date_to_today(): void
    {
        $component = Livewire::actingAs($this->mentor)
            ->test(AvailabilityGantt::class);

        $component->call('nextDay');
        $component->call('nextDay');
        $component->call('setToday');

        $this->assertSame(Carbon::today()->format('Y-m-d'), $component->instance()->date);
    }

    #[Test]
    public function available_categories_returns_only_categories_the_mentor_has_permissions_for(): void
    {
        $component = Livewire::actingAs($this->mentor)
            ->test(AvailabilityGantt::class);

        $categories = $component->instance()->availableCategories;

        $this->assertContains('S3 Training', $categories);
        $this->assertNotContains('OBS to S1 Training', $categories);
    }

    #[Test]
    public function category_filter_is_cleared_on_mount_if_mentor_lacks_permission_for_it(): void
    {
        $component = Livewire::actingAs($this->mentor)
            ->test(AvailabilityGantt::class, ['category' => 'OBS to S1 Training']);

        $this->assertNull($component->instance()->category);
    }

    #[Test]
    public function students_property_returns_empty_collection_when_mentor_has_no_callsigns(): void
    {
        $noCallsignMentor = Account::factory()->create();
        Member::factory()->create([
            'id' => $noCallsignMentor->id,
            'cid' => $noCallsignMentor->id,
        ]);

        $emptyPosition = TrainingPosition::factory()->create([
            'cts_positions' => [],
        ]);

        MentorTrainingPosition::create([
            'account_id' => $noCallsignMentor->id,
            'mentorable_type' => TrainingPosition::class,
            'mentorable_id' => $emptyPosition->id,
            'created_by' => $noCallsignMentor->id,
        ]);

        $component = Livewire::actingAs($noCallsignMentor)
            ->test(AvailabilityGantt::class);

        $this->assertTrue($component->instance()->students->isEmpty());
    }

    #[Test]
    public function students_property_only_includes_students_with_pending_sessions_in_allowed_positions(): void
    {
        $targetDate = Carbon::today();

        $student = Member::factory()->create();

        Session::factory()->create([
            'student_id' => $student->id,
            'mentor_id' => null,
            'position' => 'EGLL_APP',
            'filed' => null,
            'cancelled_datetime' => null,
        ]);

        Availability::factory()->create([
            'student_id' => $student->id,
            'date' => $targetDate->format('Y-m-d'),
            'from' => '10:00:00',
            'to' => '12:00:00',
        ]);

        $component = Livewire::actingAs($this->mentor)
            ->test(AvailabilityGantt::class);

        $studentIds = $component->instance()->students->pluck('id');

        $this->assertTrue($studentIds->contains($student->id));
    }

    #[Test]
    public function students_property_excludes_students_with_sessions_already_assigned_to_a_mentor(): void
    {
        $targetDate = Carbon::today();
        $otherMentor = Member::factory()->create();
        $student = Member::factory()->create();

        Session::factory()->create([
            'student_id' => $student->id,
            'mentor_id' => $otherMentor->id,
            'position' => 'EGLL_APP',
            'filed' => null,
            'cancelled_datetime' => null,
        ]);

        Availability::factory()->create([
            'student_id' => $student->id,
            'date' => $targetDate->format('Y-m-d'),
            'from' => '10:00:00',
            'to' => '12:00:00',
        ]);

        $component = Livewire::actingAs($this->mentor)
            ->test(AvailabilityGantt::class);

        $studentIds = $component->instance()->students->pluck('id');

        $this->assertFalse($studentIds->contains($student->id));
    }

    #[Test]
    public function students_property_excludes_students_with_no_availability_on_the_selected_date(): void
    {
        $student = Member::factory()->create();

        Session::factory()->create([
            'student_id' => $student->id,
            'mentor_id' => null,
            'position' => 'EGLL_APP',
            'filed' => null,
            'cancelled_datetime' => null,
        ]);

        Availability::factory()->create([
            'student_id' => $student->id,
            'date' => Carbon::tomorrow()->format('Y-m-d'),
            'from' => '10:00:00',
            'to' => '12:00:00',
        ]);

        $component = Livewire::actingAs($this->mentor)
            ->test(AvailabilityGantt::class);

        $studentIds = $component->instance()->students->pluck('id');

        $this->assertFalse($studentIds->contains($student->id));
    }

    #[Test]
    public function students_property_excludes_students_pending_in_positions_outside_mentor_callsigns(): void
    {
        $targetDate = Carbon::today();
        $student = Member::factory()->create();

        Session::factory()->create([
            'student_id' => $student->id,
            'mentor_id' => null,
            'position' => 'EGLL_TWR',
            'filed' => null,
            'cancelled_datetime' => null,
        ]);

        Availability::factory()->create([
            'student_id' => $student->id,
            'date' => $targetDate->format('Y-m-d'),
            'from' => '10:00:00',
            'to' => '12:00:00',
        ]);

        $component = Livewire::actingAs($this->mentor)
            ->test(AvailabilityGantt::class);

        $studentIds = $component->instance()->students->pluck('id');

        $this->assertFalse($studentIds->contains($student->id));
    }

    #[Test]
    public function students_are_ordered_by_last_session_date_ascending(): void
    {
        $targetDate = Carbon::today();

        $recentStudent = Member::factory()->create();
        $olderStudent = Member::factory()->create();

        foreach ([$recentStudent, $olderStudent] as $student) {
            Session::factory()->create([
                'student_id' => $student->id,
                'mentor_id' => null,
                'position' => 'EGLL_APP',
                'filed' => null,
                'cancelled_datetime' => null,
            ]);

            Availability::factory()->create([
                'student_id' => $student->id,
                'date' => $targetDate->format('Y-m-d'),
                'from' => '10:00:00',
                'to' => '12:00:00',
            ]);
        }

        Session::factory()->create([
            'student_id' => $recentStudent->id,
            'mentor_id' => $this->mentorMember->id,
            'position' => 'EGLL_APP',
            'taken_date' => Carbon::yesterday()->format('Y-m-d'),
            'filed' => now(),
        ]);

        Session::factory()->create([
            'student_id' => $olderStudent->id,
            'mentor_id' => $this->mentorMember->id,
            'position' => 'EGLL_APP',
            'taken_date' => Carbon::now()->subMonths(3)->format('Y-m-d'),
            'filed' => now(),
        ]);

        $component = Livewire::actingAs($this->mentor)
            ->test(AvailabilityGantt::class);

        $students = $component->instance()->students;

        $this->assertTrue($students->first()->id === $olderStudent->id);
        $this->assertTrue($students->last()->id === $recentStudent->id);
    }

    #[Test]
    public function student_overview_action_is_visible_when_training_place_exists(): void
    {
        $student = Member::factory()->create();
        $studentAccount = Account::factory()->create(['id' => $student->cid]);
        $session = Session::factory()->create([
            'mentor_id' => $this->mentorMember->id,
            'student_id' => $student->id,
            'position' => 'EGLL_APP',
            'taken_date' => Carbon::tomorrow()->format('Y-m-d'),
            'taken_from' => '10:00:00',
            'filed' => null,
            'cancelled_datetime' => null,
            'noShow' => 0,
        ]);

        $trainingPlace = TrainingPlace::factory()->create([
            'account_id' => $studentAccount->id,
            'training_position_id' => $this->trainingPosition->id,
        ]);

        Livewire::actingAs($this->mentor)
            ->test(AcceptedMentoringSessionsTable::class)
            ->assertSee('View Student Overview');
    }

    #[Test]
    public function student_overview_action_is_hidden_when_no_training_place_exists(): void
    {
        $student = Member::factory()->create();
        Session::factory()->create([
            'mentor_id' => $this->mentorMember->id,
            'student_id' => $student->id,
            'position' => 'EGLL_APP',
            'taken_date' => Carbon::tomorrow()->format('Y-m-d'),
            'taken_from' => '10:00:00',
            'filed' => null,
            'cancelled_datetime' => null,
            'noShow' => 0,
        ]);

        Livewire::actingAs($this->mentor)
            ->test(AcceptedMentoringSessionsTable::class)
            ->assertDontSee('View Student Overview');

    public function availability_gantt_shows_now_line_when_viewing_today(): void
    {
        Carbon::setTestNow(Carbon::today()->setTime(14, 30));

        $student = Member::factory()->create();

        Session::factory()->create([
            'student_id' => $student->id,
            'mentor_id' => null,
            'position' => 'EGLL_APP',
            'filed' => null,
            'cancelled_datetime' => null,
        ]);

        Availability::factory()->create([
            'student_id' => $student->id,
            'date' => Carbon::today()->format('Y-m-d'),
            'from' => '10:00:00',
            'to' => '18:00:00',
        ]);

        Livewire::actingAs($this->mentor)
            ->test(AvailabilityGantt::class)
            ->assertSeeHtml('data-gantt-now-line');

        Carbon::setTestNow();
    }

    #[Test]
    public function availability_gantt_does_not_show_now_line_when_viewing_a_future_date(): void
    {
        Carbon::setTestNow(Carbon::today()->setTime(14, 30));

        $student = Member::factory()->create();

        Session::factory()->create([
            'student_id' => $student->id,
            'mentor_id' => null,
            'position' => 'EGLL_APP',
            'filed' => null,
            'cancelled_datetime' => null,
        ]);

        Availability::factory()->create([
            'student_id' => $student->id,
            'date' => Carbon::tomorrow()->format('Y-m-d'),
            'from' => '10:00:00',
            'to' => '18:00:00',
        ]);

        Livewire::actingAs($this->mentor)
            ->test(AvailabilityGantt::class)
            ->call('nextDay')
            ->assertDontSeeHtml('data-gantt-now-line');

        Carbon::setTestNow();
    }
}
