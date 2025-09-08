<?php

namespace Tests\Feature\TrainingPanel\Exams;

use App\Filament\Training\Pages\ExamHistory;
use App\Models\Cts\ExamBooking;
use App\Models\Cts\Member;
use App\Models\Cts\PracticalResult;
use App\Models\Mship\Account;
use App\Models\Mship\Qualification;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\Feature\TrainingPanel\BaseTrainingPanelTestCase;

class ExamHistoryTest extends BaseTrainingPanelTestCase
{
    use DatabaseTransactions;

    protected array $examBookings = [];

    protected array $practicalResults = [];

    protected function setUp(): void
    {
        parent::setUp();

        // Create exam data for all levels
        $this->createExamData();
    }

    private function createExamData(): void
    {
        $examLevels = ['OBS', 'TWR', 'APP', 'CTR'];

        foreach ($examLevels as $level) {
            // Create a student account and member
            $student = Account::factory()->create();
            $studentMember = Member::factory()->create([
                'id' => $student->id,
                'cid' => $student->id,
            ]);

            // Create an exam booking
            $examBooking = ExamBooking::factory()->create([
                'taken' => 1,
                'finished' => ExamBooking::FINISHED_FLAG,
                'exam' => $level,
                'student_id' => $studentMember->id,
                'student_rating' => Qualification::code('S1')->first()->vatsim,
                'position_1' => 'EGKK_'.$level,
            ]);

            // Create examiners
            $examBooking->examiners()->create([
                'examid' => $examBooking->id,
                'senior' => $this->panelUser->id,
            ]);

            // Create a practical result
            $practicalResult = PracticalResult::factory()->create([
                'examid' => $examBooking->id,
                'student_id' => $studentMember->id,
                'result' => PracticalResult::PASSED,
                'exam' => $level,
                'date' => now()->subDays(rand(1, 30)),
            ]);

            $this->examBookings[$level] = $examBooking;
            $this->practicalResults[$level] = $practicalResult;
        }
    }

    #[Test]
    public function it_loads_if_user_has_basic_access()
    {
        $this->panelUser->givePermissionTo('training.exams.access');

        Livewire::actingAs($this->panelUser)
            ->test(ExamHistory::class)
            ->assertSuccessful();
    }

    #[Test]
    public function it_does_not_load_if_user_lacks_basic_access()
    {
        Livewire::actingAs($this->panelUser)
            ->test(ExamHistory::class)
            ->assertForbidden();
    }

    #[Test]
    public function it_shows_no_exams_when_user_has_no_conduct_permissions()
    {
        $this->panelUser->givePermissionTo('training.exams.access');

        $component = Livewire::actingAs($this->panelUser)
            ->test(ExamHistory::class)
            ->assertSuccessful();

        // Should show no table data since user has no conduct permissions
        $component->assertDontSee($this->practicalResults['OBS']->student->account->name);
        $component->assertDontSee($this->practicalResults['TWR']->student->account->name);
        $component->assertDontSee($this->practicalResults['APP']->student->account->name);
        $component->assertDontSee($this->practicalResults['CTR']->student->account->name);
    }

    #[Test]
    public function it_shows_only_obs_exams_when_user_has_obs_permission()
    {
        $this->panelUser->givePermissionTo(['training.exams.access', 'training.exams.conduct.obs']);

        $component = Livewire::actingAs($this->panelUser)
            ->test(ExamHistory::class)
            ->assertSuccessful();

        // Should show OBS exams
        $component->assertSee($this->practicalResults['OBS']->student->account->name);
        $component->assertSee('OBS');

        // Should not show other exam types
        $component->assertDontSee($this->practicalResults['TWR']->student->account->name);
        $component->assertDontSee($this->practicalResults['APP']->student->account->name);
        $component->assertDontSee($this->practicalResults['CTR']->student->account->name);
    }

    #[Test]
    public function it_shows_only_twr_exams_when_user_has_twr_permission()
    {
        $this->panelUser->givePermissionTo(['training.exams.access', 'training.exams.conduct.twr']);

        $component = Livewire::actingAs($this->panelUser)
            ->test(ExamHistory::class)
            ->assertSuccessful();

        // Should show TWR exams
        $component->assertSee($this->practicalResults['TWR']->student->account->name);
        $component->assertSee('TWR');

        // Should not show other exam types
        $component->assertDontSee($this->practicalResults['OBS']->student->account->name);
        $component->assertDontSee($this->practicalResults['APP']->student->account->name);
        $component->assertDontSee($this->practicalResults['CTR']->student->account->name);
    }

    #[Test]
    public function it_shows_only_app_exams_when_user_has_app_permission()
    {
        $this->panelUser->givePermissionTo(['training.exams.access', 'training.exams.conduct.app']);

        $component = Livewire::actingAs($this->panelUser)
            ->test(ExamHistory::class)
            ->assertSuccessful();

        // Should show APP exams
        $component->assertSee($this->practicalResults['APP']->student->account->name);
        $component->assertSee('APP');

        // Should not show other exam types
        $component->assertDontSee($this->practicalResults['OBS']->student->account->name);
        $component->assertDontSee($this->practicalResults['TWR']->student->account->name);
        $component->assertDontSee($this->practicalResults['CTR']->student->account->name);
    }

