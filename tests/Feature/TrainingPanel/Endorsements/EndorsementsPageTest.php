<?php

namespace Tests\Feature\TrainingPanel\Endorsements;

use App\Filament\Training\Pages\Endorsements;
use App\Filament\Training\Pages\Endorsements\Tables\ResourceTable;
use App\Filament\Training\Resources\EndorsementRequests\EndorsementRequestResource;
use App\Filament\Training\Resources\EndorsementRequests\Pages\CreateEndorsementRequest;
use App\Filament\Training\Resources\EndorsementRequests\Pages\ListEndorsementRequests;
use App\Filament\Training\Resources\PositionGroups\Pages\ViewPositionGroup;
use App\Filament\Training\Resources\PositionGroups\PositionGroupResource;
use App\Filament\Training\Resources\SoloEndorsements\SoloEndorsementResource;
use App\Models\Atc\Position;
use App\Models\Atc\PositionGroup;
use App\Models\Cts\Member;
use App\Models\Mship\Account;
use App\Models\Mship\Account\Endorsement;
use App\Models\Mship\Account\EndorsementRequest;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Livewire\Livewire;
use Tests\Feature\TrainingPanel\BaseTrainingPanelTestCase;

use function Livewire\invade;

class EndorsementsPageTest extends BaseTrainingPanelTestCase
{
    use DatabaseTransactions;

    protected function createUser(array $permissions = []): Account
    {
        $account = Account::factory()->createQuietly();

        Member::factory()->forAccount($account)->create();

        $account->givePermissionTo('training.access');

        if (filled($permissions)) {
            $account->givePermissionTo($permissions);
        }

        return $account;
    }

    public function test_page_is_forbidden_without_any_endorsement_permission(): void
    {
        $user = $this->createUser();

        Livewire::actingAs($user);
        Livewire::test(Endorsements::class)
            ->assertForbidden();
    }

    public function test_old_endorsement_requests_list_url_is_inaccessible(): void
    {
        $user = $this->createUser(['endorsement-request.access', 'endorsement-request.create.*']);

        Livewire::actingAs($user);
        Livewire::test(ListEndorsementRequests::class)
            ->assertForbidden();
    }

    public function test_page_renders_all_tabs_with_all_endorsement_permissions(): void
    {
        $user = $this->createUser(['endorsement-request.access', 'endorsement.view.*', 'position-group.view.*']);

        Livewire::actingAs($user);
        Livewire::test(Endorsements::class)
            ->assertSuccessful()
            ->assertSee('Endorsement Requests')
            ->assertSee('Solo Endorsements')
            ->assertSee('Tier Endorsements');
    }

    public function test_only_requests_tab_visible_with_request_access_permission(): void
    {
        $user = $this->createUser(['endorsement-request.access']);

        Livewire::actingAs($user);
        Livewire::test(Endorsements::class)
            ->assertSuccessful()
            ->assertSee('Endorsement Requests')
            ->assertDontSee('Solo Endorsements')
            ->assertDontSee('Tier Endorsements');
    }

    public function test_only_solo_tab_visible_with_endorsement_view_permission(): void
    {
        $user = $this->createUser(['endorsement.view.*']);

        Livewire::actingAs($user);
        Livewire::test(Endorsements::class)
            ->assertSuccessful()
            ->assertSee('Solo Endorsements')
            ->assertDontSee('Endorsement Requests')
            ->assertDontSee('Tier Endorsements');
    }

    public function test_only_tiers_tab_visible_with_position_group_view_permission(): void
    {
        $user = $this->createUser(['position-group.view.*']);

        Livewire::actingAs($user);
        Livewire::test(Endorsements::class)
            ->assertSuccessful()
            ->assertSee('Tier Endorsements')
            ->assertDontSee('Endorsement Requests')
            ->assertDontSee('Solo Endorsements');
    }

    public function test_requests_and_solo_tabs_visible_without_tiers_permission(): void
    {
        $user = $this->createUser(['endorsement-request.access', 'endorsement.view.*']);

        Livewire::actingAs($user);
        Livewire::test(Endorsements::class)
            ->assertSuccessful()
            ->assertSee('Endorsement Requests')
            ->assertSee('Solo Endorsements')
            ->assertDontSee('Tier Endorsements');
    }

