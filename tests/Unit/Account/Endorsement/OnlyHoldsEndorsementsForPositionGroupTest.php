<?php

namespace Tests\Unit\Account\Endorsement;

use App\Models\Atc\Position;
use App\Models\Atc\PositionGroup;
use App\Models\Mship\Account;
use App\Models\Mship\Account\Endorsement;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class OnlyHoldsEndorsementsForPositionGroupTest extends TestCase
{
    #[Test]
    public function it_treats_a_position_within_the_group_as_an_endorsement_for_the_group(): void
    {
        $positionGroup = $this->makePositionGroup();
        $position = Position::factory()->create();
        $positionGroup->positions()->attach($position->id);

        $account = $this->endorse(Account::factory()->create(), Position::class, $position->id);

        $this->assertTrue($account->onlyHoldsEndorsementsForPositionGroup($positionGroup));
    }

    #[Test]
    public function it_returns_false_when_the_account_has_no_active_endorsements(): void
    {
        $positionGroup = $this->makePositionGroup();

        $this->assertFalse(Account::factory()->create()->onlyHoldsEndorsementsForPositionGroup($positionGroup));
    }

    #[Test]
    public function it_returns_false_when_the_only_endorsement_has_expired(): void
    {
        $positionGroup = $this->makePositionGroup();

        $account = $this->endorse(Account::factory()->create(), PositionGroup::class, $positionGroup->id, now()->subDay());

        $this->assertFalse($account->onlyHoldsEndorsementsForPositionGroup($positionGroup));
    }

    #[Test]
    public function it_returns_false_when_the_position_is_outside_the_group(): void
    {
        $positionGroup = $this->makePositionGroup();
        $position = Position::factory()->create();

        $account = $this->endorse(Account::factory()->create(), Position::class, $position->id);

        $this->assertFalse($account->onlyHoldsEndorsementsForPositionGroup($positionGroup));
    }

    private function makePositionGroup(): PositionGroup
    {
        return PositionGroup::factory()->create(['name' => PositionGroup::SHANWICK_POSITION_GROUP]);
    }

    private function endorse(Account $account, string $endorsableType, int $endorsableId, mixed $expiresAt = null): Account
    {
        Endorsement::createQuietly([
            'account_id' => $account->id,
            'endorsable_type' => $endorsableType,
            'endorsable_id' => $endorsableId,
            'expires_at' => $expiresAt,
        ]);

        return $account->fresh();
    }
}
