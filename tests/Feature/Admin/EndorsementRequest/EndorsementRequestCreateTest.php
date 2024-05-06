<?php

namespace Tests\Feature\Admin\EndorsementRequest;

use App\Filament\Resources\EndorsementRequestResource\Pages\CreateEndorsementRequest;
use App\Filament\Resources\EndorsementRequestResource\Pages\ListEndorsementRequests;
use App\Models\Atc\Position;
use App\Models\Atc\PositionGroup;
use App\Models\Mship\Account;
use App\Notifications\Mship\Endorsement\EndorsementRequestCreated;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;
use Tests\Feature\Admin\BaseAdminTestCase;

class EndorsementRequestCreateTest extends BaseAdminTestCase
{
    public function test_create_not_visible_when_no_create_permission()
    {
        $this->actingAsAdminUser('endorsement-request.access');

        Livewire::test(ListEndorsementRequests::class)
            ->assertDontSee('New endorsement request');
    }

    public function test_create_visible_when_create_permission()
    {
        $this->actingAsAdminUser(['endorsement-request.access', 'endorsement-request.create.*']);

        Livewire::test(ListEndorsementRequests::class)
            ->assertSee('New endorsement request');
    }

    public function test_cannot_create_endorsement_request_for_position_group_without_permission()
    {
        $this->actingAsAdminUser('endorsement-request.access');

        Livewire::test(CreateEndorsementRequest::class)
            ->assertForbidden();
    }

    public function test_can_create_endorsement_request_for_position_group()
    {
        $accountRequestingFor = Account::factory()->create();
        $positionGroup = PositionGroup::factory()->create();

        $this->actingAsAdminUser(['endorsement-request.access', 'endorsement-request.create.*']);
        Livewire::test(CreateEndorsementRequest::class)
            // check if the position group is visible
            ->set('data.endorsable_type', 'App\Models\Atc\PositionGroup')
            ->assertSee($positionGroup->name)
            ->fillForm([
                'account_id' => $accountRequestingFor->id,
                'endorsable_id' => $positionGroup->id,
                'notes' => 'This is a test note',
            ])
            ->call('create');

        $this->assertDatabaseHas('endorsement_requests', [
            'account_id' => $accountRequestingFor->id,
            'endorsable_id' => $positionGroup->id,
            'endorsable_type' => 'App\Models\Atc\PositionGroup',
            'notes' => 'This is a test note',
        ]);
    }

    public function test_can_create_endorsement_request_for_temporarily_endorsable_position()
    {
        $accountRequestingFor = Account::factory()->create();
        $position = Position::factory()->temporarilyEndorsable()->create();
        $nonTemporarilyEndorsablePosition = Position::factory()->create();

        $this->adminUser->givePermissionTo('endorsement-request.access');
        $this->adminUser->givePermissionTo('endorsement-request.create.*');

        Livewire::actingAs($this->adminUser);
        Livewire::test(CreateEndorsementRequest::class)
            ->set('data.endorsable_type', 'App\Models\Atc\Position')
            ->assertSee($position->callsign)
            ->assertDontSee($nonTemporarilyEndorsablePosition->name)
            ->fillForm([
                'account_id' => $accountRequestingFor->id,
                'endorsable_id' => $position->id,
                'notes' => 'This is a test note',
            ])
            ->call('create');

        $this->assertDatabaseHas('endorsement_requests', [
            'account_id' => $accountRequestingFor->id,
            'endorsable_id' => $position->id,
            'endorsable_type' => 'App\Models\Atc\Position',
            'notes' => 'This is a test note',
        ]);
    }

    public function test_sends_notifications_to_all_with_permission_when_request_created()
    {
        Notification::fake();

        $accountRequestingFor = Account::factory()->create();
        $positionGroup = PositionGroup::factory()->create();

        $otherUserWithPermission = Account::factory()->create();
        $otherUserWithPermission->givePermissionTo('endorsement-request.access');
        $otherUserWithPermission->givePermissionTo('endorsement-request.approve.*');

        $userWithoutPermission = Account::factory()->create();

        $this->actingAsAdminUser(['endorsement-request.access', 'endorsement-request.create.*', 'endorsement-request.approve.*']);

        Livewire::test(CreateEndorsementRequest::class)
            ->set('data.endorsable_type', 'App\Models\Atc\PositionGroup')
            ->fillForm([
                'account_id' => $accountRequestingFor->id,
                'endorsable_id' => $positionGroup->id,
                'notes' => 'This is a test note',
            ])
            ->call('create');

        Notification::assertSentTo($otherUserWithPermission, EndorsementRequestCreated::class);
        Notification::assertSentTo($this->adminUser, EndorsementRequestCreated::class);
        Notification::assertNotSentTo($userWithoutPermission, EndorsementRequestCreated::class);
    }
}
