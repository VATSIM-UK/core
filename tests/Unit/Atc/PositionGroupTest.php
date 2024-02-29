<?php

namespace Tests\Unit\Atc;

use App\Models\Atc\PositionGroup;
use App\Models\Mship\Account;
use App\Models\Mship\Account\Endorsement;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class PositionGroupTest extends TestCase
{
    use DatabaseTransactions;

    public function test_detects_account_assigned_endorsement_for_group()
    {
        $account = Account::factory()->create();
        $positionGroup = PositionGroup::factory()->create();
        Endorsement::factory()->create([
            'account_id' => $account->id,
            'endorsable_type' => PositionGroup::class,
            'endorsable_id' => $positionGroup->id,
        ]);

        $result = $positionGroup->unassignedFor($account);

        $this->assertFalse($result->contains($positionGroup));
    }

    public function test_detects_account_not_assigned_endorsement_for_group()
    {
        $account = Account::factory()->create();
        $positionGroup = PositionGroup::factory()->create();
        $otherPositionGroup = PositionGroup::factory()->create();

        Endorsement::factory()->create([
            'account_id' => $account->id,
            'endorsable_type' => PositionGroup::class,
            'endorsable_id' => $otherPositionGroup->id,
        ]);

        $result = $positionGroup->unassignedFor($account);

        $this->assertTrue($result->contains($positionGroup));
        $this->assertFalse($result->contains($otherPositionGroup));
    }

    public function test_detects_as_not_assigned_when_solo_endorsement_expired()
    {
        $account = Account::factory()->create();
        $positionGroup = PositionGroup::factory()->create();
        $otherPositionGroup = PositionGroup::factory()->create();

        Endorsement::factory()->create([
            'account_id' => $account->id,
            'endorsable_type' => PositionGroup::class,
            'endorsable_id' => $otherPositionGroup->id,
            'expires_at' => now()->subDay(),
        ]);

        $result = $positionGroup->unassignedFor($account);

        $this->assertTrue($result->contains($positionGroup));
        $this->assertFalse($result->contains($otherPositionGroup));
    }

    public function test_detects_when_active_solo_endorsement_assigned()
    {
        $account = Account::factory()->create();
        $positionGroup = PositionGroup::factory()->create();
        $otherPositionGroup = PositionGroup::factory()->create();

        Endorsement::factory()->create([
            'account_id' => $account->id,
            'endorsable_type' => PositionGroup::class,
            'endorsable_id' => $otherPositionGroup->id,
            'expires_at' => now()->addDay(),
        ]);

        $result = $positionGroup->unassignedFor($account);

        $this->assertTrue($result->contains($positionGroup));
        $this->assertFalse($result->contains($otherPositionGroup));
    }
}
