<?php

namespace Tests\Feature\Admin\Account\RelationManagers;

use App\Filament\Resources\AccountResource\Pages\ViewAccount;
use App\Filament\Resources\AccountResource\RelationManagers\FeedbackRelationManager;
use App\Models\Mship\Account;
use App\Models\Mship\Feedback\Feedback;
use Livewire\Livewire;
use Tests\Feature\Admin\BaseAdminTestCase;

class FeedbackRelationManagerTest extends BaseAdminTestCase
{
    public function test_it_renders()
    {
        $this->actingAsAdminUser();

        $account = Account::factory()->create();
        factory(Feedback::class)->create(['account_id' => $account->id]);

        Livewire::test(FeedbackRelationManager::class, ['ownerRecord' => $account, 'pageClass' => ViewRecord::class])
            ->assertSuccessful()
            ->assertCanSeeTableRecords($account->feedback);
    }

    public function test_only_available_with_permission()
    {
        $account = Account::factory()->create();
        $this->actingAsAdminUser();
        $this->assertFalse(FeedbackRelationManager::canViewForRecord($account, ViewAccount::class));

        $this->actingAsAdminUser(['feedback.access']);
        $this->assertTrue(FeedbackRelationManager::canViewForRecord($account, ViewAccount::class));
    }
}
