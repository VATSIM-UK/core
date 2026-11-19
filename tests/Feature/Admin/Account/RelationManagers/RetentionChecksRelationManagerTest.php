<?php

namespace Tests\Feature\Admin\Account\RelationManagers;

use App\Filament\Admin\Resources\Accounts\RelationManagers\RetentionChecksRelationManager;
use App\Models\Mship\Account;
use Filament\Resources\Pages\ViewRecord;
use Livewire\Livewire;
use Tests\Feature\Admin\BaseAdminTestCase;

class RetentionChecksRelationManagerTest extends BaseAdminTestCase
{
    public function test_it_does_not_expose_retention_tokens()
    {
        $this->actingAsSuperUser();

        Livewire::test(RetentionChecksRelationManager::class, [
            'ownerRecord' => Account::factory()->create(),
            'pageClass' => ViewRecord::class,
        ])
            ->assertSuccessful()
            ->assertTableColumnDoesNotExist('token');
    }
}
