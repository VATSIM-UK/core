<?php

declare(strict_types=1);

namespace Tests\Feature\TrainingPanel\Training;

use App\Livewire\Training\TrainingPlaceExamCancellationsTable;
use App\Models\Cts\CancelReason;
use App\Models\Cts\ExamBooking;
use App\Models\Cts\Member;
use App\Models\Mship\Account;
use App\Models\Training\TrainingPlace\TrainingPlace;
use App\Models\Training\TrainingPosition\TrainingPosition;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\Feature\TrainingPanel\BaseTrainingPanelTestCase;

class TrainingPlaceExamCancellationsTableTest extends BaseTrainingPanelTestCase
{
    use DatabaseTransactions;

    protected TrainingPlace $trainingPlace;

    protected string $callsign = 'EGKK_APP';

    protected Member $member;

    protected function setUp(): void
    {
        parent::setUp();

        $this->trainingPlace = TrainingPlace::factory()
            ->for(TrainingPosition::factory()->state(['exam_callsign' => $this->callsign]), 'trainingPosition')->create();

        $account = Account::findOrFail($this->trainingPlace->account_id);

        $this->member = Member::factory()->create([
            'id' => $account->generateCTSInternalID($account->id),
            'cid' => $account->id,
        ]);
    }

    #[Test]
    public function it_renders_successfully(): void
    {
        Livewire::actingAs($this->panelUser)
            ->test(TrainingPlaceExamCancellationsTable::class, ['trainingPlace' => $this->trainingPlace])
            ->assertStatus(200);
    }

    #[Test]
    public function it_lists_exam_cancellations_matching_the_training_place_exam_callsign(): void
    {
        $matchingBooking = ExamBooking::factory()->create([
            'position_1' => $this->callsign,
            'student_id' => $this->member->id,
        ]);
        $matchingCancellation = CancelReason::factory()->create([
            'sesh_id' => $matchingBooking->id,
            'sesh_type' => 'EX',
            'reason' => 'Matching Exam Cancellation',
        ]);

        $wrongPositionBooking = ExamBooking::factory()->create([
            'position_1' => 'EGCC_TWR',
            'student_id' => $this->member->id,
        ]);
        $wrongPositionCancellation = CancelReason::factory()->create([
            'sesh_id' => $wrongPositionBooking->id,
            'sesh_type' => 'EX',
            'reason' => 'Wrong Position Cancellation',
        ]);

        $mentoringBooking = ExamBooking::factory()->create([
            'position_1' => $this->callsign,
            'student_id' => $this->member->id,
        ]);
        $mentoringCancellation = CancelReason::factory()->create([
            'sesh_id' => $mentoringBooking->id,
            'sesh_type' => 'ME',
            'reason' => 'Mentoring Cancellation',
        ]);

        Livewire::actingAs($this->panelUser)
            ->test(TrainingPlaceExamCancellationsTable::class, ['trainingPlace' => $this->trainingPlace])
            ->assertCanSeeTableRecords([$matchingCancellation])
            ->assertCanNotSeeTableRecords([$wrongPositionCancellation, $mentoringCancellation]);
    }

    #[Test]
    public function it_only_shows_cancellations_for_the_student(): void
    {
        $otherAccount = Account::factory()->create();
        $otherMember = Member::factory()->create([
            'id' => $otherAccount->id,
            'cid' => $otherAccount->id,
        ]);

        $studentBooking = ExamBooking::factory()->create([
            'position_1' => $this->callsign,
            'student_id' => $this->member->id,
        ]);
        $studentCancellation = CancelReason::factory()->create([
            'sesh_id' => $studentBooking->id,
            'sesh_type' => 'EX',
            'reason' => 'Student Cancellation',
        ]);

        $otherBooking = ExamBooking::factory()->create([
            'position_1' => $this->callsign,
            'student_id' => $otherMember->id,
        ]);
        $otherCancellation = CancelReason::factory()->create([
            'sesh_id' => $otherBooking->id,
            'sesh_type' => 'EX',
            'reason' => 'Other Student Cancellation',
        ]);

        Livewire::actingAs($this->panelUser)
            ->test(TrainingPlaceExamCancellationsTable::class, ['trainingPlace' => $this->trainingPlace])
            ->assertCanSeeTableRecords([$studentCancellation])
            ->assertCanNotSeeTableRecords([$otherCancellation]);
    }
}
