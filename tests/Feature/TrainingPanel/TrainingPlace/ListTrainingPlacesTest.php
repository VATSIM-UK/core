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
    public function it_can_filter_by_category()
    {
        // Arrange - two training positions with different categories
        $ctsPosition1 = CtsPosition::factory()->create(['callsign' => 'EGLL_APP']);
        $ctsPosition2 = CtsPosition::factory()->create(['callsign' => 'EGLL_TWR']);
        $trainingPositionApproach = TrainingPosition::factory()
            ->withCtsPositions([$ctsPosition1->callsign])
            ->create(['category' => 'approach']);
        $trainingPositionTower = TrainingPosition::factory()
            ->withCtsPositions([$ctsPosition2->callsign])
            ->create(['category' => 'tower']);

        $waitingList = WaitingList::factory()->create();
        $student1 = Account::factory()->create();
        $student2 = Account::factory()->create();
        Member::factory()->create(['cid' => $student1->id]);
        Member::factory()->create(['cid' => $student2->id]);

        $waitingListAccount1 = $waitingList->addToWaitingList($student1, $this->privacc);
        $waitingListAccount2 = $waitingList->addToWaitingList($student2, $this->privacc);

        $trainingPlaceApproach = TrainingPlace::create([
            'waiting_list_account_id' => $waitingListAccount1->id,
            'training_position_id' => $trainingPositionApproach->id,
        ]);

        $trainingPlaceTower = TrainingPlace::create([
            'waiting_list_account_id' => $waitingListAccount2->id,
            'training_position_id' => $trainingPositionTower->id,
        ]);

        // Act & Assert - Filter by approach category
        Livewire::actingAs($this->privacc)
            ->test(ListTrainingPlaces::class)
            ->filterTable('trainingPosition.category', 'approach')
            ->assertCanSeeTableRecords([$trainingPlaceApproach])
            ->assertCanNotSeeTableRecords([$trainingPlaceTower]);
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
