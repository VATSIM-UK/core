<?php

namespace Tests\Feature\Training\EndorsementRequest;

use App\Filament\Training\Pages\Endorsements\Tables\ResourceTable;
use App\Filament\Training\Resources\EndorsementRequests\EndorsementRequestResource;
use App\Models\Atc\Position;
use App\Models\Atc\PositionGroup;
use App\Models\Cts\Member;
use App\Models\Mship\Account;
use App\Models\Mship\Account\EndorsementRequest;
use App\Models\Mship\State;
use App\Notifications\Mship\Endorsement\SoloEndorsementNotification;
use App\Notifications\Mship\Endorsement\TierEndorsementNotification;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;
use Tests\Feature\TrainingPanel\BaseTrainingPanelTestCase;

class EndorsementRequestApprovalTest extends BaseTrainingPanelTestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        $this->panelUser->givePermissionTo('endorsement-request.access');

        Notification::fake();
    }

    public function test_can_approve_permanent_endorsement_request_with_permission()
    {
        $endorsementRequest = EndorsementRequest::factory()->create([
            'endorsable_type' => PositionGroup::class,
            'endorsable_id' => PositionGroup::factory()->create()->id,
        ]);

        $this->panelUser->givePermissionTo('endorsement-request.approve.*');

        Livewire::actingAs($this->panelUser);
        Livewire::test(ResourceTable::class, ['resource' => EndorsementRequestResource::class])
            ->assertCanSeeTableRecords([$endorsementRequest])
            ->mountTableAction('approve', record: $endorsementRequest->id)
            ->set('mountedActions.0.data.type', 'Permanent')
            ->callMountedTableAction()
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

        $userWithoutPermission = Account::factory()->create();
        Member::factory()->forAccount($userWithoutPermission)->create();
        $userWithoutPermission->givePermissionTo('endorsement-request.access');

        Livewire::actingAs($userWithoutPermission);
        Livewire::test(ResourceTable::class, ['resource' => EndorsementRequestResource::class])
            ->assertCanSeeTableRecords([$endorsementRequest])
            ->assertTableActionHidden('approve', $endorsementRequest->id);
    }

    public function test_sends_notification_to_user_for_permanent_endorsement_when_approved()
    {
        $endorsementRequest = EndorsementRequest::factory()->create([
            'endorsable_type' => PositionGroup::class,
            'endorsable_id' => PositionGroup::factory()->create()->id,
        ]);

        $this->panelUser->givePermissionTo('endorsement-request.approve.*');

        Livewire::actingAs($this->panelUser);
        Livewire::test(ResourceTable::class, ['resource' => EndorsementRequestResource::class])
            ->assertCanSeeTableRecords([$endorsementRequest])
            ->mountTableAction('approve', record: $endorsementRequest->id)
            ->set('mountedActions.0.data.type', 'Permanent')
            ->callMountedTableAction();

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

        $this->panelUser->givePermissionTo('endorsement-request.approve.*');

        Livewire::actingAs($this->panelUser);
        Livewire::test(ResourceTable::class, ['resource' => EndorsementRequestResource::class])
            ->assertCanSeeTableRecords([$endorsementRequest])
            ->assertTableActionVisible('approve', $endorsementRequest->id)
            ->mountTableAction('approve', $endorsementRequest->id)
            ->set('mountedActions.0.data.type', 'Temporary')
            ->set('mountedActions.0.data.days', 7)
            ->callMountedTableAction()
            ->assertTableActionHidden('approve', $endorsementRequest->id);

        $this->assertDatabaseHas('endorsement_requests', [
            'id' => $endorsementRequest->id,
            'actioned_at' => now(),
            'actioned_type' => EndorsementRequest::STATUS_APPROVED,
        ]);

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

        $this->panelUser->givePermissionTo('endorsement-request.approve.*');

        Livewire::actingAs($this->panelUser);
        Livewire::test(ResourceTable::class, ['resource' => EndorsementRequestResource::class])
            ->assertCanSeeTableRecords([$endorsementRequest])
            ->assertTableActionVisible('approve', $endorsementRequest->id)
            ->mountTableAction('approve', $endorsementRequest->id)
            ->set('mountedActions.0.data.type', 'Temporary')
            ->set('mountedActions.0.data.days', 7)
            ->callMountedTableAction()
            ->assertTableActionHidden('approve', $endorsementRequest->id);

        $this->assertDatabaseHas('endorsement_requests', [
            'id' => $endorsementRequest->id,
            'actioned_at' => now(),
            'actioned_type' => EndorsementRequest::STATUS_APPROVED,
        ]);

        Notification::assertNotSentTo($endorsementRequest->account, SoloEndorsementNotification::class);
    }

    public function test_cannot_approve_temporary_endorsement_without_days_input_with_permission()
    {
        $endorsementRequest = EndorsementRequest::factory()->create([
            'endorsable_type' => Position::class,
            'endorsable_id' => Position::factory()->create()->id,
        ]);

        $this->panelUser->givePermissionTo('endorsement-request.approve.*');

        Livewire::actingAs($this->panelUser);
        Livewire::test(ResourceTable::class, ['resource' => EndorsementRequestResource::class])
            ->assertCanSeeTableRecords([$endorsementRequest])
            ->assertTableActionVisible('approve', record: $endorsementRequest)
            ->mountTableAction('approve', record: $endorsementRequest)
            ->callMountedTableAction()
            ->assertHasTableActionErrors(['days']);

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

        $userWithoutPermission = Account::factory()->createQuietly();
        $userWithoutPermission->givePermissionTo('endorsement-request.access');

        Livewire::actingAs($userWithoutPermission);
        Livewire::test(ResourceTable::class, ['resource' => EndorsementRequestResource::class])
            ->assertCanSeeTableRecords([$endorsementRequest])
            ->assertTableActionHidden('approve', $endorsementRequest->id);
    }
}
