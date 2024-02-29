<?php

namespace Tests\Feature\Admin\EndorsementRequest;

use App\Filament\Resources\EndorsementRequestResource\Pages\ListEndorsementRequests;
use App\Models\Atc\Position;
use App\Models\Atc\PositionGroup;
use App\Models\Mship\Account\EndorsementRequest;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Livewire\Livewire;
use Tests\Feature\Admin\BaseAdminTestCase;

class EndorsementRequestApprovalTest extends BaseAdminTestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        $this->adminUser->givePermissionTo('endorsement-request.access');
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

    public function test_can_approve_temporary_endorsement_with_days_input_with_permission()
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
