<?php

namespace Tests\Feature\Admin\Account\RelationManagers;

use App\Filament\Resources\AccountResource\RelationManagers\BansRelationManager;
use App\Models\Mship\Account;
use App\Models\Mship\Account\Ban;
use App\Models\Mship\Ban\Reason;
use App\Notifications\Mship\BanCreated;
use App\Policies\Mship\Account\BanPolicy;
use Livewire;
use Mockery\MockInterface;
use Notification;
use Tests\Feature\Admin\BaseAdminTestCase;

class BansRelationManagerTest extends BaseAdminTestCase
{
    public function test_it_renders()
    {
        $this->actingAsSuperUser();

        $account = Account::factory()->has(Ban::factory())->create();
        Livewire::test(BansRelationManager::class, ['ownerRecord' => $account])
            ->assertSuccessful()
            ->assertCanSeeTableRecords($account->bans);
    }

    public function test_it_hides_create_button()
    {
        $this->actingAsSuperUser();
        $account = Account::factory()->create();
        $this->partialMock(BanPolicy::class, function (MockInterface $mock) {
            $mock->shouldReceive('create')->andReturnFalse();
        });

        Livewire::test(BansRelationManager::class, ['ownerRecord' => $account])
            ->assertTableActionHidden('create');

        $this->partialMock(BanPolicy::class, function (MockInterface $mock) {
            $mock->shouldReceive('create')->andReturnTrue();
        });
        Livewire::test(BansRelationManager::class, ['ownerRecord' => $account])
            ->assertTableActionVisible('create');
    }

    public function test_it_can_create_ban()
    {
        Notification::fake();

        $this->actingAsSuperUser();
        $account = Account::factory()->create();
        $reason = Reason::factory()->create()->id;

        Livewire::test(BansRelationManager::class, ['ownerRecord' => $account])
            ->callTableAction('create', null, ['reason' => Reason::factory()->create()->id, 'extra_info' => 'the extra info', 'note' => 'the note']);

        $this->assertDatabaseHas('mship_account_bans', ['account_id' => $account->id, 'banner_id' => $this->privacc->id, 'reason_id' => $reason, 'reason_extra' => 'the extra info']);
        Notification::assertSentTo([$account], BanCreated::class);
    }
}
