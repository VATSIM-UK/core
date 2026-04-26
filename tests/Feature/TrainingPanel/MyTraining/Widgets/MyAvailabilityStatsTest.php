<?php

declare(strict_types=1);

namespace Tests\Feature\TrainingPanel\MyTraining\Widgets;

use App\Filament\Training\Pages\MyTraining\Widgets\MyAvailabilityStats;
use App\Models\Cts\Availability;
use App\Models\Cts\Member;
use App\Models\Mship\Account;
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
        Availability::factory()->create([
            'student_id' => $this->member->id,
            'type' => 'S',
            'date' => '2026-05-11',
            'from' => '18:00:00',
            'to' => '20:00:00',
        ]);

        Availability::factory()->create([
            'student_id' => $this->member->id,
            'type' => 'S',
            'date' => '2026-05-12',
            'from' => '10:00:00',
            'to' => '11:30:00',
        ]);

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
        Availability::factory()->create([
            'student_id' => $this->member->id,
            'type' => 'S',
            'date' => '2026-05-10',
            'from' => '08:00:00',
            'to' => '10:00:00',
        ]);

        Availability::factory()->create([
            'student_id' => $this->member->id,
            'type' => 'S',
            'date' => '2026-05-10',
            'from' => '14:00:00',
            'to' => '16:00:00',
        ]);

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
        $otherMember = Member::factory()->create();

        Availability::factory()->create([
            'student_id' => $otherMember->id,
            'type' => 'S',
            'date' => '2026-06-01',
            'from' => '12:00:00',
            'to' => '15:00:00',
        ]);

        Livewire::actingAs($this->user)
            ->test(MyAvailabilityStats::class)
            ->assertSee('0 Hours');
    }
}
