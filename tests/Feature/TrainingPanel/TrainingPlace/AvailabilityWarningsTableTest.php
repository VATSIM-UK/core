<?php

declare(strict_types=1);

namespace Tests\Feature\TrainingPanel\TrainingPlace;

use App\Livewire\Training\AvailabilityWarningsTable;
use App\Models\Cts\Member;
use App\Models\Mship\Account;
use App\Models\Training\TrainingPlace\AvailabilityCheck;
use App\Models\Training\TrainingPlace\AvailabilityWarning;
use App\Models\Training\TrainingPlace\TrainingPlace;
use App\Models\Training\TrainingPosition\TrainingPosition;
use App\Models\Training\WaitingList;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\Feature\TrainingPanel\BaseTrainingPanelTestCase;

class AvailabilityWarningsTableTest extends BaseTrainingPanelTestCase
{
    use DatabaseTransactions;

    private function createTrainingPlaceWithWarning(): array
    {
        $account = Account::factory()->create();
        Member::factory()->create(['cid' => $account->id]);

        $waitingList = WaitingList::factory()->create();
        $waitingListAccount = $waitingList->addToWaitingList($account, $account);
        $trainingPosition = TrainingPosition::factory()->create(['cts_positions' => []]);

        $trainingPlace = TrainingPlace::factory()->create([
            'waiting_list_account_id' => $waitingListAccount->id,
            'training_position_id' => $trainingPosition->id,
        ]);

        $check = AvailabilityCheck::factory()->failed()->create(['training_place_id' => $trainingPlace->id]);
        $warning = AvailabilityWarning::factory()->pending()->create([
            'training_place_id' => $trainingPlace->id,
            'availability_check_id' => $check->id,
            'expires_at' => now()->addDays(1),
        ]);

        return ['trainingPlace' => $trainingPlace, 'warning' => $warning, 'check' => $check];
    }

    #[Test]
    public function it_can_delete_availability_warning_when_user_has_permission(): void
    {
        $this->panelUser->givePermissionTo('training-places.availability-warnings.delete');

        ['trainingPlace' => $trainingPlace, 'warning' => $warning, 'check' => $check] = $this->createTrainingPlaceWithWarning();

        Livewire::actingAs($this->panelUser)
            ->test(AvailabilityWarningsTable::class, ['trainingPlace' => $trainingPlace])
            ->assertStatus(200)
            ->callTableAction('delete', $warning);

        $this->assertDatabaseMissing('availability_warnings', ['id' => $warning->id]);
        $this->assertEquals('passed', $check->fresh()->status->value);
    }

    #[Test]
    public function it_does_not_show_delete_action_without_permission(): void
    {
        ['trainingPlace' => $trainingPlace, 'warning' => $warning] = $this->createTrainingPlaceWithWarning();

        Livewire::actingAs($this->panelUser)
            ->test(AvailabilityWarningsTable::class, ['trainingPlace' => $trainingPlace])
            ->assertStatus(200)
            ->assertTableActionHidden('delete', $warning);
    }
}