    #[Test]
    public function it_shows_only_ctr_exams_when_user_has_ctr_permission()
    {
        $this->panelUser->givePermissionTo(['training.exams.access', 'training.exams.conduct.ctr']);

        $component = Livewire::actingAs($this->panelUser)
            ->test(ExamHistory::class)
            ->assertSuccessful();

        // Should show CTR exams
        $component->assertSee($this->practicalResults['CTR']->student->account->name);
        $component->assertSee('CTR');

        // Should not show other exam types
        $component->assertDontSee($this->practicalResults['OBS']->student->account->name);
        $component->assertDontSee($this->practicalResults['TWR']->student->account->name);
        $component->assertDontSee($this->practicalResults['APP']->student->account->name);
    }

    #[Test]
    public function it_shows_multiple_exam_types_when_user_has_multiple_permissions()
    {
        $this->panelUser->givePermissionTo([
            'training.exams.access',
            'training.exams.conduct.obs',
            'training.exams.conduct.twr',
        ]);

        $component = Livewire::actingAs($this->panelUser)
            ->test(ExamHistory::class)
            ->assertSuccessful();

        // Should show OBS and TWR exams
        $component->assertSee($this->practicalResults['OBS']->student->account->name);
        $component->assertSee($this->practicalResults['TWR']->student->account->name);
        $component->assertSee('OBS');
        $component->assertSee('TWR');

        // Should not show APP and CTR exams
        $component->assertDontSee($this->practicalResults['APP']->student->account->name);
        $component->assertDontSee($this->practicalResults['CTR']->student->account->name);
    }

    #[Test]
    public function it_shows_all_exam_types_when_user_has_all_permissions()
    {
        $this->panelUser->givePermissionTo([
            'training.exams.access',
            'training.exams.conduct.obs',
            'training.exams.conduct.twr',
            'training.exams.conduct.app',
            'training.exams.conduct.ctr',
        ]);

        $component = Livewire::actingAs($this->panelUser)
            ->test(ExamHistory::class)
            ->assertSuccessful();

        // Should show all exam types
        $component->assertSee($this->practicalResults['OBS']->student->account->name);
        $component->assertSee($this->practicalResults['TWR']->student->account->name);
        $component->assertSee($this->practicalResults['APP']->student->account->name);
        $component->assertSee($this->practicalResults['CTR']->student->account->name);

        $component->assertSee('OBS');
        $component->assertSee('TWR');
        $component->assertSee('APP');
        $component->assertSee('CTR');
    }

    #[Test]
    public function it_displays_correct_exam_result_badges()
    {
        // Update some results to different states
        $this->practicalResults['OBS']->update(['result' => PracticalResult::PASSED]);
        $this->practicalResults['TWR']->update(['result' => PracticalResult::FAILED]);
        $this->practicalResults['APP']->update(['result' => PracticalResult::INCOMPLETE]);

        $this->panelUser->givePermissionTo([
            'training.exams.access',
            'training.exams.conduct.obs',
            'training.exams.conduct.twr',
            'training.exams.conduct.app',
        ]);

        $component = Livewire::actingAs($this->panelUser)
            ->test(ExamHistory::class)
            ->assertSuccessful();

        // Should show correct result badges
        $component->assertSee('Passed');
        $component->assertSee('Failed');
        $component->assertSee('Incomplete');
    }

    #[Test]
    public function it_displays_exam_information_correctly()
    {
        $this->panelUser->givePermissionTo(['training.exams.access', 'training.exams.conduct.twr']);

        $component = Livewire::actingAs($this->panelUser)
            ->test(ExamHistory::class)
            ->assertSuccessful();

        $twr = $this->practicalResults['TWR'];

        // Should show exam information
        $component->assertSee($twr->student->account->id); // CID
        $component->assertSee($twr->student->account->name); // Name
        $component->assertSee('TWR'); // Exam type
        $component->assertSee($twr->examBooking->position_1); // Position
    }

    #[Test]
    public function it_has_view_action_for_each_exam()
    {
        $this->panelUser->givePermissionTo(['training.exams.access', 'training.exams.conduct.twr']);

        $component = Livewire::actingAs($this->panelUser)
            ->test(ExamHistory::class)
            ->assertSuccessful();

        // Should have view action (the URL will be tested separately)
        $component->assertSee('View');
    }

    #[Test]
    public function it_filters_by_exam_level_case_insensitive()
    {
        // Test that lowercase permissions match uppercase exam levels
        $this->panelUser->givePermissionTo(['training.exams.access', 'training.exams.conduct.twr']);

        $component = Livewire::actingAs($this->panelUser)
            ->test(ExamHistory::class)
            ->assertSuccessful();

        // Should show TWR exam even though permission is lowercase 'twr'
        $component->assertSee($this->practicalResults['TWR']->student->account->name);
        $component->assertSee('TWR');
    }

    #[Test]
    public function it_handles_missing_exam_booking_gracefully()
    {
        // Create a practical result without an exam booking
        $orphanStudent = Account::factory()->create();
        $orphanStudentMember = Member::factory()->create([
            'id' => $orphanStudent->id,
            'cid' => $orphanStudent->id,
        ]);

        // Use a smaller examid that fits in the database column
        PracticalResult::factory()->create([
            'examid' => 9999, // Non-existent exam booking (smaller number)
            'student_id' => $orphanStudentMember->id,
            'result' => PracticalResult::PASSED,
            'exam' => 'TWR',
            'date' => now(),
        ]);

        $this->panelUser->givePermissionTo(['training.exams.access', 'training.exams.conduct.twr']);

        // Should load without errors even with orphaned data
        Livewire::actingAs($this->panelUser)
            ->test(ExamHistory::class)
            ->assertSuccessful();
    }
}
