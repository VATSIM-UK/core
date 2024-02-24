<?php

namespace Tests\Unit\Account\Endorsement;

use App\Models\Atc\Position;
use App\Models\Atc\PositionGroup;
use App\Models\Mship\Account;
use App\Models\Mship\Account\EndorsementRequest;
use Tests\TestCase;

class EndorsementRequestTest extends TestCase
{
    public function test_can_be_associated_with_position_group()
    {
        $account = Account::factory()->create();
        $positionGroup = PositionGroup::factory()->create();

        $endorsementRequest = EndorsementRequest::factory()->create([
            'account_id' => $account->id,
            'endorsable_id' => $positionGroup->id,
            'endorsable_type' => PositionGroup::class,
            'requested_by' => $this->privacc->id,
        ]);

        $this->assertInstanceOf(PositionGroup::class, $endorsementRequest->endorsable);
    }

    public function test_can_be_associated_with_position()
    {
        $position = Position::factory()->create();

        $endorsementRequest = EndorsementRequest::factory()->create([
            'account_id' => $this->privacc->id,
            'endorsable_id' => $position->id,
            'endorsable_type' => Position::class,
            'requested_by' => $this->privacc->id,
        ]);

        $this->assertInstanceOf(Position::class, $endorsementRequest->endorsable);
    }

    public function test_can_be_associated_with_account()
    {
        $account = Account::factory()->create();
        $positionGroup = PositionGroup::factory()->create();

        $endorsementRequest = EndorsementRequest::factory()->create([
            'account_id' => $account->id,
            'endorsable_id' => $positionGroup->id,
            'endorsable_type' => PositionGroup::class,
            'requested_by' => $this->privacc->id,
        ]);

        $this->assertInstanceOf(Account::class, $endorsementRequest->account);
    }

    public function test_can_be_approved()
    {
        $this->actingAs($this->privacc);

        $account = Account::factory()->create();
        $positionGroup = PositionGroup::factory()->create();

        $endorsementRequest = EndorsementRequest::factory()->create([
            'account_id' => $account->id,
            'endorsable_id' => $positionGroup->id,
            'endorsable_type' => PositionGroup::class,
            'requested_by' => $this->privacc->id,
        ]);

        $endorsementRequest->markApproved();

        $this->assertNotNull($endorsementRequest->actioned_at);
        $this->assertEquals($this->privacc->id, $endorsementRequest->actioned_by);
        $this->assertEquals(EndorsementRequest::STATUS_APPROVED, $endorsementRequest->actioned_type);
    }
}
