<?php

declare(strict_types=1);

namespace Tests\Unit\Discord;

use App\Models\Atc\Position;
use App\Models\Discord\DiscordRoleRule;
use App\Models\Mship\Account;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class DiscordRoleRulePositionEndorsementTest extends TestCase
{
    #[Test]
    public function a_position_endorsement_rule_is_not_satisfied_when_the_account_has_no_roster_row(): void
    {
        $position = Position::factory()->create(['type' => Position::TYPE_ENROUTE]);

        $rule = DiscordRoleRule::factory()->create();
        $rule->endorsable()->associate($position);
        $rule->save();

        $account = Account::factory()->create();

        $this->assertFalse($rule->accountSatisfies($account->fresh()));
    }
}
