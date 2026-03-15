<?php

namespace Tests\Unit\Training\TrainingPlace;

use App\Models\Atc\Position;
use App\Models\Cts\Member;
use App\Models\Cts\Position as CtsPosition;
use App\Models\Cts\Session as CtsSession;
use App\Models\Mship\Account;
use App\Models\Training\TrainingPlace\TrainingPlace;
use App\Models\Training\TrainingPosition\TrainingPosition;
use App\Models\Training\WaitingList;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class TrainingPlaceDeletionTest extends TestCase
{
    use DatabaseTransactions;

    public function test_delete_pending_session_requests(): void
    {
        $student = Account::factory()->create();
        $member = Member::factory()->create(['cid' => $student->id]);
        $admin = Account::factory()->create();

        $waitingList = WaitingList::factory()->create();
        $waitingListAccount = $waitingList->addToWaitingList($student, $admin);

        $position = Position::factory()->create();
        $ctsPosition = CtsPosition::factory()->create(['callsign' => 'EGLL_APP']);
        $trainingPosition = TrainingPosition::factory()
            ->create([
                'position_id' => $position->id,
                'cts_positions' => ['EGLL_APP'],
                'cts_primary_position' => 'EGLL_APP',
            ]);

        $trainingPlace = TrainingPlace::factory()->create([
            'waiting_list_account_id' => $waitingListAccount->id,
            'training_position_id' => $trainingPosition->id,
        ]);

        CtsSession::factory()->create([
            'student_id' => $member->id,
            'position' => 'EGLL_APP',
            'taken_date' => null,
        ]);

        $this->assertTrue(
            CtsSession::query()
                ->where('student_id', $member->id)
                ->where('position', 'EGLL_APP')
                ->whereNull('taken_date')
                ->exists()
        );

        $trainingPlace->deletePendingSessionRequests();

        $this->assertFalse(
            CtsSession::query()
                ->where('student_id', $member->id)
                ->where('position', 'EGLL_APP')
                ->whereNull('taken_date')
                ->exists()
        );
    }
}
