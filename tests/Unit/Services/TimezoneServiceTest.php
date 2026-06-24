<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Services\TimezoneService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Session;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class TimezoneServiceTest extends TestCase
{
    private TimezoneService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = app(TimezoneService::class);

        // Start with a clean session for each test
        Session::forget(TimezoneService::SESSION_KEY);
        Session::forget(TimezoneService::SESSION_BROWSER_KEY);
    }

    #[Test]
    public function it_defaults_to_utc(): void
    {
        $this->assertEquals('UTC', $this->service->getTimezone());
    }

    #[Test]
    public function it_stores_and_retrieves_a_timezone(): void
    {
        $this->service->setTimezone('Europe/London');
        $this->assertEquals('Europe/London', $this->service->getTimezone());
    }

    #[Test]
    public function it_ignores_invalid_timezone_identifiers(): void
    {
        $this->service->setTimezone('Not/A/Real/Timezone');
        $this->assertEquals('UTC', $this->service->getTimezone());
    }

    #[Test]
    public function it_stores_and_retrieves_browser_timezone(): void
    {
        $this->assertNull($this->service->getBrowserTimezone());

        $this->service->setBrowserTimezone('America/New_York');
        $this->assertEquals('America/New_York', $this->service->getBrowserTimezone());
    }

    #[Test]
    public function it_ignores_invalid_browser_timezone(): void
    {
        $this->service->setBrowserTimezone('Bogus/Place');
        $this->assertNull($this->service->getBrowserTimezone());
    }

    #[Test]
    public function it_returns_timezone_as_label_when_no_prefix_given(): void
    {
        $this->assertEquals('America/Chicago', $this->service->getTimezoneLabel('America/Chicago'));
    }

    #[Test]
    public function it_uses_prefix_when_provided(): void
    {
        $this->assertEquals('Detected: America/Chicago', $this->service->getTimezoneLabel('America/Chicago', 'Detected: '));
    }

    #[Test]
    public function it_pins_utc_to_the_top_of_the_list(): void
    {
        $list = $this->service->getTimezoneList();

        $keys = array_keys($list);
        $this->assertEquals('UTC', $keys[0]);
        $this->assertEquals('UTC (Zulu)', $list['UTC']);
    }

    #[Test]
    public function it_includes_detected_browser_timezone_at_the_top(): void
    {
        $this->service->setBrowserTimezone('Asia/Tokyo');

        $list = $this->service->getTimezoneList();

        $keys = array_keys($list);
        $this->assertEquals('Asia/Tokyo', $keys[0]);
        $this->assertStringContainsString('Detected:', $list['Asia/Tokyo']);
        $this->assertEquals('UTC', $keys[1]);
    }

    #[Test]
    public function it_does_not_duplicate_browser_timezone_in_full_list(): void
    {
        $this->service->setBrowserTimezone('Asia/Tokyo');

        $list = $this->service->getTimezoneList();

        // Asia/Tokyo should only appear once
        $tzCount = 0;
        foreach ($list as $key => $label) {
            if ($key === 'Asia/Tokyo') {
                $tzCount++;
            }
        }
        $this->assertEquals(1, $tzCount);
    }

    #[Test]
    public function it_does_not_convert_date_only_strings(): void
    {
        $this->service->setTimezone('America/New_York');

        // "2025-06-25" has no time component - should not shift
        $result = $this->service->formatDate('2025-06-25', 'd/m/Y');
        $this->assertEquals('25/06/2025', $result);
    }

    #[Test]
    public function it_converts_datetime_strings(): void
    {
        $this->service->setTimezone('America/New_York');

        // "2025-06-25 12:00" has a time component - should convert to EDT (UTC-4 - 08:00)
        $result = $this->service->formatDate('2025-06-25 12:00', 'd/m/Y H:i');
        $this->assertEquals('25/06/2025 08:00', $result);
    }

    #[Test]
    public function it_preserves_date_only_in_uncommon_formats(): void
    {
        $this->service->setTimezone('America/New_York');

        // e.g. "2025/06/25" with no time component - not converted
        $result = $this->service->formatDate('2025/06/25', 'Y-m-d');
        $this->assertEquals('2025-06-25', $result);
    }

    #[Test]
    public function it_always_converts_carbon_instances(): void
    {
        $this->service->setTimezone('America/New_York');

        $date = Carbon::parse('2025-12-25 00:00:00', 'UTC');
        $result = $this->service->formatCarbon($date, 'd/m/Y H:i');

        // Midnight UTC - previous day 19:00 EST
        $this->assertEquals('24/12/2025 19:00', $result);
    }

    #[Test]
    public function it_converts_carbon_from_utc(): void
    {
        $this->service->setTimezone('Asia/Tokyo');

        $utc = Carbon::parse('2025-06-25 03:00:00', 'UTC');
        $local = $this->service->convertFromUtc($utc);

        $this->assertEquals('Asia/Tokyo', $local->timezoneName);
        $this->assertEquals('12:00', $local->format('H:i')); // UTC+9
    }

    #[Test]
    public function it_converts_local_time_to_utc_and_back(): void
    {
        $this->service->setTimezone('America/Chicago');

        // Local 14:00 on June 25 in Chicago (CDT, UTC-5) - 19:00 UTC
        $utc = $this->service->localTimeToUtc('2025-06-25', '14:00');
        $this->assertEquals('19:00', $utc);

        // And back: 19:00 UTC - 14:00 CDT
        $local = $this->service->utcTimeToLocal('2025-06-25', '19:00');
        $this->assertEquals('14:00', $local);
    }

    #[Test]
    public function it_handles_dst_boundaries_with_date_context(): void
    {
        $this->service->setTimezone('Europe/London');

        // June (BST, UTC+1) - 14:00 local = 13:00 UTC
        $utcJune = $this->service->localTimeToUtc('2025-06-15', '14:00');
        $this->assertEquals('13:00', $utcJune);

        // December (GMT, UTC+0) - 14:00 local = 14:00 UTC
        $utcDec = $this->service->localTimeToUtc('2025-12-15', '14:00');
        $this->assertEquals('14:00', $utcDec);

        // Different UTC offsets for same local time, different DST periods
        $this->assertNotEquals($utcJune, $utcDec);
    }

    #[Test]
    public function it_wraps_midnight_when_converting_to_utc(): void
    {
        $this->service->setTimezone('Pacific/Auckland'); // UTC+12 (or UTC+13 in summer)

        // Local 02:00 - previous day 14:00-ish UTC
        $utc = $this->service->localTimeToUtc('2025-06-25', '02:00');
        $this->assertEquals('14:00', $utc);
        // Note: the H:i return loses the day-wrap but the method contract
        // is that callers must combine the date before using the time.
    }

    #[Test]
    public function it_is_consistent_after_multiple_timezone_changes(): void
    {
        $this->service->setTimezone('Asia/Tokyo');
        $tokyo = $this->service->getTimezone();
        $this->assertEquals('Asia/Tokyo', $tokyo);

        $this->service->setTimezone('Europe/London');
        $london = $this->service->getTimezone();
        $this->assertEquals('Europe/London', $london);

        $this->assertNotEquals($tokyo, $london);
    }
}
