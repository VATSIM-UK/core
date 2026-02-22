<?php

namespace Tests\Feature\TrainingPanel\TrainingPlace;

use App\Livewire\Training\LeaveOfAbsencesTable;
use App\Models\Atc\Position;
use App\Models\Cts\Member;
use App\Models\Mship\Account;
use App\Models\Mship\State;
use App\Models\Training\TrainingPlace\TrainingPlace;
use App\Models\Training\TrainingPlace\TrainingPlaceLeaveOfAbsence;
use App\Models\Training\TrainingPosition\TrainingPosition;
use App\Models\Training\WaitingList;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Event;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\Feature\TrainingPanel\BaseTrainingPanelTestCase;

class TrainingPlaceLeaveOfAbsenceTest extends BaseTrainingPanelTestCase
{
    use DatabaseTransactions;

    private TrainingPlace $trainingPlace;

    protected function setUp(): void
    {
        parent::setUp();

        $this->panelUser->givePermissionTo('training-places.view.*');

        Event::fake();

        Livewire::actingAs($this->panelUser);

        $this->trainingPlace = $this->createTrainingPlace();
    }

    #[Test]
    public function it_renders_the_table_for_authorised_user()
    {
        Livewire::test(LeaveOfAbsencesTable::class, ['trainingPlace' => $this->trainingPlace])
            ->assertStatus(200)
            ->assertSee('Leaves of Absence');
    }

    #[Test]
    public function it_shows_create_action_when_user_has_create_permission()
    {
        $this->panelUser->givePermissionTo('training-places.loas.create.*');

        Livewire::test(LeaveOfAbsencesTable::class, ['trainingPlace' => $this->trainingPlace])
            ->assertTableActionVisible('create');
    }

    #[Test]
    public function it_hides_create_action_when_user_lacks_create_permission()
    {
        Livewire::test(LeaveOfAbsencesTable::class, ['trainingPlace' => $this->trainingPlace])
            ->assertTableActionHidden('create');
    }

    #[Test]
    public function it_can_create_an_loa()
    {
        $this->panelUser->givePermissionTo('training-places.loas.create.*');

        Livewire::test(LeaveOfAbsencesTable::class, ['trainingPlace' => $this->trainingPlace])
            ->callTableAction('create', data: [
                'begins_at' => '2026-04-01',
                'ends_at' => '2026-04-10',
                'reason' => 'Taking a short break.',
            ])
            ->assertHasNoTableActionErrors();

        $this->assertDatabaseHas('training_place_leave_of_absences', [
            'training_place_id' => $this->trainingPlace->id,
            'reason' => 'Taking a short break.',
        ]);
    }

    #[Test]
    public function it_prevents_creation_of_overlapping_loa()
    {
        $this->panelUser->givePermissionTo('training-places.loas.create.*');

        $this->createLoa([
            'begins_at' => Carbon::parse('2026-04-01'),
            'ends_at' => Carbon::parse('2026-04-15')->endOfDay(),
        ]);

        Livewire::test(LeaveOfAbsencesTable::class, ['trainingPlace' => $this->trainingPlace])
            ->callTableAction('create', data: [
                'begins_at' => '2026-04-10',
                'ends_at' => '2026-04-20',
                'reason' => 'This should be blocked.',
            ]);

        $this->assertDatabaseMissing('training_place_leave_of_absences', [
            'reason' => 'This should be blocked.',
        ]);
    }

    #[Test]
    public function it_shows_end_early_action_for_active_loa_with_permission()
    {
        $this->panelUser->givePermissionTo('training-places.loas.end-early.*');

        $loa = $this->createLoa([
            'begins_at' => now()->subDay(),
            'ends_at' => now()->addDays(5)->endOfDay(),
        ]);

        Livewire::test(LeaveOfAbsencesTable::class, ['trainingPlace' => $this->trainingPlace])
            ->assertTableActionVisible('end_loa_early', $loa);
    }

    #[Test]
    public function it_hides_end_early_action_for_inactive_loa()
    {
        $this->panelUser->givePermissionTo('training-places.loas.end-early.*');

        $loa = $this->createLoa([
            'begins_at' => now()->subDays(10),
            'ends_at' => now()->subDays(2)->endOfDay(),
        ]);

        Livewire::test(LeaveOfAbsencesTable::class, ['trainingPlace' => $this->trainingPlace])
            ->assertTableActionHidden('end_loa_early', $loa);
    }

    #[Test]
    public function it_hides_end_early_action_without_permission()
    {
        $loa = $this->createLoa([
            'begins_at' => now()->subDay(),
            'ends_at' => now()->addDays(5)->endOfDay(),
        ]);

        Livewire::test(LeaveOfAbsencesTable::class, ['trainingPlace' => $this->trainingPlace])
            ->assertTableActionHidden('end_loa_early', $loa);
    }

    #[Test]
    public function it_can_end_an_loa_early()
    {
        $this->panelUser->givePermissionTo('training-places.loas.end-early.*');

        $loa = $this->createLoa([
            'begins_at' => now()->subDay(),
            'ends_at' => now()->addDays(5)->endOfDay(),
        ]);

        Livewire::test(LeaveOfAbsencesTable::class, ['trainingPlace' => $this->trainingPlace])
            ->callTableAction('end_loa_early', $loa, data: [
                'reason' => 'Student resumed training early.',
            ])
            ->assertNotified();

        $this->assertTrue($loa->fresh()->ends_at->isPast());
    }

    private function createLoa(array $attributes = []): TrainingPlaceLeaveOfAbsence
    {
        return TrainingPlaceLeaveOfAbsence::create(array_merge([
            'training_place_id' => $this->trainingPlace->id,
            'begins_at' => now()->addDay(),
            'ends_at' => now()->addDays(7)->endOfDay(),
            'reason' => 'Default test reason.',
        ], $attributes));
    }

    private function createTrainingPlace(array $cts_positions = ['EGLL_APP']): TrainingPlace
    {
        $student = Account::factory()->create();
        $student->addState(State::findByCode('DIVISION'));
        Member::factory()->create(['id' => $student->id, 'cid' => $student->id]);

        $waitingList = WaitingList::factory()->create(['department' => 'atc']);
        $waitingListAccount = $waitingList->addToWaitingList($student, $this->panelUser);

        $position = Position::factory()->create();
        $trainingPosition = TrainingPosition::factory()->withCtsPositions($cts_positions)->create(['position_id' => $position->id]);

        return TrainingPlace::factory()->create([
            'waiting_list_account_id' => $waitingListAccount->id,
            'training_position_id' => $trainingPosition->id,
        ]);
    }
}
