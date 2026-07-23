<?php

declare(strict_types=1);

namespace Tests\Feature\TrainingPanel\Exams;

use App\Filament\Training\Pages\Exam\UpcomingExams;
use App\Models\Cts\ExamBooking;
use App\Models\Cts\Member;
use App\Models\Mship\Account;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\Feature\TrainingPanel\BaseTrainingPanelTestCase;

class UpcomingExamsTest extends BaseTrainingPanelTestCase
{
    use DatabaseTransactions;

    #[Test]
    public function it_loads_when_user_has_wildcard_permission(): void
    {
        $this->panelUser->givePermissionTo('training.exams.view-upcoming.*');

        Livewire::actingAs($this->panelUser)
            ->test(UpcomingExams::class)
            ->assertSuccessful();
    }

    #[Test]
    public function it_loads_when_user_has_atc_permission(): void
    {
        $this->panelUser->givePermissionTo('training.exams.view-upcoming.atc');

        Livewire::actingAs($this->panelUser)
            ->test(UpcomingExams::class)
            ->assertSuccessful();
    }

    #[Test]
    public function it_loads_when_user_has_pilot_permission(): void
    {
        $this->panelUser->givePermissionTo('training.exams.view-upcoming.pilot');

        Livewire::actingAs($this->panelUser)
            ->test(UpcomingExams::class)
            ->assertSuccessful();
    }

    #[Test]
    public function it_is_forbidden_when_user_has_no_view_upcoming_permission(): void
    {
        Livewire::actingAs($this->panelUser)
            ->test(UpcomingExams::class)
            ->assertForbidden();
    }

    #[Test]
    public function it_is_forbidden_when_user_only_has_exams_access(): void
    {
        $this->panelUser->givePermissionTo('training.exams.access');

        Livewire::actingAs($this->panelUser)
            ->test(UpcomingExams::class)
            ->assertForbidden();
    }

    #[Test]
    public function it_defaults_to_all_when_both_atc_and_pilot_are_visible(): void
    {
        $this->panelUser->givePermissionTo('training.exams.view-upcoming.*');

        Livewire::actingAs($this->panelUser)
            ->test(UpcomingExams::class)
            ->assertSet('category', 'all');
    }

    #[Test]
    public function it_defaults_to_atc_when_only_atc_permission_is_granted(): void
    {
        $this->panelUser->givePermissionTo('training.exams.view-upcoming.atc');

        Livewire::actingAs($this->panelUser)
            ->test(UpcomingExams::class)
            ->assertSet('category', 'atc');
    }

    #[Test]
    public function it_defaults_to_pilot_when_only_pilot_permission_is_granted(): void
    {
        $this->panelUser->givePermissionTo('training.exams.view-upcoming.pilot');

        Livewire::actingAs($this->panelUser)
            ->test(UpcomingExams::class)
            ->assertSet('category', 'pilot');
    }

    #[Test]
    public function it_resets_to_all_when_category_is_invalid_and_both_are_visible(): void
    {
        $this->panelUser->givePermissionTo('training.exams.view-upcoming.*');

        Livewire::actingAs($this->panelUser)
            ->test(UpcomingExams::class, ['category' => 'not-a-real-category'])
            ->assertSet('category', 'all');
    }

    #[Test]
    public function it_resets_to_visible_category_when_category_is_invalid(): void
    {
        $this->panelUser->givePermissionTo('training.exams.view-upcoming.atc');

        Livewire::actingAs($this->panelUser)
            ->test(UpcomingExams::class, ['category' => 'pilot'])
            ->assertSet('category', 'atc');
    }

    #[Test]
    public function it_shows_future_accepted_exams(): void
    {
        $this->panelUser->givePermissionTo('training.exams.view-upcoming.*');

        $student = $this->createStudent();
        $exam = $this->createExam([
            'student_id' => $student->id,
            'taken_date' => Carbon::tomorrow()->format('Y-m-d'),
        ]);

        Livewire::actingAs($this->panelUser)
            ->test(UpcomingExams::class, ['category' => 'all'])
            ->assertCanSeeTableRecords([$exam]);
    }

