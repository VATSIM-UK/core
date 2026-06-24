<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Services\TimezoneService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Session;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class DisplayDateHelperTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Session::forget(TimezoneService::SESSION_KEY);
        Session::forget(TimezoneService::SESSION_BROWSER_KEY);
    }

    #[Test]
    public function display_date_defaults_to_utc_when_no_timezone_set(): void
    {
        $result = display_date('2025-06-25 12:00', 'H:i');
        $this->assertEquals('12:00', $result);
    }

    #[Test]
    public function display_date_converts_datetime_in_selected_timezone(): void
    {
        app(TimezoneService::class)->setTimezone('America/New_York');

        $result = display_date('2025-06-25 12:00', 'd/m/Y H:i');
        $this->assertEquals('25/06/2025 08:00', $result);
    }

    #[Test]
    public function display_date_preserves_date_only_strings(): void
    {
        app(TimezoneService::class)->setTimezone('America/New_York');

        // Date-only (no time component) → not shifted
        $result = display_date('2025-06-25', 'd/m/Y');
        $this->assertEquals('25/06/2025', $result);
    }

    #[Test]
    public function display_date_accepts_carbon_instances(): void
    {
        app(TimezoneService::class)->setTimezone('Asia/Tokyo');

        $carbon = Carbon::parse('2025-06-25 03:00:00', 'UTC');
        $result = display_date($carbon, 'd/m/Y H:i');

        // UTC 03:00 → JST 12:00
        $this->assertEquals('25/06/2025 12:00', $result);
    }

    #[Test]
    public function display_datetime_formats_with_default_datetime_format(): void
    {
        app(TimezoneService::class)->setTimezone('Europe/London');

        $result = display_datetime('2025-06-25 13:00');
        $this->assertStringContainsString('Jun', $result);
        $this->assertStringContainsString('14:00', $result); // BST (UTC+1)
    }

    #[Test]
    public function display_datetime_accepts_custom_format(): void
    {
        app(TimezoneService::class)->setTimezone('America/Chicago');

        $result = display_datetime('2025-06-25 12:00', 'H:i');
        $this->assertEquals('07:00', $result); // CDT (UTC-5)
    }
}
