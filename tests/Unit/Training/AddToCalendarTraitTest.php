<?php

declare(strict_types=1);

namespace Tests\Unit\Training;

use App\Filament\Training\Pages\Concerns\AddToCalendar;
use Carbon\Carbon;
use Filament\Actions\ActionGroup;
use PHPUnit\Framework\Attributes\Test;
use Spatie\CalendarLinks\Link;
use Tests\TestCase;

class AddToCalendarTraitTest extends TestCase
{
    #[Test]
    public function it_generates_google_calendar_url(): void
    {
        $instance = $this->createTraitInstance();

        $url = $instance->publicBuildCalendarLink($this->createTestRecord(), 'google');

        $this->assertStringStartsWith('https://calendar.google.com', $url);
    }

    #[Test]
    public function it_generates_yahoo_calendar_url(): void
    {
        $instance = $this->createTraitInstance();

        $url = $instance->publicBuildCalendarLink($this->createTestRecord(), 'yahoo');

        $this->assertStringStartsWith('https://calendar.yahoo.com', $url);
    }

    #[Test]
    public function it_generates_web_outlook_url(): void
    {
        $instance = $this->createTraitInstance();

        $url = $instance->publicBuildCalendarLink($this->createTestRecord(), 'webOutlook');

        $this->assertStringStartsWith('https://outlook.live.com', $url);
    }

    #[Test]
    public function it_generates_office_calendar_url(): void
    {
        $instance = $this->createTraitInstance();

        $url = $instance->publicBuildCalendarLink($this->createTestRecord(), 'webOffice');

        $this->assertStringContainsString('outlook', $url);
    }

    #[Test]
    public function it_returns_action_group_with_correct_structure(): void
    {
        $instance = $this->createTraitInstance();

        $actionGroup = $instance->publicGetCalendarActionGroup();

        $this->assertInstanceOf(ActionGroup::class, $actionGroup);
        $this->assertSame('Add to Calendar', $actionGroup->getLabel());
        $this->assertSame('heroicon-m-calendar-days', $actionGroup->getIcon());
    }

    /** Create a test double that uses the trait */
    private function createTraitInstance(): object
    {
        return new class
        {
            use AddToCalendar;

            public function publicBuildCalendarLink(mixed $record, string $provider): string
            {
                return $this->buildCalendarLink($record, $provider);
            }

            public function publicGetCalendarActionGroup(): ActionGroup
            {
                return $this->getCalendarActionGroup();
            }

            protected function buildCalendarLinkObject(mixed $record): Link
            {
                \assert($record instanceof \stdClass);

                return Link::create($record->title, $record->start, $record->end)
                    ->description($record->description)
                    ->address($record->location);
            }

            protected function getCalendarIcsFilename(mixed $record): string
            {
                \assert($record instanceof \stdClass);

                return $record->icsFilename;
            }
        };
    }

    /** Create a standard test record */
    private function createTestRecord(): \stdClass
    {
        return (object) [
            'title' => 'Test Event',
            'start' => Carbon::parse('2026-06-20 10:00:00', 'UTC'),
            'end' => Carbon::parse('2026-06-20 12:00:00', 'UTC'),
            'description' => 'Test description',
            'location' => 'EGLL_TWR',
            'icsFilename' => 'test-event',
        ];
    }
}
