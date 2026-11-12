<?php

namespace Tests\Feature\Discord;

use App\Events\Discord\DiscordUnlinked;
use App\Models\Mship\Account;
use Illuminate\Support\Facades\Event;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class DiscordUnlinkTest extends TestCase
{
    #[Test]
    public function unlinking_requires_a_state_changing_request()
    {
        Event::fake([DiscordUnlinked::class]);

        $this->actingAs(Account::factory()->create(['discord_id' => '123456789']))
            ->get(route('discord.destroy'))
            ->assertRedirect(route('landing'));

        Event::assertNotDispatched(DiscordUnlinked::class);
    }

    #[Test]
    public function a_member_can_unlink_their_discord_account()
    {
        Event::fake([DiscordUnlinked::class]);

        $account = Account::factory()->create(['discord_id' => '123456789']);

        $this->from(route('mship.manage.dashboard'))->actingAs($account)
            ->delete(route('discord.destroy'))
            ->assertRedirect(route('mship.manage.dashboard'));

        Event::assertDispatched(DiscordUnlinked::class);
    }
}
