<?php

declare(strict_types=1);

namespace Tests\Feature\Bookings;

use App\Livewire\Bookings\Calendar;
use App\Livewire\Bookings\CalendarSubscription;
use App\Models\Mship\Account;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CalendarSubscribeLinkTest extends TestCase
{
    use DatabaseTransactions;

    #[Test]
    public function it_redirects_to_the_subscription_card_on_the_dashboard(): void
    {
        $this->get(route('site.bookings.calendar.subscribe'))
            ->assertRedirect(route('mship.manage.dashboard').'#calendar-subscription');
    }

    #[Test]
    public function the_subscription_card_carries_the_anchor(): void
    {
        Livewire::actingAs(Account::factory()->create())
            ->test(CalendarSubscription::class)
            ->assertSeeHtml('id="calendar-subscription"');
    }

    #[Test]
    public function the_calendar_links_signed_in_members_to_the_subscription(): void
    {
        Livewire::actingAs(Account::factory()->create())
            ->test(Calendar::class)
            ->assertSeeHtml(route('site.bookings.calendar.subscribe'));
    }

    #[Test]
    public function the_calendar_does_not_link_guests_to_the_subscription(): void
    {
        Livewire::test(Calendar::class)
            ->assertDontSeeHtml(route('site.bookings.calendar.subscribe'));
    }
}
