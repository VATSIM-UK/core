<?php

declare(strict_types=1);

namespace Tests\Feature\TrainingPanel\MyTraining\Widgets;

use App\Filament\Training\Pages\MyTraining\Widgets\MyAvailabilityStats;
use App\Models\Cts\Member;
use App\Models\Mship\Account;
use App\Models\Training\Availability;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class MyAvailabilityStatsTest extends TestCase
{
    use DatabaseTransactions;

    protected Account $user;

    protected Member $member;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = Account::factory()->create();
        $this->member = Member::factory()->create(['cid' => $this->user->id]);

        Carbon::setTestNow(Carbon::create(2026, 5, 10, 12, 0, 0));
    }

    #[Test]
    public function it_shows_zero_stats_when_no_availability_exists(): void
    {
        Livewire::actingAs($this->user)
            ->test(MyAvailabilityStats::class)
            ->assertSee('Upcoming Slots')
            ->assertSee('0')
            ->assertSee('Total Availability')
            ->assertSee('0 Hours');
    }

    #[Test]
    public function it_calculates_stats_for_future_slots_correctly(): void
    {
        Availability::factory()->forAccount($this->user->id)->onDate('2026-05-11')->timeRange('18:00:00', '20:00:00')->create();

        Availability::factory()->forAccount($this->user->id)->onDate('2026-05-12')->timeRange('10:00:00', '11:30:00')->create();

        Livewire::actingAs($this->user)
            ->test(MyAvailabilityStats::class)
            ->assertSee('Upcoming Slots')
            ->assertSee('2')
            ->assertSee('Total Availability')
            ->assertSee('3.5 Hours');
    }

    #[Test]
    public function it_includes_slots_happening_later_today_but_excludes_past_slots(): void
    {
        Availability::factory()->forAccount($this->user->id)->onDate('2026-05-10')->timeRange('08:00:00', '10:00:00')->create();

        Availability::factory()->forAccount($this->user->id)->onDate('2026-05-10')->timeRange('14:00:00', '16:00:00')->create();

        Livewire::actingAs($this->user)
            ->test(MyAvailabilityStats::class)
            ->assertSee('Upcoming Slots')
            ->assertSee('1')
            ->assertSee('Total Availability')
            ->assertSee('2 Hours');
    }

    #[Test]
    public function it_ignores_availability_for_other_members(): void
    {
        $otherAccount = Account::factory()->createQuietly();
        $otherMember = Member::factory()->forAccount($otherAccount)->create();

        Availability::factory()->forAccount($otherAccount->id)->onDate('2026-06-01')->timeRange('12:00:00', '15:00:00')->create();

        Livewire::actingAs($this->user)
            ->test(MyAvailabilityStats::class)
            ->assertSee('0 Hours');
    }
}
