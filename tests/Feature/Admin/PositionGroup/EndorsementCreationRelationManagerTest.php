<?php

namespace Tests\Feature\Admin\PositionGroup;

use App\Filament\Admin\Resources\PositionGroupResource\RelationManagers\MembershipEndorsementRelationManager;
use App\Filament\Admin\Resources\WaitingListResource\Pages\ViewWaitingList;
use App\Models\Atc\PositionGroup;
use App\Models\Mship\Account;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Livewire;
use Tests\Feature\Admin\BaseAdminTestCase;

class EndorsementCreationRelationManagerTest extends BaseAdminTestCase
{
    use DatabaseTransactions;

    public function test_cannot_see_new_endorsement_action()
    {
        $positionGroup = PositionGroup::factory()->create();

        $this->actingAsAdminUser();

        Livewire::test(MembershipEndorsementRelationManager::class, ['ownerRecord' => $positionGroup, 'pageClass' => ViewWaitingList::class])
            ->assertTableActionHidden('create');
    }

    public function test_can_create_endorsement_within_position_group_with_permission()
    {
        $positionGroup = PositionGroup::factory()->create();
        $accountToEndorse = Account::factory()->create();

        $this->actingAsAdminUser(['endorsement.create.*']);

        Livewire::test(MembershipEndorsementRelationManager::class, ['ownerRecord' => $positionGroup, 'pageClass' => ViewWaitingList::class])
            ->assertTableActionExists('create')
            ->callTableAction('create', data: [
                'account_id' => $accountToEndorse->id,
            ]);

        $this->assertDatabaseHas('mship_account_endorsement', [
            'account_id' => $accountToEndorse->id,
            'endorsable_id' => $positionGroup->id,
            'endorsable_type' => PositionGroup::class,
        ]);
    }

    public function test_does_not_create_endorsement_within_position_group_without_permission()
    {
        $positionGroup = PositionGroup::factory()->create();

        $this->actingAsAdminUser(['endorsement.create.*']);

        Livewire::test(MembershipEndorsementRelationManager::class, ['ownerRecord' => $positionGroup, 'pageClass' => ViewWaitingList::class])
            ->assertTableActionVisible('create')
            ->callTableAction('create', data: [
                'account_id' => 9999999,
            ]);

        $this->assertDatabaseMissing('mship_account_endorsement', [
            'account_id' => 9999999,
            'endorsable_id' => $positionGroup->id,
            'endorsable_type' => PositionGroup::class,
        ]);
    }
}
