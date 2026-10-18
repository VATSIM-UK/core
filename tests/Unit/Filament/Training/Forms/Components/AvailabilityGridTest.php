<?php

declare(strict_types=1);

namespace Tests\Unit\Filament\Training\Forms\Components;

use App\Filament\Training\Forms\Components\AvailabilityGrid;
use Carbon\Carbon;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AvailabilityGridTest extends TestCase
{
    #[Test]
    public function it_defaults_the_last_allowed_date_to_the_cap_not_today(): void
    {
        $this->travelTo(Carbon::create(2026, 5, 13, 12, 0, 0, 'UTC'));

        $grid = AvailabilityGrid::make('availability');

        $this->assertSame('2026-06-13', $grid->getLastAllowedDate()->toDateString());
    }
}
