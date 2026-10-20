<?php

declare(strict_types=1);

namespace Tests\Feature\Bookings;

use App\Livewire\Bookings\CalendarSubscription;
use App\Models\Mship\Account;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CalendarSubscriptionTest extends TestCase
{
    use DatabaseTransactions;

    #[Test]
    public function it_generates_a_token_on_first_view(): void
    {
        $account = Account::factory()->create();

        $this->assertNull($account->calendar_feed_token);

        Livewire::actingAs($account)->test(CalendarSubscription::class);

        $this->assertNotNull($account->fresh()->calendar_feed_token);
    }

    #[Test]
    public function it_shows_the_feed_url_to_the_signed_in_member(): void
    {
        $account = Account::factory()->create();

        Livewire::actingAs($account)
            ->test(CalendarSubscription::class)
            ->assertSee('Calendar subscription')
            ->assertSee(route('api.bookings.calendar.feed', [
                'token' => $account->fresh()->calendar_feed_token,
            ]));
    }

    #[Test]
    public function resetting_the_token_invalidates_the_previous_url(): void
    {
        $account = Account::factory()->create();
        $oldToken = $account->ensureCalendarFeedToken();

        Livewire::actingAs($account)
            ->test(CalendarSubscription::class)
            ->call('regenerate')
            ->assertSee(route('api.bookings.calendar.feed', [
                'token' => $account->fresh()->calendar_feed_token,
            ]));

        $this->assertNotSame($oldToken, $account->fresh()->calendar_feed_token);

        $this->get(route('api.bookings.calendar.feed', ['token' => $oldToken]))
            ->assertNotFound();
    }

    #[Test]
    public function guests_are_not_given_a_feed_url(): void
    {
        Livewire::test(CalendarSubscription::class)
            ->assertSee('Sign in to subscribe to your bookings.')
            ->assertDontSee('Copy link');
    }
}