    #[Test]
    public function it_does_not_show_past_exams(): void
    {
        $this->panelUser->givePermissionTo('training.exams.view-upcoming.*');

        $student = $this->createStudent();
        $exam = $this->createExam([
            'student_id' => $student->id,
            'taken_date' => Carbon::yesterday()->format('Y-m-d'),
        ]);

        Livewire::actingAs($this->panelUser)
            ->test(UpcomingExams::class, ['category' => 'all'])
            ->assertCanNotSeeTableRecords([$exam]);
    }

    #[Test]
    public function it_does_not_show_unaccepted_exams(): void
    {
        $this->panelUser->givePermissionTo('training.exams.view-upcoming.*');

        $student = $this->createStudent();
        $exam = $this->createExam([
            'student_id' => $student->id,
            'taken' => 0,
            'taken_date' => Carbon::tomorrow()->format('Y-m-d'),
        ]);

        Livewire::actingAs($this->panelUser)
            ->test(UpcomingExams::class, ['category' => 'all'])
            ->assertCanNotSeeTableRecords([$exam]);
    }

    #[Test]
    public function it_does_not_show_finished_exams(): void
    {
        $this->panelUser->givePermissionTo('training.exams.view-upcoming.*');

        $student = $this->createStudent();
        $exam = $this->createExam([
            'student_id' => $student->id,
            'finished' => ExamBooking::FINISHED_FLAG,
            'taken_date' => Carbon::tomorrow()->format('Y-m-d'),
        ]);

        Livewire::actingAs($this->panelUser)
            ->test(UpcomingExams::class, ['category' => 'all'])
            ->assertCanNotSeeTableRecords([$exam]);
    }

    #[Test]
    public function it_does_not_show_exams_without_examiners(): void
    {
        $this->panelUser->givePermissionTo('training.exams.view-upcoming.*');

        $exam = $this->createExam([
            'taken_date' => Carbon::tomorrow()->format('Y-m-d'),
        ], withExaminers: false);

        Livewire::actingAs($this->panelUser)
            ->test(UpcomingExams::class, ['category' => 'all'])
            ->assertCanNotSeeTableRecords([$exam]);
    }

    #[Test]
    public function it_does_not_show_exams_today_that_have_already_started(): void
    {
        $this->panelUser->givePermissionTo('training.exams.view-upcoming.*');

        $student = $this->createStudent();
        $exam = $this->createExam([
            'student_id' => $student->id,
            'taken_date' => now()->format('Y-m-d'),
            'taken_from' => now()->subHour()->format('H:i:s'),
        ]);

        Livewire::actingAs($this->panelUser)
            ->test(UpcomingExams::class, ['category' => 'all'])
            ->assertCanNotSeeTableRecords([$exam]);
    }

    #[Test]
    public function it_shows_exams_today_that_are_still_in_the_future(): void
    {
        $this->panelUser->givePermissionTo('training.exams.view-upcoming.*');

        $student = $this->createStudent();
        $exam = $this->createExam([
            'student_id' => $student->id,
            'taken_date' => now()->format('Y-m-d'),
            'taken_from' => now()->addHours(2)->format('H:i:s'),
        ]);

        Livewire::actingAs($this->panelUser)
            ->test(UpcomingExams::class, ['category' => 'all'])
            ->assertCanSeeTableRecords([$exam]);
    }

    #[Test]
    public function it_shows_only_atc_exams_when_atc_category_is_selected(): void
    {
        $this->panelUser->givePermissionTo('training.exams.view-upcoming.*');

        $atcExam = $this->createExamWithLevel('OBS');
        $pilotExam = $this->createExamWithLevel('P1');

        Livewire::actingAs($this->panelUser)
            ->test(UpcomingExams::class, ['category' => 'atc'])
            ->assertCanSeeTableRecords([$atcExam])
            ->assertCanNotSeeTableRecords([$pilotExam]);
    }

    #[Test]
    public function it_shows_only_pilot_exams_when_pilot_category_is_selected(): void
    {
        $this->panelUser->givePermissionTo('training.exams.view-upcoming.*');

        $atcExam = $this->createExamWithLevel('OBS');
        $pilotExam = $this->createExamWithLevel('P1');

        Livewire::actingAs($this->panelUser)
            ->test(UpcomingExams::class, ['category' => 'pilot'])
            ->assertCanSeeTableRecords([$pilotExam])
            ->assertCanNotSeeTableRecords([$atcExam]);
    }

