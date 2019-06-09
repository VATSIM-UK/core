<?php

namespace Tests\Feature\Adm;

use App\Jobs\Mship\SyncToCTS;
use App\Jobs\Mship\SyncToHelpdesk;
use App\Jobs\Mship\SyncToMoodle;
use App\Models\Mship\Account;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class AccountTest extends TestCase
{
    use DatabaseTransactions;

    protected $admin;
    protected $user;

    public function setUp(): void
    {
        parent::setUp();

        $admin = factory(Account::class)->create();
        $this->user = factory(Account::class)->create();
        $admin->assignRole('privacc');
        $this->admin = $admin->fresh();
    }

    /** @test **/
    public function testGetA404WhenTryingToViewNonExistentUser()
    {
        $this->actingAs($this->admin)
                ->get(route('adm.mship.account.details', '12345'))
                ->assertNotFound();
    }

    /** @test * */
    public function testCanQueueUserToSync()
    {
        Cache::flush(); // Remove time lockout cache
        Queue::fake();

        $this->actingAs($this->admin)
            ->get(route('adm.mship.account.sync', $this->user->id))
            ->assertRedirect()
            ->assertSessionHas('success', 'User queued to sync to external services!');

        Queue::assertPushed(SyncToCTS::class);
        Queue::assertPushed(SyncToMoodle::class);
        Queue::assertPushed(SyncToHelpdesk::class);
        //Queue::assertPushed(SyncToForums::class);
    }
}
