<?php

namespace Tests\Feature\Admin\EndorsementRequest;

use App\Filament\Resources\EndorsementRequestResource\Pages\ListEndorsementRequests;
use App\Models\Atc\Position;
use App\Models\Atc\PositionGroup;
use App\Models\Mship\Account;
use App\Models\Mship\Account\EndorsementRequest;
use App\Models\Mship\State;
use App\Notifications\Mship\Endorsement\SoloEndorsementNotification;
use App\Notifications\Mship\Endorsement\TierEndorsementNotification;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;
use Tests\Feature\Admin\BaseAdminTestCase;

class EndorsementRequestApprovalTest extends BaseAdminTestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        $this->adminUser->givePermissionTo('endorsement-request.access');

        Notification::fake();
    }

    public function test_can_approve_permanent_endorsement_request_with_permission()
    {
        $endorsementRequest = EndorsementRequest::factory()->create([
            'endorsable_type' => PositionGroup::class,
            'endorsable_id' => PositionGroup::factory()->create()->id,
        ]);

        $this->adminUser->givePermissionTo('endorsement-request.approve.*');

        Livewire::actingAs($this->adminUser);
        Livewire::test(ListEndorsementRequests::class)
            ->assertCanSeeTableRecords([$endorsementRequest])
            ->callTableAction('approve', record: $endorsementRequest->id, data: [
                'type' => 'Permanent',
            ])
            ->assertTableActionHidden('approve', $endorsementRequest->id);

        $this->assertDatabaseHas('endorsement_requests', [
            'id' => $endorsementRequest->id,
            'actioned_at' => now(),
            'actioned_type' => EndorsementRequest::STATUS_APPROVED,
        ]);
    }

    public function test_cannot_approve_permanent_endorsement_request_without_permission()
    {
        $endorsementRequest = EndorsementRequest::factory()->create([
            'endorsable_type' => PositionGroup::class,
            'endorsable_id' => PositionGroup::factory()->create()->id,
        ]);

        Livewire::actingAs($this->adminUser);
        Livewire::test(ListEndorsementRequests::class)
            ->assertCanSeeTableRecords([$endorsementRequest])
            ->assertTableActionHidden('approve', $endorsementRequest->id);
    }

    public function test_sends_notification_to_user_for_permanent_endorsement_when_approved()
    {
        Notification::fake();

        $endorsementRequest = EndorsementRequest::factory()->create([
            'endorsable_type' => PositionGroup::class,
            'endorsable_id' => PositionGroup::factory()->create()->id,
        ]);

        $this->adminUser->givePermissionTo('endorsement-request.approve.*');

        Livewire::actingAs($this->adminUser);
        Livewire::test(ListEndorsementRequests::class)
            ->assertCanSeeTableRecords([$endorsementRequest])
            ->callTableAction('approve', record: $endorsementRequest->id, data: [
                'type' => 'Permanent',
            ]);

        $this->assertDatabaseHas('mship_account_endorsement', [
            'account_id' => $endorsementRequest->account_id,
            'endorsable_id' => $endorsementRequest->endorsable_id,
            'endorsable_type' => $endorsementRequest->endorsable_type,
        ]);

        Notification::assertSentTo($endorsementRequest->account, TierEndorsementNotification::class, function ($notification) use ($endorsementRequest) {
            return $notification->endorsement->endorsable->id === $endorsementRequest->endorsable_id;
        });

        Notification::assertNotSentTo($endorsementRequest->account, SoloEndorsementNotification::class);
    }

    public function test_can_approve_temporary_endorsement_with_days_input_with_permission_home_member()
    {
        $homeMember = Account::factory()->create();
        $homeMember->addState(State::findByCode('DIVISION'));

        $endorsementRequest = EndorsementRequest::factory()->create([
            'account_id' => $homeMember,
            'endorsable_type' => Position::class,
            'endorsable_id' => Position::factory()->create()->id,
        ]);

        $this->adminUser->givePermissionTo('endorsement-request.approve.*');

        Livewire::actingAs($this->adminUser);
        Livewire::test(ListEndorsementRequests::class)
            ->assertCanSeeTableRecords([$endorsementRequest])
            ->assertTableActionVisible('approve', $endorsementRequest->id)
            ->callTableAction('approve', $endorsementRequest->id, [
                'type' => 'Temporary',
                'days' => 7,
            ])
            ->assertTableActionHidden('approve', $endorsementRequest->id);

        $this->assertDatabaseHas('endorsement_requests', [
            'id' => $endorsementRequest->id,
            'actioned_at' => now(),
            'actioned_type' => EndorsementRequest::STATUS_APPROVED,
        ]);

        // check the subject of the request was sent the notification
        Notification::assertSentTo($endorsementRequest->account, SoloEndorsementNotification::class);
    }

    public function test_can_approve_temporary_endorsement_non_home_member()
    {
        $nonHomeMember = Account::factory()->create();
        $nonHomeMember->addState(State::findByCode('VISITING'));

        $endorsementRequest = EndorsementRequest::factory()->create([
            'account_id' => $nonHomeMember,
            'endorsable_type' => Position::class,
            'endorsable_id' => Position::factory()->create()->id,
        ]);

        $this->adminUser->givePermissionTo('endorsement-request.approve.*');

        Livewire::actingAs($this->adminUser);
        Livewire::test(ListEndorsementRequests::class)
            ->assertCanSeeTableRecords([$endorsementRequest])
            ->assertTableActionVisible('approve', $endorsementRequest->id)
            ->callTableAction('approve', $endorsementRequest->id, [
                'type' => 'Temporary',
                'days' => 7,
            ])
            ->assertTableActionHidden('approve', $endorsementRequest->id);

        $this->assertDatabaseHas('endorsement_requests', [
            'id' => $endorsementRequest->id,
            'actioned_at' => now(),
            'actioned_type' => EndorsementRequest::STATUS_APPROVED,
        ]);

        // check the subject of the request was not sent the notification
        Notification::assertNotSentTo($endorsementRequest->account, SoloEndorsementNotification::class);
    }

    public function test_cannot_approve_temporary_endorsement_without_days_input_with_permission()
    {
        $endorsementRequest = EndorsementRequest::factory()->create([
            'endorsable_type' => Position::class,
            'endorsable_id' => Position::factory()->create()->id,
        ]);

        $this->adminUser->givePermissionTo('endorsement-request.approve.*');

        Livewire::actingAs($this->adminUser);
        Livewire::test(ListEndorsementRequests::class)
            ->assertCanSeeTableRecords([$endorsementRequest])
            ->assertTableActionVisible('approve', $endorsementRequest->id)
            ->callTableAction('approve', $endorsementRequest->id)
            ->assertTableActionVisible('approve', $endorsementRequest->id);

        $this->assertDatabaseHas('endorsement_requests', [
            'id' => $endorsementRequest->id,
            'actioned_at' => null,
            'actioned_type' => null,
        ]);
    }

    public function test_cannot_approve_temporary_endorsement_without_permission()
    {
        $endorsementRequest = EndorsementRequest::factory()->create([
            'endorsable_type' => Position::class,
            'endorsable_id' => Position::factory()->create()->id,
        ]);

        Livewire::actingAs($this->adminUser);
        Livewire::test(ListEndorsementRequests::class)
            ->assertCanSeeTableRecords([$endorsementRequest])
            ->assertTableActionHidden('approve', $endorsementRequest->id);
    }
}
