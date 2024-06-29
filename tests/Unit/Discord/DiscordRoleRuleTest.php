<?php

namespace Tests\Unit\Discord;

use App\Models\Atc\PositionGroup;
use App\Models\Cts\Member;
use App\Models\Discord\DiscordRoleRule;
use App\Models\Mship\Account;
use App\Models\Mship\Qualification;
use App\Models\Mship\State;
use App\Models\Roster;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use PHPUnit\Framework\Attributes\DataProvider;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class DiscordRoleRuleTest extends TestCase
{
    use DatabaseTransactions;

    #[DataProvider('providerTestData')]
    public function testItReportsAccountSatisfiesCorrectly($permSetup, $stateSetup, $qualSetup, $ctsControlSetup, $endorsableSetup, $expected)
    {
        $state = State::first();

        $positionGroup = PositionGroup::factory()->create();

        $role = DiscordRoleRule::factory()->create(
            [
                'permission_id' => $permSetup[1] ? factory(Permission::class)->create() : null,
                'state_id' => $stateSetup[1] ? $state : null,
                'qualification_id' => $qualSetup[1] ? Qualification::factory()->create() : null,
                'cts_may_control_contains' => $ctsControlSetup[1] ? 'CTS_SEARCH_QUERY' : null,
            ]
        );

        if ($endorsableSetup[1]) {
            $role->endorsable()->associate($positionGroup);
        }

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

        // Add endorsement, qualification and home state
        if ($endorsableSetup[0]) {
            Roster::create(['account_id' => $account->getKey()]);
            $account->endorsements()->create([
                'endorsable_id' => $positionGroup->id,
                'endorsable_type' => PositionGroup::class,
            ]);
            $account->addQualification(Qualification::factory()->atc()->create());
            $account->addState($state);
        }

        $this->assertEquals($expected, $role->accountSatisfies($account->fresh()));
    }

    public static function providerTestData()
    {
        return [
            // [Has Perm, Requires Perm], [Has State, Requires State], [Has Qual, Requires Qual], [Has CTS, Requires CTS], [Has Endorsement, Requires Endorsement], Expected
            [[false, false], [false, false], [false, false], [false, false], [false, false], true],
            [[false, true], [false, true], [false, true], [false, true], [false, true], false],

            [[true, true], [false, true], [false, true], [false, true], [false, true], false], // Check no one requirement ends up in truthy
            [[false, true], [true, true], [false, true], [false, true], [false, true], false],
            [[false, true], [false, true], [true, true], [false, true], [false, true], false],
            [[false, true], [false, true], [false, true], [true, true], [false, true], false],
            [[false, true], [false, true], [false, true], [false, true], [false, true], false],

            [[true, true], [true, true], [true, true], [false, true], [false, true], false],
            [[true, true], [true, true], [true, true], [true, true], [true, true], true],

            [[true, true], [false, false], [false, false], [false, false], [false, false], true],
            [[false, false], [true, true], [false, false], [false, false], [false, false], true],
            [[false, false], [false, false], [true, true], [false, false], [false, false], true],
            [[false, false], [false, false], [false, false], [true, true], [false, false], true],
            [[false, false], [false, false], [false, false], [false, false], [true, true], true],
        ];
    }
}
