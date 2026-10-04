<?php

namespace Tests\Feature\Admin\Account\RelationManagers;

use App\Filament\Admin\Resources\Accounts\Pages\ViewAccount;
use App\Filament\Admin\Resources\Accounts\RelationManagers\StatesRelationManager;
use App\Models\Mship\State;
use Livewire\Livewire;
use Tests\Feature\Admin\BaseAdminTestCase;

class StatesRelationManagerTest extends BaseAdminTestCase
{
    public function test_it_renders_with_active_state()
    {
        $this->actingAsSuperUser();

        $this->privacc->addState(State::findByCode('VISITING'));

        Livewire::test(StatesRelationManager::class, [
            'ownerRecord' => $this->privacc->fresh(),
            'pageClass' => ViewAccount::class,
        ])->assertSuccessful();
    }

    public function test_it_renders_with_ended_state()
    {
        $this->actingAsSuperUser();

        $state = State::findByCode('VISITING');
        $this->privacc->addState($state);
        $this->privacc->removeState($state->fresh());

        Livewire::test(StatesRelationManager::class, [
            'ownerRecord' => $this->privacc->fresh(),
            'pageClass' => ViewAccount::class,
        ])->assertSuccessful();
    }
}
