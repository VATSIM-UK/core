<?php

namespace Tests\Feature\Admin\Account\RelationManagers;

use App\Filament\Admin\Resources\Accounts\Pages\ViewAccount;
use App\Filament\Admin\Resources\Accounts\RelationManagers\EndorsementsRelationManager;
use App\Models\Atc\PositionGroup;
use App\Models\Mship\Account;
use App\Models\Mship\Account\Endorsement;
use Illuminate\Support\Facades\Gate;
use Livewire\Livewire;
use Tests\Feature\Admin\BaseAdminTestCase;

class EndorsementsRelationManagerTest extends BaseAdminTestCase
{
    public function test_it_renders()
    {
        $this->actingAsSuperUser();

        Livewire::test(EndorsementsRelationManager::class, [
            'ownerRecord' => Account::factory()->create(),
            'pageClass' => ViewAccount::class,
        ])->assertSuccessful();
    }

    public function test_account_viewer_cannot_revoke_a_permanent_endorsement()
    {
        $this->actingAsAdminUser(['account.view-insensitive.*']);

        $endorsement = $this->createEndorsement();

        Livewire::test(EndorsementsRelationManager::class, [
            'ownerRecord' => $endorsement->account,
            'pageClass' => ViewAccount::class,
        ])->assertTableActionHidden('revoke', $endorsement);

        $this->assertFalse(Gate::forUser($this->adminUser)->allows('delete', $endorsement));
        $this->assertNull($endorsement->fresh()->expires_at);
    }

    public function test_account_viewer_cannot_extend_a_temporary_endorsement()
    {
        $this->actingAsAdminUser(['account.view-insensitive.*']);

        $endorsement = $this->createEndorsement(now()->addDays(7)->toDateString());

        Livewire::test(EndorsementsRelationManager::class, [
            'ownerRecord' => $endorsement->account,
            'pageClass' => ViewAccount::class,
        ])->assertTableActionHidden('edit', $endorsement);

        $this->assertFalse(Gate::forUser($this->adminUser)->allows('update', $endorsement));
    }

    public function test_it_revokes_an_endorsement_with_the_create_permission()
    {
        $this->actingAsAdminUser(['endorsement.create.*']);

        $endorsement = $this->createEndorsement(now()->addDays(7)->toDateString());

        Livewire::test(EndorsementsRelationManager::class, [
            'ownerRecord' => $endorsement->account,
            'pageClass' => ViewAccount::class,
        ])
            ->assertTableActionVisible('revoke', $endorsement)
            ->callTableAction('revoke', $endorsement);

        $this->assertTrue($endorsement->fresh()->expires_at->isPast());
    }

    public function test_it_does_not_revoke_without_the_create_permission()
    {
        $this->actingAsAdminUser(['endorsement.temporary.edit.*']);

        $endorsement = $this->createEndorsement(now()->addDays(7)->toDateString());

        Livewire::test(EndorsementsRelationManager::class, [
            'ownerRecord' => $endorsement->account,
            'pageClass' => ViewAccount::class,
        ])->assertTableActionHidden('revoke', $endorsement);

        $this->assertTrue($endorsement->fresh()->expires_at->isFuture());
    }

    public function test_it_extends_a_temporary_endorsement_with_the_temporary_edit_permission()
    {
        $this->actingAsAdminUser(['endorsement.temporary.edit.*']);

        $endorsement = $this->createEndorsement(now()->addDays(7)->toDateString());
        $newExpiry = now()->addDays(30)->toDateString();

        Livewire::test(EndorsementsRelationManager::class, [
            'ownerRecord' => $endorsement->account,
            'pageClass' => ViewAccount::class,
        ])
            ->assertTableActionVisible('edit', $endorsement)
            ->callTableAction('edit', $endorsement, ['expires_at' => $newExpiry]);

        $this->assertSame($newExpiry, $endorsement->fresh()->expires_at->toDateString());
    }

    public function test_it_does_not_extend_without_the_temporary_edit_permission()
    {
        $this->actingAsAdminUser(['endorsement.create.*']);

        $originalExpiry = now()->addDays(7)->toDateString();
        $endorsement = $this->createEndorsement($originalExpiry);

        Livewire::test(EndorsementsRelationManager::class, [
            'ownerRecord' => $endorsement->account,
            'pageClass' => ViewAccount::class,
        ])->assertTableActionHidden('edit', $endorsement);

        $this->assertSame($originalExpiry, $endorsement->fresh()->expires_at->toDateString());
    }

    private function createEndorsement(?string $expiresAt = null): Endorsement
    {
        return Endorsement::factory()->createQuietly([
            'endorsable_type' => PositionGroup::class,
            'endorsable_id' => PositionGroup::factory(),
            'expires_at' => $expiresAt,
            'created_by' => $this->adminUser->id,
        ]);
    }
}
