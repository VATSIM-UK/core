<?php

declare(strict_types=1);

namespace Tests\Unit\Training\TrainingPlace;

use App\Models\Training\TrainingPlace\TrainingPlace;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class TrainingPlaceAvailabilityGracePeriodTest extends TestCase
{
    use DatabaseTransactions;

    #[Test]
    public function it_reports_within_grace_period_before_cutoff(): void
    {
        Carbon::setTestNow('2026-04-01 12:00:00');

        $place = TrainingPlace::withoutEvents(fn () => TrainingPlace::factory()->create([
            'created_at' => Carbon::parse('2026-04-01 00:00:00'),
        ]));

        $this->assertTrue($place->isWithinAvailabilityCheckGracePeriod());
        $this->assertSame(
            '2026-04-03 00:00:00',
            $place->availabilityCheckGracePeriodEndsAt()->format('Y-m-d H:i:s')
        );

        Carbon::setTestNow();
    }

    #[Test]
    public function it_reports_outside_grace_period_from_cutoff_instant_onwards(): void
    {
        Carbon::setTestNow('2026-04-03 00:00:00');

        $place = TrainingPlace::withoutEvents(fn () => TrainingPlace::factory()->create([
            'created_at' => Carbon::parse('2026-04-01 00:00:00'),
        ]));

        $this->assertFalse($place->isWithinAvailabilityCheckGracePeriod());

        Carbon::setTestNow();
    }
}
