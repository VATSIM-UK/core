<?php

declare(strict_types=1);

namespace Tests\Feature\TrainingPanel\Mentor;

use App\Filament\Training\Pages\StudentOverview\ListStudentOverviews;
use App\Filament\Training\Pages\StudentOverview\ViewStudentOverview;
use App\Livewire\Training\StudentAvailabilityTable;
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

class StudentOverviewTest extends BaseTrainingPanelTestCase
{
    use DatabaseTransactions;

    private Account $mentor;

    private Member $mentorMember;

    private Account $student;

    private Member $studentMember;

    private TrainingPosition $trainingPosition;

    private TrainingPlace $trainingPlace;

    protected function setUp(): void
    {
        parent::setUp();

        $this->mentor = Account::factory()->create();
        $this->mentorMember = Member::factory()->create([
            'id' => $this->mentor->generateCTSInternalID($this->mentor->id),
            'cid' => $this->mentor->id,
        ]);

        $this->mentor->givePermissionTo('training.beta');

        $this->student = Account::factory()->create();
        $this->studentMember = Member::factory()->create([
            'id' => $this->student->generateCTSInternalID($this->student->id),
            'cid' => $this->student->id,
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

        $this->trainingPlace = TrainingPlace::factory()->create([
            'account_id' => $this->student->id,
            'training_position_id' => $this->trainingPosition->id,
        ]);
    }

    #[Test]
    public function list_page_is_accessible_to_users_with_view_any_session_permission(): void
    {
        $this->actingAs($this->mentor);

        $this->assertTrue(ListStudentOverviews::canAccess());
    }

    #[Test]
    public function list_page_is_denied_to_users_without_session_view_permission(): void
    {
        $noAccess = Account::factory()->create();
        $this->actingAs($noAccess);

        $this->assertFalse(ListStudentOverviews::canAccess());
    }

    #[Test]
    public function list_table_shows_training_places_in_the_mentors_allowed_categories(): void
    {
        Livewire::actingAs($this->mentor)
            ->test(ListStudentOverviews::class)
            ->assertSee($this->student->name);
    }

    #[Test]
    public function list_table_hides_training_places_outside_the_mentors_allowed_categories(): void
    {
        $otherPosition = TrainingPosition::factory()->create(['category' => 'OBS to S1 Training']);
        $otherStudent = Account::factory()->create();
        TrainingPlace::factory()->create([
            'account_id' => $otherStudent->id,
            'training_position_id' => $otherPosition->id,
        ]);

        Livewire::actingAs($this->mentor)
            ->test(ListStudentOverviews::class)
            ->assertDontSee($otherStudent->name);
    }

    #[Test]
    public function list_table_shows_all_training_places_to_users_with_view_all_permission(): void
    {
        $admin = Account::factory()->create();
        $admin->givePermissionTo('training.mentoring.view.*');

        $otherPosition = TrainingPosition::factory()->create(['category' => 'OBS to S1 Training']);
        $otherStudent = Account::factory()->create();
        TrainingPlace::factory()->create([
            'account_id' => $otherStudent->id,
            'training_position_id' => $otherPosition->id,
        ]);

        Livewire::actingAs($admin)
            ->test(ListStudentOverviews::class)
            ->assertSee($this->student->name)
            ->assertSee($otherStudent->name);
    }

    #[Test]
    public function view_page_renders_successfully_for_an_authorised_mentor(): void
    {
        Livewire::actingAs($this->mentor)
            ->test(ViewStudentOverview::class, ['trainingPlaceId' => $this->trainingPlace->id])
            ->assertSuccessful();
    }

    #[Test]
    public function view_page_loads_soft_deleted_training_places(): void
    {
        $this->trainingPlace->delete();

        Livewire::actingAs($this->mentor)
            ->test(ViewStudentOverview::class, ['trainingPlaceId' => $this->trainingPlace->id])
            ->assertSuccessful();
    }

    #[Test]
    public function has_pending_session_returns_false_when_student_has_no_member_record(): void
    {
        $accountWithoutMember = Account::factory()->create();
        $place = TrainingPlace::factory()->create([
            'account_id' => $accountWithoutMember->id,
            'training_position_id' => $this->trainingPosition->id,
        ]);

        $component = Livewire::actingAs($this->mentor)
            ->test(StudentAvailabilityTable::class, ['trainingPlace' => $place]);

        $this->assertFalse($component->instance()->hasPendingSession());
    }

    #[Test]
    public function has_pending_session_returns_false_when_all_sessions_are_filed(): void
    {
        Session::factory()->create([
            'student_id' => $this->studentMember->id,
            'mentor_id' => null,
            'filed' => now(),
            'cancelled_datetime' => null,
        ]);

        $component = Livewire::actingAs($this->mentor)
            ->test(StudentAvailabilityTable::class, ['trainingPlace' => $this->trainingPlace]);

        $this->assertFalse($component->instance()->hasPendingSession());
    }

    #[Test]
    public function has_pending_session_returns_false_when_all_sessions_are_cancelled(): void
    {
        Session::factory()->create([
            'student_id' => $this->studentMember->id,
            'mentor_id' => null,
            'filed' => null,
            'cancelled_datetime' => now(),
        ]);

        $component = Livewire::actingAs($this->mentor)
            ->test(StudentAvailabilityTable::class, ['trainingPlace' => $this->trainingPlace]);

        $this->assertFalse($component->instance()->hasPendingSession());
    }

    #[Test]
    public function has_pending_session_returns_true_when_an_unfiled_uncancelled_session_exists(): void
    {
        Session::factory()->create([
            'student_id' => $this->studentMember->id,
            'mentor_id' => null,
            'filed' => null,
            'cancelled_datetime' => null,
        ]);

        $component = Livewire::actingAs($this->mentor)
            ->test(StudentAvailabilityTable::class, ['trainingPlace' => $this->trainingPlace]);

        $this->assertTrue($component->instance()->hasPendingSession());
    }

    #[Test]
    public function availability_table_shows_upcoming_slots_for_the_student(): void
    {
        Availability::factory()->create([
            'student_id' => $this->studentMember->id,
            'date' => Carbon::tomorrow()->format('Y-m-d'),
            'from' => '10:00:00',
            'to' => '12:00:00',
        ]);

        Livewire::actingAs($this->mentor)
            ->test(StudentAvailabilityTable::class, ['trainingPlace' => $this->trainingPlace])
            ->assertSee(Carbon::tomorrow()->format('d/m/Y'));
    }

    #[Test]
    public function availability_table_does_not_show_past_slots(): void
    {
        Availability::factory()->create([
            'student_id' => $this->studentMember->id,
            'date' => Carbon::yesterday()->format('Y-m-d'),
            'from' => '10:00:00',
            'to' => '12:00:00',
        ]);

        Livewire::actingAs($this->mentor)
            ->test(StudentAvailabilityTable::class, ['trainingPlace' => $this->trainingPlace])
            ->assertDontSee(Carbon::yesterday()->format('d/m/Y'));
    }

    #[Test]
    public function availability_table_shows_warning_when_student_has_no_pending_session(): void
    {
        Livewire::actingAs($this->mentor)
            ->test(StudentAvailabilityTable::class, ['trainingPlace' => $this->trainingPlace])
            ->assertSee('No Session Request');
    }

    #[Test]
    public function availability_table_does_not_show_warning_when_student_has_a_pending_session(): void
    {
        Session::factory()->create([
            'student_id' => $this->studentMember->id,
            'mentor_id' => null,
            'filed' => null,
            'cancelled_datetime' => null,
        ]);

        Livewire::actingAs($this->mentor)
            ->test(StudentAvailabilityTable::class, ['trainingPlace' => $this->trainingPlace])
            ->assertDontSee('No Session Request');
    }
}
