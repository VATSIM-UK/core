<?php

namespace Tests\Feature\TrainingPanel\WaitingLists\Pages;

use App\Filament\Training\Resources\WaitingListResource\RelationManagers\AccountsRelationManager;
use App\Models\Cts\Member;
use App\Models\Mship\Account;
use App\Models\Training\TrainingPosition\TrainingPosition;
use App\Models\Training\WaitingList;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\Feature\TrainingPanel\BaseTrainingPanelTestCase;

class ManualTrainingPlaceCreationFromWaitingListTest extends BaseTrainingPanelTestCase
{
    use DatabaseTransactions;

    #[Test]
    public function it_renders_the_relation_manager()
    {
        $waitingList = WaitingList::factory()->create();

        Livewire::actingAs($this->privacc)
            ->test(AccountsRelationManager::class, [
                'ownerRecord' => $waitingList,
                'pageClass' => ViewRecord::class,
            ])
            ->assertSuccessful();
    }

    #[Test]
    public function it_can_manual_setup_a_training_place()
    {
        // Arrange
        $ctsPosition = \App\Models\Cts\Position::factory()->create(['callsign' => 'EGLL_TWR']);
        $trainingPosition = TrainingPosition::factory()->withCtsPositions([$ctsPosition->callsign])->create();
        $waitingList = WaitingList::factory()->create();
        $waitingList->trainingPositions()->attach($trainingPosition);

        $student = Account::factory()->create();
        Member::factory()->create(['cid' => $student->id]);

        $waitingListAccount = $waitingList->addToWaitingList($student, $this->privacc);

        // Act - Use privacc who has all permissions
        Livewire::actingAs($this->privacc)
            ->test(AccountsRelationManager::class, [
                'ownerRecord' => $waitingList,
                'pageClass' => ViewRecord::class,
            ])
            ->callTableAction('manualSetupTrainingPlace', $waitingListAccount, [
                'training_position_id' => $trainingPosition->id,
            ])
            ->assertHasNoTableActionErrors();

        // Assert: Training place should be created
        $this->assertDatabaseHas('training_places', [
            'waiting_list_account_id' => $waitingListAccount->id,
            'training_position_id' => $trainingPosition->id,
        ]);

        // Assert: User should be removed from waiting list (soft deleted)
        $this->assertNotNull($waitingListAccount->fresh()->deleted_at);
    }

    #[Test]
    public function it_creates_training_place_with_multiple_linked_positions()
    {
        // Arrange - Create multiple training positions linked to the waiting list
        $ctsPosition1 = \App\Models\Cts\Position::factory()->create(['callsign' => 'EGLL_TWR']);
        $ctsPosition2 = \App\Models\Cts\Position::factory()->create(['callsign' => 'EGLL_APP']);
        $position1 = TrainingPosition::factory()->withCtsPositions([$ctsPosition1->callsign])->create();
        $position2 = TrainingPosition::factory()->withCtsPositions([$ctsPosition2->callsign])->create();

        $waitingList = WaitingList::factory()->create();
        $waitingList->trainingPositions()->attach([$position1->id, $position2->id]);

        $student1 = Account::factory()->create();
        $student2 = Account::factory()->create();
        Member::factory()->create(['cid' => $student1->id]);
        Member::factory()->create(['cid' => $student2->id]);

        $waitingListAccount1 = $waitingList->addToWaitingList($student1, $this->privacc);
        $waitingListAccount2 = $waitingList->addToWaitingList($student2, $this->privacc);

        // Act - Offer first position to first student
        Livewire::actingAs($this->privacc)
            ->test(AccountsRelationManager::class, [
                'ownerRecord' => $waitingList,
                'pageClass' => ViewRecord::class,
            ])
            ->callTableAction('manualSetupTrainingPlace', $waitingListAccount1, [
                'training_position_id' => $position1->id,
            ])
            ->assertHasNoTableActionErrors();

        // Act - Offer second position to second student
        Livewire::actingAs($this->privacc)
            ->test(AccountsRelationManager::class, [
                'ownerRecord' => $waitingList,
                'pageClass' => ViewRecord::class,
            ])
            ->callTableAction('manualSetupTrainingPlace', $waitingListAccount2, [
                'training_position_id' => $position2->id,
            ])
            ->assertHasNoTableActionErrors();

        // Assert: Both training places should be created with correct positions
        $this->assertDatabaseHas('training_places', [
            'waiting_list_account_id' => $waitingListAccount1->id,
            'training_position_id' => $position1->id,
        ]);

        $this->assertDatabaseHas('training_places', [
            'waiting_list_account_id' => $waitingListAccount2->id,
            'training_position_id' => $position2->id,
        ]);
    }

    #[Test]
    public function it_requires_training_position_to_be_selected()
    {
        // Arrange
        $trainingPosition = TrainingPosition::factory()->create();
        $waitingList = WaitingList::factory()->create();
        $waitingList->trainingPositions()->attach($trainingPosition);

        $student = Account::factory()->create();
        Member::factory()->create(['cid' => $student->id]);

        $waitingListAccount = $waitingList->addToWaitingList($student, $this->privacc);

        // Act & Assert
        Livewire::actingAs($this->privacc)
            ->test(AccountsRelationManager::class, [
                'ownerRecord' => $waitingList,
                'pageClass' => ViewRecord::class,
            ])
            ->callTableAction('manualSetupTrainingPlace', $waitingListAccount, [
                'training_position_id' => null,
            ])
            ->assertHasTableActionErrors(['training_position_id' => 'required']);
    }

    #[Test]
    public function it_shows_manual_setup_training_place_action_only_with_permission()
    {
        // Arrange
        $waitingList = WaitingList::factory()->create();
        $student = Account::factory()->create();
        Member::factory()->create(['cid' => $student->id]);
        $waitingListAccount = $waitingList->addToWaitingList($student, $this->privacc);

        // User without permission
        $userWithoutPermission = Account::factory()->create();

        // Act & Assert - Action should be hidden without permission
        Livewire::actingAs($userWithoutPermission)
            ->test(AccountsRelationManager::class, [
                'ownerRecord' => $waitingList,
                'pageClass' => ViewRecord::class,
            ])
            ->assertTableActionHidden('manualSetupTrainingPlace', $waitingListAccount);

        // Act & Assert - Action should be visible with permission (privacc has all permissions)
        Livewire::actingAs($this->privacc)
            ->test(AccountsRelationManager::class, [
                'ownerRecord' => $waitingList,
                'pageClass' => ViewRecord::class,
            ])
            ->assertTableActionVisible('manualSetupTrainingPlace', $waitingListAccount);
    }

    #[Test]
    public function it_sends_success_notification_after_manual_setup_training_place()
    {
        // Arrange
        $ctsPosition = \App\Models\Cts\Position::factory()->create(['callsign' => 'EGKK_APP']);
        $trainingPosition = TrainingPosition::factory()->withCtsPositions([$ctsPosition->callsign])->create();
        $waitingList = WaitingList::factory()->create();
        $waitingList->trainingPositions()->attach($trainingPosition);

        $student = Account::factory()->create();
        Member::factory()->create(['cid' => $student->id]);

        $waitingListAccount = $waitingList->addToWaitingList($student, $this->privacc);

        // Act & Assert
        Livewire::actingAs($this->privacc)
            ->test(AccountsRelationManager::class, [
                'ownerRecord' => $waitingList,
                'pageClass' => ViewRecord::class,
            ])
            ->callTableAction('manualSetupTrainingPlace', $waitingListAccount, [
                'training_position_id' => $trainingPosition->id,
            ])
            ->assertNotified();
    }
}
