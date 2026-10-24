<?php

namespace App\Services;

use App\Support\Calendar\IcsEvent;
use Carbon\Carbon;

class IcsService
{
    /**
     * Generate an ICS calendar attachment string.
     *
     * @param  string  $uid  Unique identifier for the event (e.g. 'exam-123@vatsim.uk')
     * @param  string  $summary  Event title/summary
     * @param  string  $description  Event description (plain text)
     * @param  Carbon  $start  Event start date/time (will be treated as UTC)
     * @param  Carbon  $end  Event end date/time (will be treated as UTC)
     * @param  string  $location  Optional location (e.g. ATC position)
     * @param  int  $sequence  Event revision; increase it to replace a previously sent copy
     * @return string Raw ICS content
     */
    public static function generate(
        string $uid,
        string $summary,
        string $description,
        Carbon $start,
        Carbon $end,
        string $location = '',
        int $sequence = 0,
    ): string {
        $lines = [
            'BEGIN:VCALENDAR',
            'VERSION:2.0',
            'PRODID:-//VATSIM UK//Core//EN',
            'CALSCALE:GREGORIAN',
            'METHOD:PUBLISH',
            ...self::eventLines(
                new IcsEvent(
                    uid: $uid,
                    summary: $summary,
                    startsAt: $start,
                    endsAt: $end,
                    description: $description,
                    location: $location,
                    sequence: $sequence,
                ),
                self::formatUtc(Carbon::now('UTC')),
                'VATSIM UK Training Department',
            ),
            'END:VCALENDAR',
        ];

        return self::foldLines(implode("\r\n", $lines));
    }

    /**
     * Generate an iCal feed containing multiple events.
     *
     * @param  iterable<int, IcsEvent>  $events
     * @return string Raw ICS content
     */
    public static function calendar(string $name, iterable $events): string
    {
        $dtStamp = self::formatUtc(Carbon::now('UTC'));

        $lines = [
            'BEGIN:VCALENDAR',
            'VERSION:2.0',
            'PRODID:-//VATSIM UK//Core//EN',
            'CALSCALE:GREGORIAN',
            'METHOD:PUBLISH',
            'X-WR-CALNAME:'.self::escapeText($name),
            'X-WR-TIMEZONE:UTC',
            'X-PUBLISHED-TTL:PT1H',
            'REFRESH-INTERVAL;VALUE=DURATION:PT1H',
        ];

        foreach ($events as $event) {
            $lines = [...$lines, ...self::eventLines($event, $dtStamp, 'VATSIM UK')];
        }

        $lines[] = 'END:VCALENDAR';

        return self::foldLines(implode("\r\n", $lines));
    }

    /**
     * @return array<int, string>
     */
    private static function eventLines(IcsEvent $event, string $dtStamp, string $organizerName): array
    {
        $lines = [
            'BEGIN:VEVENT',
            "UID:{$event->uid}",
            "DTSTAMP:{$dtStamp}",
            "SEQUENCE:{$event->sequence}",
            'DTSTART:'.self::formatUtc($event->startsAt),
            'DTEND:'.self::formatUtc($event->endsAt),
            'SUMMARY:'.self::escapeText($event->summary),
            'DESCRIPTION:'.self::escapeText($event->description),
        ];

        if (! empty($event->location)) {
            $lines[] = 'LOCATION:'.self::escapeText($event->location);
        }

        $lines[] = "ORGANIZER;CN={$organizerName}:mailto:no-reply@vatsim.uk";
        $lines[] = 'END:VEVENT';

        return $lines;
    }

    /**
     * Format a Carbon instance as a UTC ICS date-time string (e.g. 20260101T120000Z).
     */
    private static function formatUtc(Carbon $date): string
    {
        return $date->copy()->setTimezone('UTC')->format('Ymd\THis\Z');
    }

    /**
     * Escape special characters in ICS text values
     */
    private static function escapeText(string $text): string
    {
        $replacements = [
            '\\' => '\\\\',
            ';' => '\\;',
            ',' => '\\,',
            "\n" => '\\n',
            "\r" => '',
        ];

        return str_replace(array_keys($replacements), array_values($replacements), $text);
    }

    /**
     * Fold lines to 75 octets, backing off to a character boundary so a
     * multi-byte character is never split across two physical lines.
     */
    private static function foldLines(string $content): string
    {
        $folded = [];

        foreach (explode("\r\n", $content) as $line) {
            $continuation = false;

            while (strlen($line) > ($continuation ? 74 : 75)) {
                $chunk = mb_strcut($line, 0, $continuation ? 74 : 75, 'UTF-8');

                $folded[] = ($continuation ? ' ' : '').$chunk;
                $line = substr($line, strlen($chunk));
                $continuation = true;
            }

            $folded[] = ($continuation ? ' ' : '').$line;
        }

        return implode("\r\n", $folded)."\r\n";
    }
}
