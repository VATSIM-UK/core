<?php

namespace Tests\Feature\Filament;

use App\Support\DateFormat;
use Filament\Forms\Components\DateTimePicker;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class DateFormatDefaultsTest extends TestCase
{
    #[Test]
    public function datetime_pickers_use_the_standard_display_formats_by_default(): void
    {
        $picker = DateTimePicker::make('test')->configure();

        $this->assertSame(DateFormat::DATE, $picker->getDefaultDateDisplayFormat());
        $this->assertSame(DateFormat::DATETIME, $picker->getDefaultDateTimeDisplayFormat());
        $this->assertSame(DateFormat::TIME, $picker->getDefaultTimeDisplayFormat());

        $this->assertSame(DateFormat::DATETIME, $picker->getDisplayFormat());
        $this->assertSame(DateFormat::DATETIME, $picker->getDefaultDateTimeWithSecondsDisplayFormat());
        $this->assertSame(DateFormat::TIME, $picker->getDefaultTimeWithSecondsDisplayFormat());
    }
}