    public function test_requests_and_tiers_tabs_visible_without_solo_permission(): void
    {
        $user = $this->createUser(['endorsement-request.access', 'position-group.view.*']);

        Livewire::actingAs($user);
        Livewire::test(Endorsements::class)
            ->assertSuccessful()
            ->assertSee('Endorsement Requests')
            ->assertSee('Tier Endorsements')
            ->assertDontSee('Solo Endorsements');
    }

    public function test_active_tab_defaults_to_requests(): void
    {
        $user = $this->createUser(['endorsement-request.access', 'endorsement.view.*', 'position-group.view.*']);

        Livewire::actingAs($user);
        Livewire::test(Endorsements::class)
            ->assertSet('activeTab', 'requests');
    }

    public function test_active_tab_can_be_explicitly_set_to_solo(): void
    {
        $user = $this->createUser(['endorsement.view.*']);

        Livewire::actingAs($user);
        Livewire::test(Endorsements::class, ['activeTab' => 'solo'])
            ->assertSet('activeTab', 'solo')
            ->assertSee('Solo Endorsements');
    }

    public function test_active_tab_can_be_explicitly_set_to_tiers(): void
    {
        $user = $this->createUser(['position-group.view.*']);

        Livewire::actingAs($user);
        Livewire::test(Endorsements::class, ['activeTab' => 'tiers'])
            ->assertSet('activeTab', 'tiers')
            ->assertSee('Tier Endorsements');
    }

    public function test_active_tab_falls_back_to_first_visible_tab_when_requested_tab_not_visible(): void
    {
        $user = $this->createUser(['endorsement.view.*']);

        Livewire::actingAs($user);
        Livewire::test(Endorsements::class, ['activeTab' => 'requests'])
            ->assertSet('activeTab', 'solo');
    }

    public function test_active_tab_falls_back_to_tiers_when_active_tab_hidden(): void
    {
        $user = $this->createUser(['position-group.view.*']);

        Livewire::actingAs($user);
        Livewire::test(Endorsements::class, ['activeTab' => 'solo'])
            ->assertSet('activeTab', 'tiers');
    }

    public function test_url_for_requests_resource_points_to_requests_tab(): void
    {
        $this->assertSame(
            Endorsements::getUrl(['activeTab' => 'requests']),
            Endorsements::urlFor(EndorsementRequestResource::class),
        );
    }

    public function test_url_for_solo_resource_points_to_solo_tab(): void
    {
        $this->assertSame(
            Endorsements::getUrl(['activeTab' => 'solo']),
            Endorsements::urlFor(SoloEndorsementResource::class),
        );
    }

    public function test_url_for_tiers_resource_points_to_tiers_tab(): void
    {
        $this->assertSame(
            Endorsements::getUrl(['activeTab' => 'tiers']),
            Endorsements::urlFor(PositionGroupResource::class),
        );
    }

    public function test_url_for_unknown_resource_falls_back_to_requests_tab(): void
    {
        $this->assertSame(
            Endorsements::getUrl(['activeTab' => 'requests']),
            Endorsements::urlFor(\stdClass::class),
        );
    }

    public function test_requests_tab_renders_endorsement_requests(): void
    {
        $user = $this->createUser(['endorsement-request.access']);
        $endorsementRequest = EndorsementRequest::factory()->create();

        Livewire::actingAs($user);
        Livewire::test(Endorsements::class)
            ->assertSuccessful()
            ->assertSee($endorsementRequest->account->name);
    }

    public function test_solo_tab_renders_solo_endorsements(): void
    {
        $user = $this->createUser(['endorsement.view.*']);
        $soloEndorsement = Endorsement::factory()->create([
            'endorsable_type' => Position::class,
            'endorsable_id' => Position::factory(),
            'expires_at' => now()->addDays(1),
        ]);

        Livewire::actingAs($user);
        Livewire::test(Endorsements::class, ['activeTab' => 'solo'])
            ->assertSuccessful()
            ->assertSee($soloEndorsement->account->name);
    }

    public function test_requests_table_shows_create_action_when_user_can_create(): void
    {
        $this->panelUser->givePermissionTo('endorsement-request.access');
        $this->panelUser->givePermissionTo('endorsement-request.create.*');

        Livewire::actingAs($this->panelUser);
        Livewire::test(ResourceTable::class, ['resource' => EndorsementRequestResource::class])
            ->assertSuccessful()
            ->assertTableActionVisible('create');
    }

