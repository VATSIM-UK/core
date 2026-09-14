<?php

namespace Tests\Unit\Support;

use Carbon\CarbonImmutable;
use Illuminate\Support\Carbon;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CarbonDateMacrosTest extends TestCase
{
    #[Test]
    public function to_panel_date_formats_with_the_standard_date_format(): void
    {
        $this->assertSame('12. 09. 2026', Carbon::parse('2026-09-12 14:30:00')->toPanelDate());
    }

    #[Test]
    public function to_panel_time_formats_with_the_standard_time_format(): void
    {
        $this->assertSame('14:30', Carbon::parse('2026-09-12 14:30:00')->toPanelTime());
    }

    #[Test]
    public function to_panel_date_time_formats_with_the_standard_datetime_format(): void
    {
        $this->assertSame('12. 09. 2026 14:30', Carbon::parse('2026-09-12 14:30:00')->toPanelDateTime());
    }

    #[Test]
    public function the_macros_are_available_on_carbon_immutable(): void
    {
        $this->assertSame('12. 09. 2026 14:30', CarbonImmutable::parse('2026-09-12 14:30:00')->toPanelDateTime());
    }

    #[Test]
    public function to_panel_date_with_weekday_formats_with_the_day_of_week(): void
    {
        $this->assertSame('Sat, 12. 09. 2026', Carbon::parse('2026-09-12 14:30:00')->toPanelDateWithWeekday());
    }

    #[Test]
    public function the_macros_are_null_safe_via_the_nullsafe_operator(): void
    {
        $nullable = null;

        $this->assertNull($nullable?->toPanelDateTime());
    }
}
