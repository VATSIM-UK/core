<?php

namespace Tests\Feature\TrainingPanel\WaitingLists\Pages;

use App\Filament\Training\Resources\WaitingLists\RelationManagers\AccountsRelationManager;
use App\Models\Cts\Member;
use App\Models\Mship\Account;
use App\Models\Training\TrainingPosition\TrainingPosition;
use App\Models\Training\WaitingList;
use App\Models\Training\WaitingList\WaitingListAccount;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\Feature\TrainingPanel\BaseTrainingPanelTestCase;

class TrainableSelectionTest extends BaseTrainingPanelTestCase
{
    use DatabaseTransactions;

    #[DataProvider('trainingPlaceActionProvider')]
    #[Test]
    public function it_selects_the_only_linked_trainable_by_default(string $action): void
    {
        $trainingPosition = TrainingPosition::factory()->create();
        $waitingList = WaitingList::factory()->create();
        $waitingList->trainingPositions()->attach($trainingPosition);
        $waitingListAccount = $this->addStudentToWaitingList($waitingList);

        Livewire::actingAs($this->privacc)
            ->test(AccountsRelationManager::class, [
                'ownerRecord' => $waitingList,
                'pageClass' => ViewRecord::class,
            ])
            ->mountTableAction($action, $waitingListAccount)
            ->assertTableActionDataSet([
                'trainable' => TrainingPosition::class.'|'.$trainingPosition->id,
            ]);
    }

    #[DataProvider('trainingPlaceActionProvider')]
    #[Test]
    public function it_does_not_select_a_trainable_by_default_when_multiple_are_linked(string $action): void
    {
        $trainingPositions = TrainingPosition::factory()->count(2)->create();
        $waitingList = WaitingList::factory()->create();
        $waitingList->trainingPositions()->attach($trainingPositions);
        $waitingListAccount = $this->addStudentToWaitingList($waitingList);

        Livewire::actingAs($this->privacc)
            ->test(AccountsRelationManager::class, [
                'ownerRecord' => $waitingList,
                'pageClass' => ViewRecord::class,
            ])
            ->mountTableAction($action, $waitingListAccount)
            ->assertTableActionDataSet([
                'trainable' => null,
            ]);
    }

    /**
     * @return array<string, array{string}>
     */
    public static function trainingPlaceActionProvider(): array
    {
        return [
            'offer training place' => ['offerTrainingPlace'],
            'manual setup training place' => ['manualSetupTrainingPlace'],
        ];
    }

    private function addStudentToWaitingList(WaitingList $waitingList): WaitingListAccount
    {
        $student = Account::factory()->create();
        Member::factory()->create(['cid' => $student->id]);

        return $waitingList->addToWaitingList($student, $this->privacc);
    }
}
