<?php

declare(strict_types=1);

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\Session;

class TimezoneService
{
    const SESSION_KEY = 'training_timezone';

    const SESSION_BROWSER_KEY = 'training_browser_timezone';

    public function getTimezone(): string
    {
        return Session::get(self::SESSION_KEY, 'UTC');
    }

    public function setTimezone(string $timezone): void
    {
        if (in_array($timezone, timezone_identifiers_list())) {
            Session::put(self::SESSION_KEY, $timezone);
        }
    }

    public function getBrowserTimezone(): ?string
    {
        return Session::get(self::SESSION_BROWSER_KEY);
    }

    public function setBrowserTimezone(string $timezone): void
    {
        if (in_array($timezone, timezone_identifiers_list())) {
            Session::put(self::SESSION_BROWSER_KEY, $timezone);
        }
    }

    public function convertFromUtc(Carbon $date): Carbon
    {
        return $date->copy()->setTimezone($this->getTimezone());
    }

    /**
     * Format a UTC date string in the user's timezone.
     * Date-only strings (no time component) are not converted.
     */
    public function formatDate(string $dateString, string $format = 'D j M Y'): string
    {
        $carbon = Carbon::parse($dateString, 'UTC');

        if (! preg_match('/\d{1,2}:\d{2}/', $dateString)) {
            return $carbon->format($format);
        }

        return $carbon->setTimezone($this->getTimezone())->format($format);
    }

    /**
     * Format a Carbon instance (assumed UTC) in the user's timezone.
     */
    public function formatCarbon(Carbon $date, string $format): string
    {
        return $this->convertFromUtc($date)->format($format);
    }

    /**
     * Convert a local time to UTC (returns H:i). The date provides DST context.
     */
    public function localTimeToUtc(string $dateString, string $timeString): string
    {
        return Carbon::parse("{$dateString} {$timeString}", $this->getTimezone())
            ->utc()
            ->format('H:i');
    }

    /**
     * Convert a UTC time to local (returns H:i). The date provides DST context.
     */
    public function utcTimeToLocal(string $dateString, string $timeString): string
    {
        return Carbon::parse("{$dateString} {$timeString}", 'UTC')
            ->setTimezone($this->getTimezone())
            ->format('H:i');
    }

    /**
     * Get a display label for a timezone. Appends "(not ZULU)" for Europe/London during BST.
     */
    public function getTimezoneLabel(?string $timezone = null, ?string $prefix = null): string
    {
        $timezone = $timezone ?? $this->getTimezone();
        $label = $prefix ?? $timezone;

        if ($timezone === 'Europe/London' && now()->setTimezone('Europe/London')->offsetHours === 1) {
            $label .= ' (not ZULU)';
        }

        return $label;
    }

    /**
     * Build a searchable timezone list for a select dropdown, with detected browser
     * timezone and UTC pinned to the top.
     */
    public function getTimezoneList(): array
    {
        $zones = timezone_identifiers_list();
        $options = array_combine($zones, $zones);

        if (isset($options['Europe/London'])) {
            $options['Europe/London'] = $this->getTimezoneLabel('Europe/London');
        }

        $topZones = [];

        $browserTz = $this->getBrowserTimezone();
        if ($browserTz && isset($options[$browserTz])) {
            $topZones[$browserTz] = 'Detected: '.$this->getTimezoneLabel($browserTz);
            unset($options[$browserTz]);
        }

        if (isset($options['UTC'])) {
            $topZones['UTC'] = 'UTC (Zulu)';
            unset($options['UTC']);
        }

        return $topZones + $options;
    }
}
