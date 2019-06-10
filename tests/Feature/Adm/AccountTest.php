<?php

namespace Tests\Feature\Adm;

use App\Jobs\Mship\SyncToCTS;
use App\Jobs\Mship\SyncToHelpdesk;
use App\Jobs\Mship\SyncToMoodle;
use Illuminate\Foundation\Testing\DatabaseTransactions;
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
}
