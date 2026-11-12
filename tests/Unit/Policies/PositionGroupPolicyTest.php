<?php

namespace Tests\Unit\Policies;

use App\Models\Atc\PositionGroup;
use App\Models\Mship\Account;
use App\Policies\PositionGroupPolicy;
use Tests\TestCase;

class PositionGroupPolicyTest extends TestCase
{
    public function test_update_is_not_granted_by_view_permission(): void
    {
        $account = Account::factory()->create();
        $account->givePermissionTo('position-group.view.*');

        $this->assertFalse((new PositionGroupPolicy)->update($account, new PositionGroup));
    }
}