    public function test_requests_table_hides_create_action_without_create_permission(): void
    {
        $user = $this->createUser(['endorsement-request.access']);

        Livewire::actingAs($user);
        Livewire::test(ResourceTable::class, ['resource' => EndorsementRequestResource::class])
            ->assertSuccessful()
            ->assertDontSee('New endorsement request');
    }

    public function test_create_action_url_points_to_create_page(): void
    {
        $this->panelUser->givePermissionTo('endorsement-request.access');
        $this->panelUser->givePermissionTo('endorsement-request.create.*');

        Livewire::actingAs($this->panelUser);
        Livewire::test(ResourceTable::class, ['resource' => EndorsementRequestResource::class])
            ->assertTableActionHasUrl('create', EndorsementRequestResource::getUrl('create'));
    }

    public function test_approve_action_visible_on_pending_request_with_permission(): void
    {
        $this->panelUser->givePermissionTo('endorsement-request.access');
        $this->panelUser->givePermissionTo('endorsement-request.approve.*');

        $endorsementRequest = EndorsementRequest::factory()->create([
            'endorsable_type' => PositionGroup::class,
            'endorsable_id' => PositionGroup::factory()->create()->id,
        ]);

        Livewire::actingAs($this->panelUser);
        Livewire::test(ResourceTable::class, ['resource' => EndorsementRequestResource::class])
            ->assertTableActionVisible('approve', $endorsementRequest->id);
    }

    public function test_reject_action_visible_on_pending_request_with_permission(): void
    {
        $this->panelUser->givePermissionTo('endorsement-request.access');
        $this->panelUser->givePermissionTo('endorsement-request.approve.*');

        $endorsementRequest = EndorsementRequest::factory()->create([
            'endorsable_type' => PositionGroup::class,
            'endorsable_id' => PositionGroup::factory()->create()->id,
        ]);

        Livewire::actingAs($this->panelUser);
        Livewire::test(ResourceTable::class, ['resource' => EndorsementRequestResource::class])
            ->assertTableActionVisible('reject', $endorsementRequest->id);
    }

    public function test_approve_action_hidden_on_pending_request_without_permission(): void
    {
        $user = $this->createUser(['endorsement-request.access']);

        $endorsementRequest = EndorsementRequest::factory()->create([
            'endorsable_type' => PositionGroup::class,
            'endorsable_id' => PositionGroup::factory()->create()->id,
        ]);

        Livewire::actingAs($user);
        Livewire::test(ResourceTable::class, ['resource' => EndorsementRequestResource::class])
            ->assertTableActionHidden('approve', $endorsementRequest->id);
    }

    public function test_reject_action_hidden_on_pending_request_without_permission(): void
    {
        $user = $this->createUser(['endorsement-request.access']);

        $endorsementRequest = EndorsementRequest::factory()->create([
            'endorsable_type' => PositionGroup::class,
            'endorsable_id' => PositionGroup::factory()->create()->id,
        ]);

        Livewire::actingAs($user);
        Livewire::test(ResourceTable::class, ['resource' => EndorsementRequestResource::class])
            ->assertTableActionHidden('reject', $endorsementRequest->id);
    }

    public function test_approve_action_hidden_on_approved_request_even_with_permission(): void
    {
        $this->panelUser->givePermissionTo('endorsement-request.access');
        $this->panelUser->givePermissionTo('endorsement-request.approve.*');

        $endorsementRequest = EndorsementRequest::factory()->create([
            'endorsable_type' => PositionGroup::class,
            'endorsable_id' => PositionGroup::factory()->create()->id,
            'actioned_at' => now(),
            'actioned_type' => EndorsementRequest::STATUS_APPROVED,
        ]);

        Livewire::actingAs($this->panelUser);
        Livewire::test(ResourceTable::class, ['resource' => EndorsementRequestResource::class])
            ->assertTableActionHidden('approve', $endorsementRequest->id);
    }

