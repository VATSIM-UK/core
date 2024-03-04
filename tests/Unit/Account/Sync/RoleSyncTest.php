<?php

namespace Tests\Unit\Account\Sync;

use App\Events\Mship\Roles\RoleAssigned;
use App\Events\Mship\Roles\RoleRemoved;
use App\Jobs\Mship\SyncToCTS;
use App\Jobs\Mship\SyncToDiscord;
use App\Jobs\Mship\SyncToForums;
use App\Jobs\Mship\SyncToHelpdesk;
use App\Jobs\Mship\SyncToMoodle;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Queue;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class RoleSyncTest extends TestCase
{
    use DatabaseTransactions;

    protected $role;

    protected function setUp(): void
    {
        parent::setUp();

        $this->role = factory(Role::class)->create();

        // Disable Discord connection
        config(['services.discord.token' => null]);
        $this->user->discord_id = 1234;
        $this->user->saveQuietly();

        Cache::flush(); // Remove time lockout cache
    }

    /** @test */
    public function itTriggersEvent()
    {
        Event::fake();

        $this->user->assignRole($this->role);
        Event::assertDispatched(RoleAssigned::class);

        $this->user->removeRole($this->role);
        Event::assertDispatched(RoleRemoved::class);
    }

    /** @test */
    public function itTriggersJobsWhenARoleIsAssigned()
    {
        Queue::fake();

        event(new RoleAssigned($this->user, $this->role));

        Queue::assertPushed(SyncToCTS::class, 1);
        Queue::assertPushed(SyncToMoodle::class, 1);
        Queue::assertPushed(SyncToHelpdesk::class, 1);
        Queue::assertPushed(SyncToDiscord::class, 1);
        Queue::assertPushed(SyncToForums::class, 1);
    }

    /** @test */
    public function itTriggersJobsWhenARoleIsRemoved()
    {
        Queue::fake();

        event(new RoleRemoved($this->user, $this->role));

        Queue::assertPushed(SyncToCTS::class, 1);
        Queue::assertPushed(SyncToMoodle::class, 1);
        Queue::assertPushed(SyncToHelpdesk::class, 1);
        Queue::assertPushed(SyncToDiscord::class, 1);
        Queue::assertPushed(SyncToForums::class, 1);
    }
}
