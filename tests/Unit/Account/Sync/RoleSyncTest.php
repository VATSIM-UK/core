<?php

namespace Tests\Unit\Account\Sync;

use Tests\TestCase;
use App\Jobs\Mship\SyncToCTS;
use App\Jobs\Mship\SyncToMoodle;
use App\Jobs\Mship\SyncToHelpdesk;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Queue;
use App\Events\Mship\Roles\RoleRemoved;
use App\Events\Mship\Roles\RoleAssigned;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class RoleSyncTest extends TestCase
{
    use DatabaseTransactions;

    protected $role;

    protected function setUp(): void
    {
        parent::setUp();

        $this->role = factory(Role::class)->create();

        Cache::flush(); // Remove time lockout cache
    }

    /** @test */
    public function itTriggersEvent()
    {
        Event::fake();
        event(new RoleAssigned($this->user, $this->role));
        event(new RoleRemoved($this->user, $this->role));
        Event::assertDispatched(RoleAssigned::class);
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
        //Queue::assertPushed(SyncToForums::class, 1);
    }

    /** @test */
    public function itTriggersJobsWhenARoleIsRemoved()
    {
        Queue::fake();

        event(new RoleRemoved($this->user, $this->role));

        Queue::assertPushed(SyncToCTS::class, 1);
        Queue::assertPushed(SyncToMoodle::class, 1);
        Queue::assertPushed(SyncToHelpdesk::class, 1);
        //Queue::assertPushed(SyncToForums::class, 1);
    }
}
