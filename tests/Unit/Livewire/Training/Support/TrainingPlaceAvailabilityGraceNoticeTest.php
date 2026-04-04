<?php

declare(strict_types=1);

namespace Tests\Unit\Livewire\Training\Support;

use App\Livewire\Training\Support\TrainingPlaceAvailabilityGraceNotice;
use App\Models\Training\TrainingPlace\TrainingPlace;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class TrainingPlaceAvailabilityGraceNoticeTest extends TestCase
{
    use DatabaseTransactions;

    #[Test]
    public function it_returns_notice_while_within_grace_period(): void
    {
        Carbon::setTestNow('2026-04-01 12:00:00');

        $place = TrainingPlace::withoutEvents(fn () => TrainingPlace::factory()->create([
            'created_at' => Carbon::parse('2026-04-01 00:00:00'),
        ]));

        $notice = TrainingPlaceAvailabilityGraceNotice::message($place);

        $this->assertNotNull($notice);
        $this->assertStringContainsString('48', $notice);
        $this->assertStringContainsString('03/04/2026, 00:00', $notice);

        Carbon::setTestNow();
    }

    #[Test]
    public function it_returns_null_after_grace_period(): void
    {
        Carbon::setTestNow('2026-04-03 00:00:00');

        $place = TrainingPlace::withoutEvents(fn () => TrainingPlace::factory()->create([
            'created_at' => Carbon::parse('2026-04-01 00:00:00'),
        ]));

        $this->assertNull(TrainingPlaceAvailabilityGraceNotice::message($place));

        Carbon::setTestNow();
    }

    #[Test]
    public function it_returns_null_for_trashed_place_when_only_active_requested(): void
    {
        Carbon::setTestNow('2026-04-01 12:00:00');

        $place = TrainingPlace::withoutEvents(fn () => TrainingPlace::factory()->create([
            'created_at' => Carbon::parse('2026-04-01 00:00:00'),
        ]));
        $place->deleteQuietly();

        $this->assertNull(TrainingPlaceAvailabilityGraceNotice::message($place, onlyForActivePlace: true));
        $this->assertNotNull(TrainingPlaceAvailabilityGraceNotice::message($place, onlyForActivePlace: false));

        Carbon::setTestNow();
    }
}
