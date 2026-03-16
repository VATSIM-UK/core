<?php

namespace Tests\Unit\Jobs\Training;

use App\Jobs\Training\CreateCtsSessionRequestForTrainingPlace;
use App\Models\Cts\Member;
use App\Models\Cts\Position as CtsPosition;
use App\Models\Cts\Session as CtsSession;
use App\Models\Mship\Account;
use App\Models\Training\TrainingPlace\TrainingPlace;
use App\Models\Training\TrainingPosition\TrainingPosition;
use App\Models\Training\WaitingList;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CreateCtsSessionRequestForTrainingPlaceTest extends TestCase
{
    use DatabaseTransactions;

    private Account $account;

    private Member $ctsMember;

    private TrainingPlace $trainingPlace;

    private TrainingPosition $trainingPosition;

    private string $callsign = 'EGKK_APP';

    protected function setUp(): void
    {
        parent::setUp();

        // Create CTS member first as the CID is not overwritten when using a factory
        $this->ctsMember = Member::factory()->create();
        $this->account = Account::factory()->create(['id' => $this->ctsMember->cid]);

        $waitingList = WaitingList::factory()->create();
        $waitingListAccount = $waitingList->addToWaitingList($this->account, Account::factory()->create());

        $this->trainingPosition = TrainingPosition::factory()->create([
            'cts_positions' => [],
            'cts_primary_position' => $this->callsign,
        ]);

        $this->trainingPlace = TrainingPlace::factory()->create([
            'waiting_list_account_id' => $waitingListAccount->id,
            'training_position_id' => $this->trainingPosition->id,
        ]);

        CtsPosition::factory()->create([
            'callsign' => $this->callsign,
            'rts_id' => 42,
            'prog_sheet_id' => 7,
        ]);
    }

    #[Test]
    public function it_creates_a_cts_session_request_when_none_exists(): void
    {
        (new CreateCtsSessionRequestForTrainingPlace($this->trainingPlace))->handle();

        $this->assertDatabaseHas('sessions', [
            'rts_id' => 42,
            'position' => $this->callsign,
            'progress_sheet_id' => 7,
            'student_id' => $this->ctsMember->id,
            'request_time' => $this->knownDate->toDateTimeString(),
        ], 'cts');
    }

    #[Test]
    public function it_does_not_create_a_new_request_when_an_open_request_exists(): void
    {
        CtsSession::factory()->create([
            'student_id' => $this->ctsMember->id,
            'position' => $this->callsign,
            'taken_date' => null,
        ]);

        (new CreateCtsSessionRequestForTrainingPlace($this->trainingPlace))->handle();

        $this->assertSame(
            1,
            CtsSession::query()
                ->where('student_id', $this->ctsMember->id)
                ->where('position', $this->callsign)
                ->whereNull('taken_date')
                ->count()
        );
    }

    #[Test]
    public function it_does_not_create_a_new_request_when_a_future_session_is_booked_and_not_done(): void
    {
        CtsSession::factory()->create([
            'student_id' => $this->ctsMember->id,
            'position' => $this->callsign,
            'taken_date' => now()->addDay()->format('Y-m-d'),
            'session_done' => 0,
        ]);

        (new CreateCtsSessionRequestForTrainingPlace($this->trainingPlace))->handle();

        $this->assertSame(
            1,
            CtsSession::query()
                ->where('student_id', $this->ctsMember->id)
                ->where('position', $this->callsign)
                ->whereNotNull('taken_date')
                ->count()
        );

        $this->assertSame(
            0,
            CtsSession::query()
                ->where('student_id', $this->ctsMember->id)
                ->where('position', $this->callsign)
                ->whereNull('taken_date')
                ->count()
        );
    }

    #[Test]
    public function it_skips_when_training_place_account_has_no_cts_member(): void
    {
        $accountWithoutCtsMember = Account::factory()->create(['id' => 9_999_999]);
        $waitingList = WaitingList::factory()->create();
        $waitingListAccount = $waitingList->addToWaitingList($accountWithoutCtsMember, Account::factory()->create());

        $trainingPlace = TrainingPlace::factory()->create([
            'waiting_list_account_id' => $waitingListAccount->id,
            'training_position_id' => $this->trainingPosition->id,
        ]);

        (new CreateCtsSessionRequestForTrainingPlace($trainingPlace))->handle();

        $this->assertSame(0, CtsSession::query()->where('student_id', 9_999_999)->count());
    }

    #[Test]
    public function it_skips_when_cts_primary_position_is_missing(): void
    {
        $this->trainingPosition->update(['cts_primary_position' => null]);

        $trainingPlace = $this->trainingPlace->fresh();

        $countBefore = CtsSession::query()
            ->where('student_id', $this->ctsMember->id)
            ->where('position', $this->callsign)
            ->whereNull('taken_date')
            ->count();

        (new CreateCtsSessionRequestForTrainingPlace($trainingPlace))->handle();

        $this->assertSame($countBefore, CtsSession::query()
            ->where('student_id', $this->ctsMember->id)
            ->where('position', $this->callsign)
            ->whereNull('taken_date')
            ->count());
    }

    #[Test]
    public function it_skips_when_cts_position_cannot_be_found(): void
    {
        $this->trainingPosition->update(['cts_primary_position' => 'NON_EXISTENT']);

        (new CreateCtsSessionRequestForTrainingPlace($this->trainingPlace))->handle();

        $this->assertSame(
            0,
            CtsSession::query()
                ->where('student_id', $this->ctsMember->id)
                ->where('position', 'NON_EXISTENT')
                ->whereNull('taken_date')
                ->count()
        );
    }
}
