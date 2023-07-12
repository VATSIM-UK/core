<?php

namespace Tests\Feature\AdminOld;

use App\Jobs\UpdateMember;
use App\Models\Mship\Account\Ban;
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
            ->get(route('adm.mship.account.details', '1300006'))
            ->assertStatus(404);
    }

    /** @test */
    public function testCanViewBansList()
    {
        // Add a ban
        $ban = Ban::factory()->create([
            'account_id' => $this->user->id,
        ]);

        $this->actingAs($this->privacc)
            ->get(route('adm.mship.ban.index'))
            ->assertSuccessful()
            ->assertSeeTextInOrder([
                $ban->account->real_name,
                $ban->banner->real_name,
                $ban->created_at->format('dS M Y'),
                $ban->period_start->format('dS M Y'),
                $ban->period_finish->format('dS M Y'),
                'Local',
                'Active',
            ]);
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
            ->assertSessionHas('success', 'User queued to refresh central membership details & sync to external services!');

        Queue::assertPushed(UpdateMember::class);
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
