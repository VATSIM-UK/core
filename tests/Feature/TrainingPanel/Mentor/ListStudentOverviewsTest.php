<?php

declare(strict_types=1);

namespace Tests\Feature\TrainingPanel\Mentor;

use App\Filament\Training\Pages\StudentOverview\ListStudentOverviews;
use App\Models\Cts\Member;
use App\Models\Mship\Account;
use App\Models\Training\Mentoring\MentorTrainingPosition;
use App\Models\Training\TrainingPlace\TrainingPlace;
use App\Models\Training\TrainingPosition\TrainingPosition;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\Feature\TrainingPanel\BaseTrainingPanelTestCase;

class ListStudentOverviewsTest extends BaseTrainingPanelTestCase
{
    use DatabaseTransactions;

    private Account $mentor;

    private Member $mentorMember;

    private Account $student;

    private TrainingPosition $trainingPosition;

    private TrainingPlace $trainingPlace;

    protected function setUp(): void
    {
        parent::setUp();

        $this->mentor = Account::factory()->create();
        $this->mentorMember = Member::factory()->create([
            'id' => $this->mentor->id,
            'cid' => $this->mentor->id,
        ]);

        $this->student = Account::factory()->create();
        Member::factory()->create([
            'id' => $this->student->id,
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
    public function page_renders_successfully(): void
    {
        Livewire::actingAs($this->mentor)
            ->test(ListStudentOverviews::class)
            ->assertSuccessful();
    }

    #[Test]
    public function page_groups_students_by_category(): void
    {
        Livewire::actingAs($this->mentor)
            ->test(ListStudentOverviews::class)
            ->assertSee('S3 Training');
    }

    #[Test]
    public function page_hides_students_from_categories_outside_mentor_permissions(): void
    {
        $otherPosition = TrainingPosition::factory()->create([
            'category' => 'OBS to S1 Training',
        ]);
        $otherStudent = Account::factory()->create();
        Member::factory()->create([
            'id' => $otherStudent->id,
            'cid' => $otherStudent->id,
        ]);
        TrainingPlace::factory()->create([
            'account_id' => $otherStudent->id,
            'training_position_id' => $otherPosition->id,
        ]);

        Livewire::actingAs($this->mentor)
            ->test(ListStudentOverviews::class)
            ->assertSee($this->student->name)
            ->assertDontSee($otherStudent->name);
    }

    #[Test]
    public function page_shows_all_students_to_users_with_view_all_permission(): void
    {
        $admin = Account::factory()->create();
        $admin->givePermissionTo('training.mentoring.view.*');

        $otherPosition = TrainingPosition::factory()->create([
            'category' => 'OBS to S1 Training',
        ]);
        $otherStudent = Account::factory()->create();
        Member::factory()->create([
            'id' => $otherStudent->id,
            'cid' => $otherStudent->id,
        ]);
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
    public function page_shows_empty_state_when_no_students_match(): void
    {
        $this->trainingPosition->update(['category' => null]);

        Livewire::actingAs($this->mentor)
            ->test(ListStudentOverviews::class)
            ->assertSee('No students found');
    }
}
