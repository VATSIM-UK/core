<?php

namespace Tests\Feature\Admin\SoloEndorsement\Pages;

use App\Filament\Resources\SoloEndorsementResource\Pages\ListSoloEndorsements;
use App\Models\Atc\Position;
use App\Models\Mship\Account\Endorsement;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Livewire\Livewire;
use Tests\Feature\Admin\BaseAdminTestCase;

class ListSoloEndorsementPageTest extends BaseAdminTestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        Livewire::actingAs($this->adminUser);
    }

    public function test_can_access_list_page_with_permission()
    {
        $this->adminUser->givePermissionTo('endorsement.view.*');

        Livewire::test(ListSoloEndorsements::class)
            ->assertSee('Solo Endorsements');
    }

    public function test_cannot_access_list_page_without_permission()
    {
        Livewire::test(ListSoloEndorsements::class)
            ->assertForbidden();
    }

    public function test_only_displays_solo_endorsements_with_expiry()
    {
        $this->adminUser->givePermissionTo('endorsement.view.*');

        $soloEndorsement = Endorsement::factory()->create([
            'endorsable_type' => Position::class,
            'endorsable_id' => Position::factory(),
            'expires_at' => now()->addDays(1),
        ]);

        $soloEndorsementWithoutExpiry = Endorsement::factory()->create([
            'endorsable_type' => Position::class,
            'endorsable_id' => Position::factory(),
            'expires_at' => null,
        ]);

        Livewire::test(ListSoloEndorsements::class)
            ->assertSee($soloEndorsement->account->name)
            ->assertDontSee($soloEndorsementWithoutExpiry->account->name);
    }

    public function test_only_displays_active_solo_endorsements_by_default()
    {
        $this->adminUser->givePermissionTo('endorsement.view.*');

        $soloEndorsement = Endorsement::factory()->create([
            'endorsable_type' => Position::class,
            'endorsable_id' => Position::factory(),
            'expires_at' => now()->addDays(1),
        ]);

        $expiredSoloEndorsement = Endorsement::factory()->create([
            'endorsable_type' => Position::class,
            'endorsable_id' => Position::factory(),
            'expires_at' => now()->subDays(1),
        ]);

        Livewire::test(ListSoloEndorsements::class)
            ->assertSee($soloEndorsement->account->name)
            ->assertDontSee($expiredSoloEndorsement->account->name);
    }

    public function test_filter_can_be_changed_to_expired_endorsements()
    {
        $this->adminUser->givePermissionTo('endorsement.view.*');

        $soloEndorsement = Endorsement::factory()->create([
            'endorsable_type' => Position::class,
            'endorsable_id' => Position::factory(),
            'expires_at' => now()->addDays(1),
        ]);

        $expiredSoloEndorsement = Endorsement::factory()->create([
            'endorsable_type' => Position::class,
            'endorsable_id' => Position::factory(),
            'expires_at' => now()->subDays(1),
        ]);

        Livewire::test(ListSoloEndorsements::class)
            ->filterTable('expires_at', false)
            ->assertSee($expiredSoloEndorsement->account->name)
            ->assertDontSee($soloEndorsement->account->name);
    }

    public function test_filter_can_be_changed_to_all_endorsements()
    {
        $this->adminUser->givePermissionTo('endorsement.view.*');

        $soloEndorsement = Endorsement::factory()->create([
            'endorsable_type' => Position::class,
            'endorsable_id' => Position::factory(),
            'expires_at' => now()->addDays(1),
        ]);

        $expiredSoloEndorsement = Endorsement::factory()->create([
            'endorsable_type' => Position::class,
            'endorsable_id' => Position::factory(),
            'expires_at' => now()->subDays(1),
        ]);

        Livewire::test(ListSoloEndorsements::class)
            ->filterTable('expires_at', null)
            ->assertSee($soloEndorsement->account->name)
            ->assertSee($expiredSoloEndorsement->account->name);
    }
}
