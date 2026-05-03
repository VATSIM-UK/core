<?php

declare(strict_types=1);

namespace Tests\Feature\TrainingPanel\Training;

use App\Livewire\Training\ExamCancellationsTable;
use App\Models\Cts\CancelReason;
use App\Models\Cts\ExamBooking;
use App\Models\Cts\Member;
use App\Models\Mship\Account;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\Feature\TrainingPanel\BaseTrainingPanelTestCase;

class ExamCancellationsTableTest extends BaseTrainingPanelTestCase
{
    use DatabaseTransactions;

    private function createCancellation(string $examType, string $reason = 'Test Reason'): CancelReason
    {
        $studentAccount = Account::factory()->create();
        $student = Member::factory()->create(['cid' => $studentAccount->id]);

        $booking = ExamBooking::factory()->create([
            'student_id' => $student->id,
            'exam' => $examType,
            'position_1' => 'EGKK_TWR',
        ]);

        return CancelReason::factory()->create([
            'sesh_id' => $booking->id,
            'sesh_type' => 'EX',
            'reason' => $reason,
            'reason_by' => 1234567,
            'date' => now(),
        ]);
    }

    #[Test]
    public function it_renders_successfully(): void
    {
        Livewire::actingAs($this->panelUser)
            ->test(ExamCancellationsTable::class)
            ->assertStatus(200);
    }

    #[Test]
    public function it_shows_cancellations_the_user_has_permission_to_see(): void
    {
        $this->panelUser->givePermissionTo('training.exams.conduct.twr');

        $twrCancellation = $this->createCancellation('twr', 'Tower Cancellation');
        $appCancellation = $this->createCancellation('app', 'Approach Cancellation');

        Livewire::actingAs($this->panelUser)
            ->test(ExamCancellationsTable::class)
            ->assertCanSeeTableRecords([$twrCancellation])
            ->assertCanNotSeeTableRecords([$appCancellation]);
    }

    #[Test]
    public function it_can_filter_by_atc_position(): void
    {
        $this->panelUser->givePermissionTo('training.exams.conduct.twr');

        $twrCancellation = $this->createCancellation('twr');
        $twrCancellation->examBooking->update(['position_1' => 'EGCC_APP']);

        $anotherCancellation = $this->createCancellation('twr');
        $anotherCancellation->examBooking->update(['position_1' => 'EGKK_TWR']);

        Livewire::actingAs($this->panelUser)
            ->test(ExamCancellationsTable::class)
            ->filterTable('position', [
                'atc_positions' => ['TWR'],
            ])
            ->assertCanSeeTableRecords([$anotherCancellation])
            ->assertCanNotSeeTableRecords([$twrCancellation]);
    }

    #[Test]
    public function it_does_not_show_mentoring_cancellations(): void
    {
        $this->panelUser->givePermissionTo('training.exams.conduct.twr');

        $examCancellation = $this->createCancellation('twr');

        $mentoringCancellation = CancelReason::factory()->create([
            'sesh_type' => 'ME',
            'reason' => 'Mentoring Cancelled',
        ]);

        Livewire::actingAs($this->panelUser)
            ->test(ExamCancellationsTable::class)
            ->assertCanSeeTableRecords([$examCancellation])
            ->assertCanNotSeeTableRecords([$mentoringCancellation]);
    }
}
