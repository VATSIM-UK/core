<?php

namespace Tests\Unit\TeamSpeak;

use App\Listeners\TeamSpeak\AssignAtcServerGroup;
use App\Listeners\TeamSpeak\RemoveAtcServerGroup;
use App\Models\Mship\Account;
use App\Models\TeamSpeak\AtcGroupAssignment;
use App\Models\TeamSpeak\AtcServerGroup;
use App\Models\TeamSpeak\Registration;
use App\Services\TeamSpeak\AtcServerGroupService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use PlanetTeamSpeak\TeamSpeak3Framework\Adapter\ServerQuery\Reply;
use PlanetTeamSpeak\TeamSpeak3Framework\Exception\ServerQueryException;
use PlanetTeamSpeak\TeamSpeak3Framework\Node\Server;
use PlanetTeamSpeak\TeamSpeak3Framework\Node\ServerGroup as TsServerGroup;
use Tests\TestCase;

class AtcServerGroupTest extends TestCase
{
    use DatabaseTransactions;

    private AtcServerGroupService $service;

    private Server $server;

    private Account $account;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = new AtcServerGroupService;
        $this->server = $this->createMock(Server::class);
        $this->account = Account::factory()->create();
    }

    private function makeReply(): Reply
    {
        return $this->getMockBuilder(Reply::class)
            ->disableOriginalConstructor()
            ->getMock();
    }

    private function giveAccountTsRegistration(Account $account, int $dbid = 12345): void
    {
        Registration::factory()->create([
            'account_id' => $account->id,
            'dbid' => $dbid,
            'last_login' => now(),
        ]);
    }

    private function makeTsServerGroup(int $sgid = 100): TsServerGroup
    {
        $node = $this->getMockBuilder(TsServerGroup::class)
            ->disableOriginalConstructor()
            ->getMock();
        $node->method('getId')->willReturn($sgid);

        return $node;
    }

    private function makeServerGroup(string $callsign = 'EGKK_TWR', int $sgid = 100): AtcServerGroup
    {
        return AtcServerGroup::create(['callsign' => $callsign, 'ts_sgid' => $sgid]);
    }

    private function makeAssignment(Account $account, AtcServerGroup $group): AtcGroupAssignment
    {
        return AtcGroupAssignment::create([
            'account_id' => $account->id,
            'atc_server_group_id' => $group->id,
        ]);
    }

    public function test_server_group_is_empty_with_no_assignments()
    {
        $group = $this->makeServerGroup();
        $this->assertTrue($group->isEmpty());
    }

    public function test_server_group_is_not_empty_when_assignments_exist()
    {
        $group = $this->makeServerGroup();
        $this->makeAssignment($this->account, $group);

        $this->assertFalse($group->isEmpty());
    }

    public function test_account_cannot_have_more_than_one_assignment()
    {
        $group = $this->makeServerGroup();
        $this->makeAssignment($this->account, $group);

        $this->expectException(\Illuminate\Database\QueryException::class);

        $this->makeAssignment($this->account, $group);
    }

    public function test_assign_creates_new_ts_server_group_when_none_exists()
    {
        $this->giveAccountTsRegistration($this->account);
        $this->server->method('serverGroupGetByName')
            ->willThrowException(new ServerQueryException('group not found', 0));
        $this->server->expects($this->once())
            ->method('serverGroupCreate')
            ->with('EGKK_TWR')
            ->willReturn(100);
        $this->server->method('request')->willReturn($this->makeReply());

        $this->service->assign($this->account, 'EGKK_TWR', $this->server);

        $this->assertDatabaseHas('teamspeak_atc_server_groups', [
            'callsign' => 'EGKK_TWR',
            'ts_sgid' => 100,
        ]);
    }

    public function test_assign_sets_name_display_permission_on_new_group()
    {
        $this->giveAccountTsRegistration($this->account);
        $this->server->method('serverGroupGetByName')
            ->willThrowException(new ServerQueryException('group not found', 0));
        $this->server->method('serverGroupCreate')->willReturn(100);

        $reply = $this->makeReply();
        $requests = [];
        $this->server->method('request')
            ->willReturnCallback(function (string $cmd) use (&$requests, $reply) {
                $requests[] = $cmd;

                return $reply;
            });

        $this->service->assign($this->account, 'EGKK_TWR', $this->server);

        $this->assertContains('servergroupaddperm sgid=100 permsid=i_group_show_name_in_tree permvalue=1 permnegated=0 permskip=1', $requests);
    }

    public function test_assign_reuses_existing_db_record_without_ts_lookup()
    {
        $this->giveAccountTsRegistration($this->account);
        $this->makeServerGroup('EGKK_TWR', 100);

        $this->server->expects($this->never())->method('serverGroupGetByName');
        $this->server->expects($this->never())->method('serverGroupCreate');
        $this->server->method('request')->willReturn($this->makeReply());

        $this->service->assign($this->account, 'EGKK_TWR', $this->server);

        $this->assertEquals(1, AtcServerGroup::where('callsign', 'EGKK_TWR')->count());
    }

    public function test_assign_creates_assignment_record_in_db()
    {
        $this->giveAccountTsRegistration($this->account);
        $group = $this->makeServerGroup();
        $this->server->method('request')->willReturn($this->makeReply());

        $this->service->assign($this->account, 'EGKK_TWR', $this->server);

        $this->assertDatabaseHas('teamspeak_atc_group_assignments', [
            'account_id' => $this->account->id,
            'atc_server_group_id' => $group->id,
        ]);
    }

    public function test_assign_sends_servergroupaddclient_request()
    {
        $this->giveAccountTsRegistration($this->account, 12345);
        $this->makeServerGroup('EGKK_TWR', 100);

        $reply = $this->makeReply();
        $requests = [];
        $this->server->method('request')
            ->willReturnCallback(function (string $cmd) use (&$requests, $reply) {
                $requests[] = $cmd;

                return $reply;
            });

        $this->service->assign($this->account, 'EGKK_TWR', $this->server);

        $this->assertContains('servergroupaddclient sgid=100 cldbid=12345', $requests);
    }

    public function test_assign_skips_ts_calls_when_account_has_no_registration()
    {
        $this->server->expects($this->never())->method('request');

        $this->service->assign($this->account, 'EGKK_TWR', $this->server);

        $this->assertDatabaseMissing('teamspeak_atc_group_assignments', [
            'account_id' => $this->account->id,
        ]);
    }

    public function test_assign_does_not_create_duplicate_assignment_records()
    {
        $this->giveAccountTsRegistration($this->account);
        $this->makeServerGroup();
        $this->server->method('request')->willReturn($this->makeReply());

        $this->service->assign($this->account, 'EGKK_TWR', $this->server);
        $this->service->assign($this->account, 'EGKK_TWR', $this->server);

        $this->assertEquals(1, AtcGroupAssignment::where('account_id', $this->account->id)->count());
    }

    public function test_assign_releases_existing_assignment_before_assigning_new_one()
    {
        $this->giveAccountTsRegistration($this->account);
        $oldGroup = $this->makeServerGroup('EGLL_APP', 99);
        $newGroup = $this->makeServerGroup('EGKK_TWR', 100);
        $this->makeAssignment($this->account, $oldGroup);
        $this->server->method('request')->willReturn($this->makeReply());

        $this->service->assign($this->account, 'EGKK_TWR', $this->server);

        $this->assertDatabaseMissing('teamspeak_atc_group_assignments', [
            'account_id' => $this->account->id,
            'atc_server_group_id' => $oldGroup->id,
        ]);
        $this->assertDatabaseHas('teamspeak_atc_group_assignments', [
            'account_id' => $this->account->id,
            'atc_server_group_id' => $newGroup->id,
        ]);
    }

    public function test_assign_deletes_old_group_from_db_when_it_becomes_empty()
    {
        $this->giveAccountTsRegistration($this->account);
        $oldGroup = $this->makeServerGroup('EGLL_APP', 99);
        $this->makeAssignment($this->account, $oldGroup);
        $this->makeServerGroup('EGKK_TWR', 100);
        $this->server->method('request')->willReturn($this->makeReply());

        $this->service->assign($this->account, 'EGKK_TWR', $this->server);

        $this->assertDatabaseMissing('teamspeak_atc_server_groups', ['callsign' => 'EGLL_APP']);
    }

    public function test_assign_preserves_old_group_when_other_assignments_remain()
    {
        $this->giveAccountTsRegistration($this->account);
        $otherAccount = Account::factory()->create();
        $oldGroup = $this->makeServerGroup('EGLL_APP', 99);
        $this->makeAssignment($this->account, $oldGroup);
        $this->makeAssignment($otherAccount, $oldGroup);
        $this->makeServerGroup('EGKK_TWR', 100);
        $this->server->method('request')->willReturn($this->makeReply());

        $this->service->assign($this->account, 'EGKK_TWR', $this->server);

        $this->assertDatabaseHas('teamspeak_atc_server_groups', ['id' => $oldGroup->id]);
    }

    public function test_release_removes_assignment_record_from_db()
    {
        $group = $this->makeServerGroup();
        $this->makeAssignment($this->account, $group);
        $this->server->method('request')->willReturn($this->makeReply());

        $this->service->release($this->account, $this->server);

        $this->assertDatabaseMissing('teamspeak_atc_group_assignments', [
            'account_id' => $this->account->id,
        ]);
    }

    public function test_release_sends_servergroupdelclient_request()
    {
        $this->giveAccountTsRegistration($this->account, 12345);
        $group = $this->makeServerGroup('EGKK_TWR', 100);
        $this->makeAssignment($this->account, $group);

        $reply = $this->makeReply();
        $requests = [];
        $this->server->method('request')
            ->willReturnCallback(function (string $cmd) use (&$requests, $reply) {
                $requests[] = $cmd;

                return $reply;
            });

        $this->service->release($this->account, $this->server);

        $this->assertContains('servergroupdelclient sgid=100 cldbid=12345', $requests);
    }

    public function test_release_deletes_empty_server_group_from_db()
    {
        $group = $this->makeServerGroup();
        $this->makeAssignment($this->account, $group);
        $this->server->method('request')->willReturn($this->makeReply());

        $this->service->release($this->account, $this->server);

        $this->assertDatabaseMissing('teamspeak_atc_server_groups', ['id' => $group->id]);
    }

    public function test_release_preserves_group_when_other_assignments_remain()
    {
        $otherAccount = Account::factory()->create();
        $group = $this->makeServerGroup();
        $this->makeAssignment($this->account, $group);
        $this->makeAssignment($otherAccount, $group);
        $this->server->method('request')->willReturn($this->makeReply());

        $this->service->release($this->account, $this->server);

        $this->assertDatabaseHas('teamspeak_atc_server_groups', ['id' => $group->id]);
        $this->assertDatabaseHas('teamspeak_atc_group_assignments', [
            'account_id' => $otherAccount->id,
        ]);
    }

    public function test_prune_empty_groups_deletes_empty_group_from_db()
    {
        $group = $this->makeServerGroup();
        $this->server->method('request')->willReturn($this->makeReply());

        $count = $this->service->pruneEmptyGroups($this->server);

        $this->assertEquals(1, $count);
        $this->assertDatabaseMissing('teamspeak_atc_server_groups', ['id' => $group->id]);
    }

    public function test_prune_empty_groups_sends_servergroupdel_request()
    {
        $this->makeServerGroup('EGKK_TWR', 100);

        $reply = $this->makeReply();
        $requests = [];
        $this->server->method('request')
            ->willReturnCallback(function (string $cmd) use (&$requests, $reply) {
                $requests[] = $cmd;

                return $reply;
            });

        $this->service->pruneEmptyGroups($this->server);

        $this->assertContains('servergroupdel sgid=100 force=1', $requests);
    }

    public function test_prune_empty_groups_skips_groups_with_assignments()
    {
        $group = $this->makeServerGroup();
        $this->makeAssignment($this->account, $group);

        $this->server->expects($this->never())->method('request');

        $count = $this->service->pruneEmptyGroups($this->server);

        $this->assertEquals(0, $count);
        $this->assertDatabaseHas('teamspeak_atc_server_groups', ['id' => $group->id]);
    }

    public function test_prune_empty_groups_handles_mix_of_empty_and_populated_groups()
    {
        $otherAccount = Account::factory()->create();
        $emptyGroup = $this->makeServerGroup('EGKK_TWR', 100);
        $populatedGroup = $this->makeServerGroup('EGLL_APP', 101);
        $this->makeAssignment($otherAccount, $populatedGroup);
        $this->server->method('request')->willReturn($this->makeReply());

        $count = $this->service->pruneEmptyGroups($this->server);

        $this->assertEquals(1, $count);
        $this->assertDatabaseMissing('teamspeak_atc_server_groups', ['id' => $emptyGroup->id]);
        $this->assertDatabaseHas('teamspeak_atc_server_groups', ['id' => $populatedGroup->id]);
    }

    public function test_assign_listener_uses_teamspeak_queue()
    {
        $listener = new AssignAtcServerGroup($this->service);
        $this->assertEquals('teamspeak', $listener->queue);
    }

    public function test_remove_listener_uses_teamspeak_queue()
    {
        $listener = new RemoveAtcServerGroup($this->service);
        $this->assertEquals('teamspeak', $listener->queue);
    }
}
