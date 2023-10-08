<?php

namespace Tests\Unit\Discord;

use App\Models\Cts\Member;
use App\Models\Discord\DiscordRoleRule;
use App\Models\Mship\Account;
use App\Models\Mship\Qualification;
use App\Models\Mship\State;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use PHPUnit\Framework\Attributes\DataProvider;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class DiscordRoleRuleTest extends TestCase
{
    use DatabaseTransactions;

    #[DataProvider(method: 'providerTestData')]
    public function itReportsAccountSatisfiesCorrectly($permSetup, $stateSetup, $qualSetup, $ctsControlSetup, $expected)
    {
        $state = State::first();

        $role = DiscordRoleRule::factory()->create(
            [
                'permission_id' => $permSetup[1] ? factory(Permission::class)->create() : null,
                'state_id' => $stateSetup[1] ? $state : null,
                'qualification_id' => $qualSetup[1] ? Qualification::factory()->create() : null,
                'cts_may_control_contains' => $ctsControlSetup[1] ? 'CTS_SEARCH_QUERY' : null,
            ]
        );

        // Setup CTS may control
        $member = factory(Member::class)->create([
            'visit_may_control' => $ctsControlSetup[0] ? 'Some Group / CTS_SEARCH_QUERY / Another Group' : 'Some Group / Another Group',
        ]);
        $account = Account::factory()->create(['id' => $member->cid]);

        // Setup Perm
        if ($permSetup[0]) {
            $account->givePermissionTo($role->permission);
        }

        // Setup State
        if ($stateSetup[0]) {
            $account->addState($state);
        }

        // Setup Qualification
        if ($qualSetup[0]) {
            $account->addQualification($role->qualification);
        }

        $this->assertEquals($expected, $role->accountSatisfies($account->fresh()));
    }

    public static function providerTestData()
    {
        return [
            // [Has Perm, Requires Perm], [Has State, Requires State], [Has Qual, Requires Qual], [Has CTS, Requires CTS], Expected
            [[false, false], [false, false], [false, false], [false, false], true],
            [[false, true], [false, true], [false, true], [false, true], false],

            [[true, true], [false, true], [false, true], [false, true], false], // Check no one requirement ends up in truthy
            [[false, true], [true, true], [false, true], [false, true], false],
            [[false, true], [false, true], [true, true], [false, true], false],
            [[false, true], [false, true], [false, true], [true, true], false],

            [[true, true], [true, true], [true, true], [false, true], false],
            [[true, true], [true, true], [true, true], [true, true], true],

            [[true, true], [false, false], [false, false], [false, false], true],
            [[false, false], [true, true], [false, false], [false, false], true],
            [[false, false], [false, false], [true, true], [false, false], true],
            [[false, false], [false, false], [false, false], [true, true], true],
        ];
    }
}
