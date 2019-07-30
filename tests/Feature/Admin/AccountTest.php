<?php

namespace Tests\Feature\Admin;

use App\Jobs\Mship\SyncToCTS;
use App\Jobs\Mship\SyncToHelpdesk;
use App\Jobs\Mship\SyncToMoodle;
use App\Models\Mship\Account;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class AccountTest extends TestCase
{
    use DatabaseTransactions;

    /** @test */
    public function testGetA404WhenTryingToViewNonExistentUser()
    {
        $this->actingAs($this->privacc)
            ->get(route('adm.mship.account.details', '12345'))
            ->assertNotFound();
    }

    /** @test */
    public function testCanViewBansList()
    {
        $this->actingAs($this->privacc)
            ->get(route('adm.mship.ban.index'))
            ->assertSuccessful();
    }

    /** @test */
    public function testCanQueueUserToSync()
    {
        // Remove time lockout cache & fake queue
        Cache::flush();
        Queue::fake();

        $this->actingAs($this->privacc)
            ->get(route('adm.mship.account.sync', $this->user->id))
            ->assertRedirect()
            ->assertSessionHas('success', 'User queued to sync to external services!');

        Queue::assertPushed(SyncToCTS::class);
        Queue::assertPushed(SyncToMoodle::class);
        Queue::assertPushed(SyncToHelpdesk::class);
        //Queue::assertPushed(SyncToForums::class);
    }

    /** @test */
    public function testCanImpersonateUser()
    {
        $this->assertNull(Auth::user());

        $this->actingAs($this->privacc)
            ->post(route('adm.mship.account.impersonate', $this->user->id), ['reason' => 'Lorem Ipsum Dorum'])
            ->assertRedirect()
            ->assertSessionHas('success', 'You are now impersonating this user - your reason has been logged. Be good!');

        $this->assertEquals($this->user->id, Auth::user()->id);
    }
}
