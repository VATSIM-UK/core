<?php

namespace Tests\Feature\TrainingPanel\MyTraining;

use App\Filament\Training\Pages\MyTraining\MyPendingExams;
use App\Models\Cts\ExamBooking;
use App\Models\Cts\ExamSetup;
use App\Models\Cts\Member;
use App\Models\Mship\Account;
use App\Models\Mship\Qualification;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\Feature\TrainingPanel\BaseTrainingPanelTestCase;

class MyPendingExamsTest extends BaseTrainingPanelTestCase
{
    use DatabaseTransactions;

    protected Account $studentAccount;

    protected Member $studentMember;

    protected ExamBooking $examBooking;

    protected ExamSetup $examSetup;

    protected function setUp(): void
    {
        parent::setUp();

        $this->studentAccount = Account::factory()->create();
        $this->studentMember = Member::factory()->recycle($this->studentAccount)->create([
            'cid' => $this->studentAccount->id,
        ]);

        $this->examBooking = ExamBooking::factory()->create([
            'taken' => 0,
            'finished' => ExamBooking::NOT_FINISHED_FLAG,
            'exam' => 'TWR',
            'student_id' => $this->studentMember->id,
            'student_rating' => Qualification::code('S1')->first()->vatsim,
            'position_1' => 'EGKK_TWR',
        ]);

        $this->examSetup = ExamSetup::factory()->create([
            'bookid' => $this->examBooking->id,
            'student_id' => $this->studentMember->id,
            'exam' => 'TWR',
            'position_1' => 'EGKK_TWR',
            'setup_date' => now()->subDays(2),
        ]);

        $this->studentAccount->givePermissionTo('training.access');
    }

    #[Test]
    public function it_loads_for_member_with_training_access(): void
    {
        Livewire::actingAs($this->studentAccount)
            ->test(MyPendingExams::class)
            ->assertSuccessful();
    }

    #[Test]
    public function it_does_not_load_without_training_access(): void
    {
        $noAccessAccount = Account::factory()->create();
        Member::factory()->recycle($noAccessAccount)->create(['cid' => $noAccessAccount->id]);

        $this->actingAs($noAccessAccount)
            ->get('/training/my-pending-exams')
            ->assertNotFound();
    }

    #[Test]
    public function it_only_shows_pending_exams_belonging_to_the_authenticated_member(): void
    {
        $otherAccount = Account::factory()->create();
        $otherMember = Member::factory()->recycle($otherAccount)->create(['cid' => $otherAccount->id]);
        $otherBooking = ExamBooking::factory()->create([
            'taken' => 0,
            'finished' => ExamBooking::NOT_FINISHED_FLAG,
            'exam' => 'APP',
            'student_id' => $otherMember->id,
            'student_rating' => Qualification::code('S1')->first()->vatsim,
            'position_1' => 'EGKK_APP',
        ]);
        ExamSetup::factory()->create([
            'bookid' => $otherBooking->id,
            'student_id' => $otherMember->id,
            'exam' => 'APP',
            'setup_date' => now(),
        ]);

        $component = Livewire::actingAs($this->studentAccount)
            ->test(MyPendingExams::class)
            ->assertSuccessful();

        $records = $component->instance()->getTable()->getRecords();

        $this->assertCount(1, $records);
        $this->assertEquals($this->examBooking->id, $records->first()->id);
    }

    #[Test]
    public function it_does_not_show_finished_exams(): void
    {
        $this->examBooking->update(['finished' => ExamBooking::FINISHED_FLAG]);

        $component = Livewire::actingAs($this->studentAccount)
            ->test(MyPendingExams::class)
            ->assertSuccessful();

        $this->assertCount(0, $component->instance()->getTable()->getRecords());
    }

    #[Test]
    public function it_shows_multiple_pending_exams_when_member_has_several(): void
    {
        $secondBooking = ExamBooking::factory()->create([
            'taken' => 0,
            'finished' => ExamBooking::NOT_FINISHED_FLAG,
            'exam' => 'APP',
            'student_id' => $this->studentMember->id,
            'student_rating' => Qualification::code('S1')->first()->vatsim,
            'position_1' => 'EGKK_APP',
        ]);
        ExamSetup::factory()->create([
            'bookid' => $secondBooking->id,
            'student_id' => $this->studentMember->id,
            'exam' => 'APP',
            'setup_date' => now()->subDay(),
        ]);

        $component = Livewire::actingAs($this->studentAccount)
            ->test(MyPendingExams::class)
            ->assertSuccessful();

        $this->assertCount(2, $component->instance()->getTable()->getRecords());
    }

    #[Test]
    public function it_shows_empty_state_when_member_has_no_pending_exams(): void
    {
        $emptyAccount = Account::factory()->create();
        Member::factory()->recycle($emptyAccount)->create(['cid' => $emptyAccount->id]);
        $emptyAccount->givePermissionTo('training.access');

        $component = Livewire::actingAs($emptyAccount)
            ->test(MyPendingExams::class)
            ->assertSuccessful();

        $this->assertCount(0, $component->instance()->getTable()->getRecords());
    }
}
