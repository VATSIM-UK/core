<?php

namespace Tests\Unit\Support;

use App\Support\DateFormat;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class DateFormatTest extends TestCase
{
    #[Test]
    public function it_defines_the_expected_date_time_and_datetime_formats(): void
    {
        $this->assertSame('d. m. Y', DateFormat::DATE);
        $this->assertSame('H:i', DateFormat::TIME);
        $this->assertSame('d. m. Y H:i', DateFormat::DATETIME);
    }
}
