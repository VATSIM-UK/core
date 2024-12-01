<?php

namespace Tests\Unit;

use App\Models\Mship\Qualification;
use App\Models\TeamSpeak\Channel;
use App\Models\TeamSpeak\ChannelGroup;
use App\Models\TeamSpeak\ServerGroup;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class TeamSpeakTest extends TestCase
{
    use DatabaseTransactions;

    protected $channel;

    protected $channelGroups;

    protected $serverGroups;

    protected function setUp(): void
    {
        parent::setUp();

        $this->channel = factory(Channel::class)->create();
        $this->channel->children()->save(factory(Channel::class)->make());
        $this->channel->children()->save(factory(Channel::class)->make());
        $this->channel->children()->save(factory(Channel::class)->make());
        $this->channel->parent()->associate(factory(Channel::class)->create());
        $this->channel->save();
        $this->channel = $this->channel->fresh(['parent', 'children']);

        $this->serverGroups = factory(ServerGroup::class, 5)->create();
        $this->channelGroups = factory(ChannelGroup::class, 5)->create();

        $this->account = \App\Models\Mship\Account::factory()->create([
            'name_first' => 'John',
            'name_last' => 'Doe',
            'email' => 'i_sleep@gmail.com',
        ]);
    }

    public function test_channel_parent()
    {
        $parent = $this->channel->parent;
        $this->assertEquals($parent->id, $this->channel->parent_id);
        $this->assertNull($parent->parent);
    }

    public function test_channel_children()
    {
        $children = $this->channel->children;
        $this->assertEquals(3, count($children));
        $child = $children->first();
        $this->assertEquals($child->parent_id, $this->channel->id);
        $this->assertEmpty($child->children);
    }

    public function test_channel_protection()
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

    public function test_channel_group_doesnt_retrieve_server_groups()
    {
        $groups = ChannelGroup::where('type', 's')->get();
        $this->assertEquals(0, count($groups));
    }

    public function test_server_group_doesnt_retrieve_channel_groups()
    {
        $groups = ServerGroup::where('type', 'c')->get();
        $this->assertEquals(0, count($groups));
    }

    public function test_group_permission()
    {
        $group = $this->serverGroups->first();
        $permission = factory(Permission::class)->create();
        $group->permission()->associate($permission)->save();
        $group = $group->fresh(['permission']);
        $this->assertEquals($group->permission->id, $permission->id);
    }

    public function test_group_qualification()
    {
        $group = $this->serverGroups->first();
        $qualification = Qualification::factory()->create();
        $group->qualification()->associate($qualification)->save();
        $group = $group->fresh(['qualification']);
        $this->assertEquals($group->qualification->id, $qualification->id);
    }

    public function test_valid_display_name()
    {
        $validDisplayName = 'John Doe';
        $this->assertEquals(true, $this->account->isValidDisplayName($validDisplayName));
    }

    public function test_invalid_display_name()
    {
        $invalidDisplayName = 'John Do';
        $this->assertFalse($this->account->isValidDisplayName($invalidDisplayName));
    }

    public function test_partially_valid_display_name()
    {
        $validDisplayName = 'John Doe - EGKK_TWR';
        $this->assertTrue($this->account->isPartiallyValidDisplayName($validDisplayName));
    }

    public function test_not_partially_valid_display_name()
    {
        $validDisplayName = 'John Do - EGKK_TWR';
        $this->assertFalse($this->account->isPartiallyValidDisplayName($validDisplayName));
    }

    public function test_allowed_duplicates()
    {
        $this->assertFalse($this->account->isDuplicateDisplayName('John Do1'));
        $this->assertTrue($this->account->isDuplicateDisplayName('John Doe1'));
    }

    // TODO: registration tests
    // TODO: confirmation tests
}
