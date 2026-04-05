<?php

namespace Tests\Feature\TrainingPanel\TrainingPlace;

use App\Filament\Training\Pages\TrainingPlace\ViewTrainingPlace;
use App\Models\Atc\Position;
use App\Models\Cts\Member;
use App\Models\Cts\Position as CtsPosition;
use App\Models\Mship\Account;
use App\Models\Mship\State;
use App\Models\Training\TrainingPlace\TrainingPlace;
use App\Models\Training\TrainingPosition\TrainingPosition;
use App\Models\Training\WaitingList;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\Feature\TrainingPanel\BaseTrainingPanelTestCase;

/**
 * Observer side effects must run; ViewTrainingPlaceTest fakes all events in setUp.
 */
class ViewTrainingPlaceRestoreIntegrationTest extends BaseTrainingPanelTestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        $this->panelUser->givePermissionTo('training-places.view.*');
        $this->panelUser->givePermissionTo('training-places.restore.*');

        Livewire::actingAs($this->panelUser);
    }

    #[Test]
    public function it_can_restore_training_place_and_adds_account_note(): void
    {
        $trainingPlace = $this->createTrainingPlace();
        $callsign = $trainingPlace->trainingPosition->position->callsign;
        $trainingPlace->delete();

        Livewire::test(ViewTrainingPlace::class, ['trainingPlaceId' => $trainingPlace->id])
            ->callAction('restoreTrainingPlace')
            ->assertNotified();

        $this->assertNotSoftDeleted('training_places', ['id' => $trainingPlace->id]);

        $this->assertDatabaseHas('mship_account_note', [
            'account_id' => $trainingPlace->waitingListAccount->account->id,
            'content' => "Training place restored on {$callsign}.",
        ]);
    }

    private function createTrainingPlace(): TrainingPlace
    {
        $ctsPosition = CtsPosition::factory()->create();
        $student = Account::factory()->create();
        $student->addState(State::findByCode('DIVISION'));
        Member::factory()->create(['id' => $student->id, 'cid' => $student->id]);
        $waitingList = WaitingList::factory()->create(['department' => 'atc']);
        $waitingListAccount = $waitingList->addToWaitingList($student, $this->panelUser);
        $position = Position::factory()->create();
        $trainingPosition = TrainingPosition::factory()
            ->withCtsPositions([$ctsPosition->callsign])
            ->create([
                'position_id' => $position->id,
                'created_at' => now()->subDays(14),
            ]);

        return TrainingPlace::factory()->create([
            'waiting_list_account_id' => $waitingListAccount->id,
            'training_position_id' => $trainingPosition->id,
        ]);
    }
}
