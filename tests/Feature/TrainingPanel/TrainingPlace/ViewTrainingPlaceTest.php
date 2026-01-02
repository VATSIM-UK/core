<?php

namespace Tests\Feature\TrainingPanel\TrainingPlace;

use App\Filament\Training\Pages\TrainingPlace\ViewTrainingPlace;
use App\Models\Atc\Position;
use App\Models\Cts\Member;
use App\Models\Cts\Session;
use App\Models\Mship\Account;
use App\Models\Mship\State;
use App\Models\Training\TrainingPlace\TrainingPlace;
use App\Models\Training\TrainingPosition\TrainingPosition;
use App\Models\Training\WaitingList;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Livewire\Livewire;
use Tests\Feature\TrainingPanel\BaseTrainingPanelTestCase;

class ViewTrainingPlaceTest extends BaseTrainingPanelTestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        $this->panelUser->givePermissionTo('training-places.view.*');

        Livewire::actingAs($this->panelUser);
    }

    public function test_page_can_be_accessed_with_valid_training_place()
    {
        $trainingPlace = $this->createTrainingPlace();

        Livewire::test(ViewTrainingPlace::class, ['trainingPlaceId' => $trainingPlace->id])
            ->assertStatus(200);
    }

    public function test_page_returns_404_for_invalid_training_place_id()
    {
        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);

        Livewire::test(ViewTrainingPlace::class, ['trainingPlaceId' => 'non-existent-id']);
    }

    public function test_page_cannot_be_accessed_without_permission()
    {
        $trainingPlace = $this->createTrainingPlace();

        // Create a user without the training-places.view.* permission
        $userWithoutPermission = Account::factory()->create();
        Member::factory()->create(['id' => $userWithoutPermission->id, 'cid' => $userWithoutPermission->id]);
        $userWithoutPermission->givePermissionTo('training.access'); // Has training panel access but not training places

        Livewire::actingAs($userWithoutPermission)
            ->test(ViewTrainingPlace::class, ['trainingPlaceId' => $trainingPlace->id])
            ->assertForbidden();
    }

    public function test_page_can_be_accessed_with_permission()
    {
        $trainingPlace = $this->createTrainingPlace();

        // Create a user with the training-places.view.* permission
        $userWithPermission = Account::factory()->create();
        Member::factory()->create(['id' => $userWithPermission->id, 'cid' => $userWithPermission->id]);
        $userWithPermission->givePermissionTo('training.access');
        $userWithPermission->givePermissionTo('training-places.view.*');

        Livewire::actingAs($userWithPermission)
            ->test(ViewTrainingPlace::class, ['trainingPlaceId' => $trainingPlace->id])
            ->assertStatus(200);
    }

    public function test_infolist_displays_training_place_details()
    {
        $trainingPlace = $this->createTrainingPlace();

        Livewire::test(ViewTrainingPlace::class, ['trainingPlaceId' => $trainingPlace->id])
            ->assertStatus(200)
            ->assertSee($trainingPlace->waitingListAccount->account->name)
            ->assertSee($trainingPlace->waitingListAccount->account->id)
            ->assertSee($trainingPlace->trainingPosition->position->name);
    }

    public function test_infolist_displays_dates_correctly()
    {
        $trainingPlace = $this->createTrainingPlace();

        $formattedTrainingStart = $trainingPlace->created_at->format('d/m/Y');
        $formattedWaitingListJoin = $trainingPlace->waitingListAccount->created_at->format('d/m/Y');

        Livewire::test(ViewTrainingPlace::class, ['trainingPlaceId' => $trainingPlace->id])
            ->assertStatus(200)
            ->assertSee($formattedTrainingStart)
            ->assertSee($formattedWaitingListJoin);
    }

    public function test_table_displays_mentoring_sessions_for_training_position()
    {
        $trainingPlace = $this->createTrainingPlace();
        $cts_positions = $trainingPlace->trainingPosition->cts_positions;

        // Create a session that matches the training position
        $session = Session::factory()->create([
            'position' => $cts_positions[0],
            'taken_date' => now()->subDays(5),
            'student_id' => $trainingPlace->waitingListAccount->account->member->id,
        ]);

        Livewire::test(ViewTrainingPlace::class, ['trainingPlaceId' => $trainingPlace->id])
            ->assertStatus(200)
            ->assertSee($session->position)
            ->assertSee($session->taken_date->format('d/m/Y'));
    }

    public function test_table_does_not_display_sessions_for_other_positions()
    {
        $trainingPlace = $this->createTrainingPlace();

        // Create a session for a different position
        $otherSession = Session::factory()->create([
            'student_id' => $trainingPlace->waitingListAccount->account->member->id,
            'position' => 'EGKK_TWR', // Different position
            'taken_date' => now()->subDays(5),
        ]);

        Livewire::test(ViewTrainingPlace::class, ['trainingPlaceId' => $trainingPlace->id])
            ->assertStatus(200)
            ->assertDontSee($otherSession->position);
    }

    public function test_table_displays_mentor_information()
    {
        $trainingPlace = $this->createTrainingPlace();
        $cts_positions = $trainingPlace->trainingPosition->cts_positions;

        $mentor = Member::factory()->create();
        $session = Session::factory()->create([
            'student_id' => $trainingPlace->waitingListAccount->account->member->id,
            'position' => $cts_positions[0],
            'taken_date' => now()->subDays(5),
            'mentor_id' => $mentor->id,
        ]);

        Livewire::test(ViewTrainingPlace::class, ['trainingPlaceId' => $trainingPlace->id])
            ->assertStatus(200)
            ->assertSee($mentor->cid)
            ->assertSee($mentor->name);
    }

    public function test_table_displays_pending_status_for_incomplete_session()
    {
        $trainingPlace = $this->createTrainingPlace();
        $cts_positions = $trainingPlace->trainingPosition->cts_positions;

        $session = Session::factory()->create([
            'position' => $cts_positions[0],
            'taken_date' => now()->subDays(5),
            'student_id' => $trainingPlace->waitingListAccount->account->member->id,
            'noShow' => 0, // Explicitly set to 0 to ensure it's false
            'cancelled_datetime' => null, // Explicitly set to null
            'session_done' => 0, // Explicitly set to 0 to ensure it's false
        ]);

        Livewire::test(ViewTrainingPlace::class, ['trainingPlaceId' => $trainingPlace->id])
            ->assertStatus(200)
            ->assertSee('Pending');
    }

    public function test_table_displays_completed_status()
    {
        $trainingPlace = $this->createTrainingPlace();
        $cts_positions = $trainingPlace->trainingPosition->cts_positions;

        $session = Session::factory()->create([
            'student_id' => $trainingPlace->waitingListAccount->account->member->id,
            'position' => $cts_positions[0],
            'taken_date' => now()->subDays(5),
            'noShow' => 0,
            'cancelled_datetime' => null,
            'session_done' => 1,
        ]);

        Livewire::test(ViewTrainingPlace::class, ['trainingPlaceId' => $trainingPlace->id])
            ->assertStatus(200)
            ->assertSee($cts_positions[0]) // Verify session position appears
            ->assertSee('Completed');
    }

    public function test_table_displays_no_show_status()
    {
        $trainingPlace = $this->createTrainingPlace();
        $cts_positions = $trainingPlace->trainingPosition->cts_positions;

        $session = Session::factory()->create([
            'student_id' => $trainingPlace->waitingListAccount->account->member->id,
            'position' => $cts_positions[0],
            'taken_date' => now()->subDays(5),
            'noShow' => 1, // Explicitly set to 1 to ensure it's true
            'cancelled_datetime' => null, // Explicitly set to null
            'session_done' => 0, // Explicitly set to 0
        ]);

        Livewire::test(ViewTrainingPlace::class, ['trainingPlaceId' => $trainingPlace->id])
            ->assertStatus(200)
            ->assertSee('No Show');
    }

    public function test_table_displays_cancelled_status()
    {
        $trainingPlace = $this->createTrainingPlace();
        $cts_positions = $trainingPlace->trainingPosition->cts_positions;

        $session = Session::factory()->create([
            'student_id' => $trainingPlace->waitingListAccount->account->member->id,
            'position' => $cts_positions[0],
            'taken_date' => now()->subDays(5),
            'cancelled_datetime' => now()->subDays(6)->toDateTimeString(),
            'noShow' => 0,
            'session_done' => 0,
        ]);

        Livewire::test(ViewTrainingPlace::class, ['trainingPlaceId' => $trainingPlace->id])
            ->assertStatus(200)
            ->assertSee($cts_positions[0]) // Verify session position appears
            ->assertSee('Cancelled');
    }

    public function test_table_view_action_has_correct_url()
    {
        $trainingPlace = $this->createTrainingPlace();
        $cts_positions = $trainingPlace->trainingPosition->cts_positions;

        $session = Session::factory()->create([
            'position' => $cts_positions[0],
            'taken_date' => now()->subDays(5),
            'student_id' => $trainingPlace->waitingListAccount->account->member->id,
        ]);

        $expectedUrl = "https://cts.vatsim.uk/mentors/report.php?id={$session->id}&view=report";

        Livewire::test(ViewTrainingPlace::class, ['trainingPlaceId' => $trainingPlace->id])
            ->assertStatus(200)
            ->assertTableActionHasUrl('view', $expectedUrl, record: $session);
    }

    public function test_table_displays_empty_state_when_no_sessions()
    {
        $trainingPlace = $this->createTrainingPlace();

        Livewire::test(ViewTrainingPlace::class, ['trainingPlaceId' => $trainingPlace->id])
            ->assertStatus(200)
            ->assertSee('No mentoring sessions found');
    }

    public function test_training_place_with_multiple_cts_positions_shows_all_relevant_sessions()
    {
        $trainingPlace = $this->createTrainingPlace(['EGLL_APP', 'EGLL_TWR']);

        // Create sessions for both positions
        $session1 = Session::factory()->create([
            'student_id' => $trainingPlace->waitingListAccount->account->member->id,
            'position' => 'EGLL_APP',
            'taken_date' => now()->subDays(5),
        ]);

        $session2 = Session::factory()->create([
            'student_id' => $trainingPlace->waitingListAccount->account->member->id,
            'position' => 'EGLL_TWR',
            'taken_date' => now()->subDays(3),
        ]);

        Livewire::test(ViewTrainingPlace::class, ['trainingPlaceId' => $trainingPlace->id])
            ->assertStatus(200)
            ->assertSee($session1->position)
            ->assertSee($session2->position);
    }

    public function test_page_heading_includes_mentoring_session_history()
    {
        $trainingPlace = $this->createTrainingPlace();

        Livewire::test(ViewTrainingPlace::class, ['trainingPlaceId' => $trainingPlace->id])
            ->assertStatus(200)
            ->assertSee('Mentoring session history');
    }

    /**
     * Helper method to create a training place with all required relationships
     */
    private function createTrainingPlace(array $cts_positions = ['EGLL_APP']): TrainingPlace
    {
        // Create an account for the student
        $student = Account::factory()->create();
        $student->addState(State::findByCode('DIVISION'));

        Member::factory()->create(['id' => $student->id, 'cid' => $student->id]);

        // Create a waiting list
        $waitingList = WaitingList::factory()->create(['department' => 'atc']);

        // Add student to waiting list
        $waitingListAccount = $waitingList->addToWaitingList($student, $this->panelUser);

        // Create position
        $position = Position::factory()->create();

        // Create training position
        $trainingPosition = TrainingPosition::factory()
            ->withCtsPositions($cts_positions)
            ->create([
                'position_id' => $position->id,
                'created_at' => now()->subDays(14),
            ]);

        // Create and return training place
        return TrainingPlace::factory()->create([
            'waiting_list_account_id' => $waitingListAccount->id,
            'training_position_id' => $trainingPosition->id,
        ]);
    }
}