    #[Test]
    public function it_shows_all_exam_types_when_all_category_is_selected(): void
    {
        $this->panelUser->givePermissionTo('training.exams.view-upcoming.*');

        $atcExam = $this->createExamWithLevel('OBS');
        $pilotExam = $this->createExamWithLevel('P1');

        Livewire::actingAs($this->panelUser)
            ->test(UpcomingExams::class, ['category' => 'all'])
            ->assertCanSeeTableRecords([$atcExam, $pilotExam]);
    }

    #[Test]
    public function it_shows_all_atc_levels_when_atc_category_is_selected(): void
    {
        $this->panelUser->givePermissionTo('training.exams.view-upcoming.*');

        $obsExam = $this->createExamWithLevel('OBS');
        $twrExam = $this->createExamWithLevel('TWR');
        $appExam = $this->createExamWithLevel('APP');
        $ctrExam = $this->createExamWithLevel('CTR');

        Livewire::actingAs($this->panelUser)
            ->test(UpcomingExams::class, ['category' => 'atc'])
            ->assertCanSeeTableRecords([$obsExam, $twrExam, $appExam, $ctrExam]);
    }

    #[Test]
    public function it_shows_all_pilot_levels_when_pilot_category_is_selected(): void
    {
        $this->panelUser->givePermissionTo('training.exams.view-upcoming.*');

        $p1Exam = $this->createExamWithLevel('P1');
        $p2Exam = $this->createExamWithLevel('P2');
        $p3Exam = $this->createExamWithLevel('P3');

        Livewire::actingAs($this->panelUser)
            ->test(UpcomingExams::class, ['category' => 'pilot'])
            ->assertCanSeeTableRecords([$p1Exam, $p2Exam, $p3Exam]);
    }

    #[Test]
    public function it_shows_category_switcher_when_both_categories_are_visible(): void
    {
        $this->panelUser->givePermissionTo('training.exams.view-upcoming.*');

        Livewire::actingAs($this->panelUser)
            ->test(UpcomingExams::class)
            ->assertSee('Training Department:')
            ->assertSee('All');
    }

    #[Test]
    public function the_category_switcher_label_reflects_current_selection(): void
    {
        $this->panelUser->givePermissionTo('training.exams.view-upcoming.*');

        Livewire::actingAs($this->panelUser)
            ->test(UpcomingExams::class, ['category' => 'atc'])
            ->assertSee('Training Department: ATC');
    }

    private function createStudent(): Member
    {
        $account = Account::factory()->create();

        return Member::factory()->create([
            'id' => $account->id,
            'cid' => $account->id,
        ]);
    }

    private function createExam(
        array $overrides = [],
        ?array $examinerOverrides = null,
        bool $withExaminers = true,
    ): ExamBooking {
        $exam = ExamBooking::factory()->create(array_merge([
            'exam' => 'TWR',
            'taken_date' => Carbon::parse('2026-01-01')->format('Y-m-d'),
            'taken_from' => '10:00:00',
            'taken_to' => '11:00:00',
        ], $overrides));

        if ($withExaminers) {
            $exam->examiners()->create(array_merge([
                'examid' => $exam->id,
                'senior' => $this->panelUser->id,
            ], $examinerOverrides ?? []));
        }

        return $exam->fresh(['student', 'examiners.primaryExaminer']);
    }

    private function createExamWithLevel(string $level): ExamBooking
    {
        $student = $this->createStudent();

        $exam = ExamBooking::factory()->create([
            'student_id' => $student->id,
            'exam' => $level,
            'position_1' => match ($level) {
                'OBS' => 'OBS_CC_PT2',
                'TWR' => 'EGKK_TWR',
                'APP' => 'EGKK_APP',
                'CTR' => 'EGTT_CTR',
                'P1' => 'P1_PPL(A)',
                'P2' => 'P2_SEIR(A)',
                'P3' => 'P3_CMEL(A)',
                default => 'EGKK_TWR',
            },
            'taken_date' => Carbon::tomorrow()->format('Y-m-d'),
            'taken_from' => '10:00:00',
            'taken_to' => '11:00:00',
        ]);

        $exam->examiners()->create([
            'examid' => $exam->id,
            'senior' => $this->panelUser->id,
        ]);

        return $exam->fresh(['student', 'examiners.primaryExaminer']);
    }
}
