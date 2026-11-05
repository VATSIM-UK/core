<?php

namespace Tests\Unit\Account\Sync;

use App\Events\Mship\Roles\RoleAssigned;
use App\Events\Mship\Roles\RoleRemoved;
use App\Jobs\Mship\ReconcileHelpdeskStaff;
use App\Jobs\Mship\SyncToCTS;
use App\Jobs\Mship\SyncToDiscord;
use App\Jobs\Mship\SyncToHelpdesk;
use App\Jobs\Mship\SyncToMoodle;
use App\Models\Mship\RoleHelpdeskStaffSetting;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Queue;
use PHPUnit\Framework\Attributes\Test;
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

    #[Test]
    public function it_triggers_event()
    {
        Event::fake();

        $this->user->assignRole($this->role);
        Event::assertDispatched(RoleAssigned::class);

        $this->user->removeRole($this->role);
        Event::assertDispatched(RoleRemoved::class);
    }

    #[Test]
    public function it_triggers_jobs_when_a_role_is_assigned()
    {
        Queue::fake();

        event(new RoleAssigned($this->user, $this->role));

        Queue::assertPushed(SyncToCTS::class, 1);
        Queue::assertPushed(SyncToMoodle::class, 1);
        Queue::assertPushed(SyncToHelpdesk::class, 1);
        Queue::assertPushed(SyncToDiscord::class, 1);
    }

    #[Test]
    public function it_triggers_jobs_when_a_role_is_removed()
    {
        Queue::fake();

        event(new RoleRemoved($this->user, $this->role));

        Queue::assertPushed(SyncToCTS::class, 1);
        Queue::assertPushed(SyncToMoodle::class, 1);
        Queue::assertPushed(SyncToHelpdesk::class, 1);
        Queue::assertPushed(SyncToDiscord::class, 1);
    }

    #[Test]
    public function it_reconciles_helpdesk_staff_for_a_mapped_role()
    {
        Queue::fake();

        RoleHelpdeskStaffSetting::withoutEvents(fn () => RoleHelpdeskStaffSetting::query()->create([
            'role_id' => $this->role->id,
            'helpdesk_department_id' => 10,
            'helpdesk_team_id' => 20,
            'helpdesk_role_id' => 30,
            'priority' => 0,
        ]));

        event(new RoleAssigned($this->user, $this->role));
        event(new RoleRemoved($this->user, $this->role));

        Queue::assertPushed(
            ReconcileHelpdeskStaff::class,
            2,
        );
    }
}