    public function test_reject_action_hidden_on_approved_request_even_with_permission(): void
    {
        $this->panelUser->givePermissionTo('endorsement-request.access');
        $this->panelUser->givePermissionTo('endorsement-request.approve.*');

        $endorsementRequest = EndorsementRequest::factory()->create([
            'endorsable_type' => PositionGroup::class,
            'endorsable_id' => PositionGroup::factory()->create()->id,
            'actioned_at' => now(),
            'actioned_type' => EndorsementRequest::STATUS_APPROVED,
        ]);

        Livewire::actingAs($this->panelUser);
        Livewire::test(ResourceTable::class, ['resource' => EndorsementRequestResource::class])
            ->assertTableActionHidden('reject', $endorsementRequest->id);
    }

    public function test_can_reject_request_from_requests_tab(): void
    {
        $this->panelUser->givePermissionTo('endorsement-request.access');
        $this->panelUser->givePermissionTo('endorsement-request.approve.*');

        $endorsementRequest = EndorsementRequest::factory()->create([
            'endorsable_type' => PositionGroup::class,
            'endorsable_id' => PositionGroup::factory()->create()->id,
        ]);

        Livewire::actingAs($this->panelUser);
        Livewire::test(ResourceTable::class, ['resource' => EndorsementRequestResource::class])
            ->assertCanSeeTableRecords([$endorsementRequest])
            ->mountTableAction('reject', record: $endorsementRequest->id)
            ->callMountedTableAction();

        $this->assertDatabaseHas('endorsement_requests', [
            'id' => $endorsementRequest->id,
            'actioned_type' => EndorsementRequest::STATUS_REJECTED,
        ]);
    }

    public function test_tiers_table_displays_position_groups(): void
    {
        $user = $this->createUser(['position-group.view.*']);
        $positionGroup = PositionGroup::factory()->create();

        Livewire::actingAs($user);
        Livewire::test(ResourceTable::class, ['resource' => PositionGroupResource::class])
            ->assertCanSeeTableRecords([$positionGroup]);
    }

    public function test_view_action_visible_on_position_group_row(): void
    {
        $user = $this->createUser(['position-group.view.*']);
        $positionGroup = PositionGroup::factory()->create();

        Livewire::actingAs($user);
        Livewire::test(ResourceTable::class, ['resource' => PositionGroupResource::class])
            ->assertTableActionVisible('view', $positionGroup->id);
    }

    public function test_view_action_url_points_to_view_page(): void
    {
        $user = $this->createUser(['position-group.view.*']);
        $positionGroup = PositionGroup::factory()->create();

        Livewire::actingAs($user);
        Livewire::test(ResourceTable::class, ['resource' => PositionGroupResource::class])
            ->assertTableActionHasUrl('view', PositionGroupResource::getUrl('view', ['record' => $positionGroup]), $positionGroup->getRouteKey());
    }

    public function test_view_position_group_has_back_to_endorsements_action(): void
    {
        $user = $this->createUser(['position-group.view.*']);
        $positionGroup = PositionGroup::factory()->create();

        Livewire::actingAs($user);
        Livewire::test(ViewPositionGroup::class, ['record' => $positionGroup->getRouteKey()])
            ->assertActionHasUrl('back', Endorsements::urlFor(PositionGroupResource::class));
    }

    public function test_create_page_redirects_to_requests_tab_after_create(): void
    {
        $accountRequestingFor = Account::factory()->create();
        $positionGroup = PositionGroup::factory()->create();

        $this->panelUser->givePermissionTo('endorsement-request.access');
        $this->panelUser->givePermissionTo('endorsement-request.create.*');

        $component = Livewire::actingAs($this->panelUser)
            ->test(CreateEndorsementRequest::class)
            ->set('data.endorsable_type', 'App\Models\Atc\PositionGroup')
            ->set('data.account_id', $accountRequestingFor->id)
            ->set('data.endorsable_id', $positionGroup->id)
            ->call('create');

        $this->assertSame(
            Endorsements::urlFor(EndorsementRequestResource::class),
            invade($component->instance())->getRedirectUrl(),
        );
    }

    public function test_create_page_cancel_action_urls_to_requests_tab(): void
    {
        $this->panelUser->givePermissionTo('endorsement-request.access');
        $this->panelUser->givePermissionTo('endorsement-request.create.*');

        $component = Livewire::actingAs($this->panelUser)
            ->test(CreateEndorsementRequest::class);

        $this->assertSame(
            Endorsements::urlFor(EndorsementRequestResource::class),
            invade($component->instance())->getCancelFormAction()->getUrl(),
        );
    }
}
