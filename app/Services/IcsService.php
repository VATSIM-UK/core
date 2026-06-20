<?php

namespace App\Services;

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
     * @return string Raw ICS content
     */
    public static function generate(
        string $uid,
        string $summary,
        string $description,
        Carbon $start,
        Carbon $end,
        string $location = ''
    ): string {
        $dtStart = self::formatUtc($start);
        $dtEnd = self::formatUtc($end);
        $dtStamp = self::formatUtc(Carbon::now('UTC'));

        $escapedSummary = self::escapeText($summary);
        $escapedDescription = self::escapeText($description);

        $lines = [
            'BEGIN:VCALENDAR',
            'VERSION:2.0',
            'PRODID:-//VATSIM UK//Core//EN',
            'CALSCALE:GREGORIAN',
            'METHOD:PUBLISH',
            'BEGIN:VEVENT',
            "UID:{$uid}",
            "DTSTAMP:{$dtStamp}",
            "DTSTART:{$dtStart}",
            "DTEND:{$dtEnd}",
            "SUMMARY:{$escapedSummary}",
            "DESCRIPTION:{$escapedDescription}",
        ];

        if (! empty($location)) {
            $escapedLocation = self::escapeText($location);
            $lines[] = "LOCATION:{$escapedLocation}";
        }

        $lines[] = 'ORGANIZER;CN=VATSIM UK Training Department:mailto:training@vatsim.uk';
        $lines[] = 'END:VEVENT';
        $lines[] = 'END:VCALENDAR';

        $icsContent = implode("\r\n", $lines);

        return self::foldLines($icsContent);
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
     * Fold lines longer than 75 octets
     */
    private static function foldLines(string $content): string
    {
        $lines = explode("\r\n", $content);
        $foldedLines = [];

        foreach ($lines as $line) {
            if (strlen($line) > 75) {
                $foldedLines[] = rtrim(chunk_split($line, 74, "\r\n "));
            } else {
                $foldedLines[] = $line;
            }
        }

        return implode("\r\n", $foldedLines)."\r\n";
    }
}
