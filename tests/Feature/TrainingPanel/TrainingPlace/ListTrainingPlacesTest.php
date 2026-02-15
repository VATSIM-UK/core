<?php

namespace Tests\Feature\TrainingPanel\TrainingPlace;

use App\Filament\Training\Resources\TrainingPlaceResource\Pages\ListTrainingPlaces;
use App\Models\Cts\Member;
use App\Models\Cts\Position as CtsPosition;
use App\Models\Mship\Account;
use App\Models\Training\TrainingPlace\TrainingPlace;
use App\Models\Training\TrainingPosition\TrainingPosition;
use App\Models\Training\WaitingList;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\Feature\TrainingPanel\BaseTrainingPanelTestCase;

class ListTrainingPlacesTest extends BaseTrainingPanelTestCase
{
    use DatabaseTransactions;

    #[Test]
    public function it_can_render_training_places_list_page_with_permission()
    {
        // Arrange
        $user = Account::factory()->create();
        $user->givePermissionTo('training-places.view.*');

        // Act & Assert
        Livewire::actingAs($user)
            ->test(ListTrainingPlaces::class)
            ->assertSuccessful();
    }

    #[Test]
    public function it_cannot_render_training_places_list_page_without_permission()
    {
        // Arrange
        $user = Account::factory()->create();

        // Act & Assert
        Livewire::actingAs($user)
            ->test(ListTrainingPlaces::class)
            ->assertForbidden();
    }

    #[Test]
    public function it_displays_training_places_in_table()
    {
        // Arrange
        $ctsPosition = CtsPosition::factory()->create(['callsign' => 'EGLL_TWR']);
        $trainingPosition = TrainingPosition::factory()->withCtsPositions([$ctsPosition->callsign])->create();

        $waitingList = WaitingList::factory()->create(['name' => 'Test Waiting List']);
        $student = Account::factory()->create();
        Member::factory()->create(['cid' => $student->id]);

        $waitingListAccount = $waitingList->addToWaitingList($student, $this->privacc);

        $trainingPlace = TrainingPlace::create([
            'waiting_list_account_id' => $waitingListAccount->id,
            'training_position_id' => $trainingPosition->id,
        ]);

        // Act & Assert
        Livewire::actingAs($this->privacc)
            ->test(ListTrainingPlaces::class)
            ->assertSuccessful()
            ->assertCanSeeTableRecords([$trainingPlace])
            ->assertTableColumnExists('waitingListAccount.account.name')
            ->assertTableColumnExists('waitingListAccount.account_id')
            ->assertTableColumnExists('trainingPosition.position.callsign');
    }

    #[Test]
    public function it_can_filter_by_waiting_list()
    {
        // Arrange
        $ctsPosition = CtsPosition::factory()->create(['callsign' => 'EGLL_APP']);
        $trainingPosition = TrainingPosition::factory()->withCtsPositions([$ctsPosition->callsign])->create();

        $waitingList1 = WaitingList::factory()->create(['name' => 'List One']);
        $waitingList2 = WaitingList::factory()->create(['name' => 'List Two']);

        $student1 = Account::factory()->create();
        $student2 = Account::factory()->create();
        Member::factory()->create(['cid' => $student1->id]);
        Member::factory()->create(['cid' => $student2->id]);

        $waitingListAccount1 = $waitingList1->addToWaitingList($student1, $this->privacc);
        $waitingListAccount2 = $waitingList2->addToWaitingList($student2, $this->privacc);

        $trainingPlace1 = TrainingPlace::create([
            'waiting_list_account_id' => $waitingListAccount1->id,
            'training_position_id' => $trainingPosition->id,
        ]);

        $trainingPlace2 = TrainingPlace::create([
            'waiting_list_account_id' => $waitingListAccount2->id,
            'training_position_id' => $trainingPosition->id,
        ]);

        // Act & Assert - Filter by first waiting list
        Livewire::actingAs($this->privacc)
            ->test(ListTrainingPlaces::class)
            ->filterTable('waiting_list', $waitingList1->id)
            ->assertCanSeeTableRecords([$trainingPlace1])
            ->assertCanNotSeeTableRecords([$trainingPlace2]);
    }

    #[Test]
    public function it_can_search_by_student_name()
    {
        // Arrange
        $ctsPosition = CtsPosition::factory()->create(['callsign' => 'EGKK_TWR']);
        $trainingPosition = TrainingPosition::factory()->withCtsPositions([$ctsPosition->callsign])->create();

        $waitingList = WaitingList::factory()->create();
        $student = Account::factory()->create([
            'name_first' => 'John',
            'name_last' => 'Smith',
        ]);
        Member::factory()->create(['cid' => $student->id]);

        $waitingListAccount = $waitingList->addToWaitingList($student, $this->privacc);

        $trainingPlace = TrainingPlace::create([
            'waiting_list_account_id' => $waitingListAccount->id,
            'training_position_id' => $trainingPosition->id,
        ]);

        // Act & Assert
        Livewire::actingAs($this->privacc)
            ->test(ListTrainingPlaces::class)
            ->searchTable('John')
            ->assertCanSeeTableRecords([$trainingPlace]);
    }
}
