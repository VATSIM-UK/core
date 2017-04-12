<?php

namespace Tests\Unit;

use App\Models\TeamSpeak\Channel;
use App\Models\TeamSpeak\ChannelGroup;
use App\Models\TeamSpeak\ServerGroup;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class TeamSpeakTest extends TestCase
{
    use DatabaseTransactions;

    protected $channel, $channelGroups, $serverGroups;

    public function setUp()
    {
        parent::setUp();

        $this->channel = factory(Channel::class)->create();
        $this->channel->children()->save(factory(Channel::class)->make());
        $this->channel->children()->save(factory(Channel::class)->make());
        $this->channel->children()->save(factory(Channel::class)->make());
        $this->channel->parent()->associate(factory(Channel::class)->create());
        $this->channel->save();
        $this->channel = $this->channel->fresh(['parent', 'children']);

        $this->serverGroups = factory(\App\Models\TeamSpeak\ServerGroup::class, 5)->create();
        $this->channelGroups = factory(\App\Models\TeamSpeak\ChannelGroup::class, 5)->create();

        $this->account = factory(\App\Models\Mship\Account::class)->create([
            "name_first" => "John",
            "name_last" => "Doe",
            "email" => "i_sleep@gmail.com",
        ]);
    }

    public function testChannelParent()
    {
        $parent = $this->channel->parent;
        $this->assertEquals($parent->id, $this->channel->parent_id);
        $this->assertNull($parent->parent);
    }

    public function testChannelChildren()
    {
        $children = $this->channel->children;
        $this->assertEquals(3, count($children));
        $child = $children->first();
        $this->assertEquals($child->parent_id, $this->channel->id);
        $this->assertEmpty($child->children);
    }

    public function testChannelProtection()
    {
        $this->assertFalse($this->channel->protected);
        $this->channel->update(['protected' => 0]);
        $this->channel = $this->channel->fresh(['children']);
        $this->assertFalse($this->channel->protected);
        $this->assertFalse($this->channel->children->first()->protected);
        $this->channel->update(['protected' => 1]);
        $this->channel = $this->channel->fresh(['children']);
        $this->assertTrue($this->channel->protected);
        $this->assertTrue($this->channel->children->first()->protected);
    }

    public function testChannelGroupDoesntRetrieveServerGroups()
    {
        $groups = ChannelGroup::where('type', 's')->get();
        $this->assertEquals(0, count($groups));
    }

    public function testServerGroupDoesntRetrieveChannelGroups()
    {
        $groups = ServerGroup::where('type', 'c')->get();
        $this->assertEquals(0, count($groups));
    }

    public function testGroupPermission()
    {
        $group = $this->serverGroups->first();
        $permission = factory(\App\Models\Mship\Permission::class)->create();
        $group->permission()->associate($permission)->save();
        $group = $group->fresh(['permission']);
        $this->assertEquals($group->permission->id, $permission->id);
    }

    public function testGroupQualification()
    {
        $group = $this->serverGroups->first();
        $qualification = factory(\App\Models\Mship\Qualification::class)->create();
        $group->qualification()->associate($qualification)->save();
        $group = $group->fresh(['qualification']);
        $this->assertEquals($group->qualification->id, $qualification->id);
    }

    public function testValidDisplayName()
    {
        $account = $this->account;
        $validDisplayName = "John Doe";
        $this->assertEquals(true, $account->isValidDisplayName($validDisplayName));
    }
    public function testInvalidDisplayName()
    {
        $account = $this->account;
        $invalidDisplayName = "John Do";
        $this->assertEquals(false, $account->isValidDisplayName($invalidDisplayName));
    }

    public function testPartiallyValidDisplayName()
    {
        $account = $this->account;
        $validDisplayName = "John Doe - EGKK_TWR";
        $this->assertEquals(true, $account->isPartiallyValidDisplayName($validDisplayName));
    }

    public function testNotPartiallyValidDisplayName()
    {
        $account = $this->account;
        $validDisplayName = "John Do - EGKK_TWR";
        $this->assertEquals(false, $account->isPartiallyValidDisplayName($validDisplayName));
    }

    // TODO: registration tests
    // TODO: confirmation tests
}
