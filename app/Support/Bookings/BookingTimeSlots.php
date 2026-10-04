<?php

declare(strict_types=1);

namespace App\Support\Bookings;

/**
 * Quarter-hour booking slots used by the bookings calendar create modal.
 *
 * Mirrors the Alpine helpers in resources/views/livewire/bookings/_create-modal.blade.php
 * so the "end must be after start" filtering can be unit-tested in PHP.
 */
final class BookingTimeSlots
{
    /**
     * @return list<string> Times as H:i on 15-minute boundaries from 00:00 to 23:45.
     */
    public static function all(): array
    {
        $slots = [];

        for ($hour = 0; $hour < 24; $hour++) {
            foreach ([0, 15, 30, 45] as $minute) {
                $slots[] = sprintf('%02d:%02d', $hour, $minute);
            }
        }

        return $slots;
    }

    /**
     * End times that are strictly later than the given start on the same day.
     *
     * @return list<string>
     */
    public static function validEndTimes(?string $startTime): array
    {
        $slots = self::all();

        if ($startTime === null || $startTime === '') {
            return $slots;
        }

        $startMinutes = self::toMinutes($startTime);

        return array_values(array_filter(
            $slots,
            fn (string $slot): bool => self::toMinutes($slot) > $startMinutes
        ));
    }

    /**
     * Keep the current end when it is still valid; otherwise prefer start+1h, else the earliest valid slot.
     */
    public static function ensureValidEndTime(string $startTime, ?string $endTime): string
    {
        $valid = self::validEndTimes($startTime);

        if ($endTime !== null && $endTime !== '' && in_array($endTime, $valid, true)) {
            return $endTime;
        }

        $preferredMinutes = self::toMinutes($startTime) + 60;
        $preferred = sprintf('%02d:%02d', intdiv($preferredMinutes, 60) % 24, $preferredMinutes % 60);

        if (in_array($preferred, $valid, true)) {
            return $preferred;
        }

        return $valid[0] ?? '';
    }

    public static function toMinutes(string $time): int
    {
        [$hour, $minute] = explode(':', $time);

        return (int) $hour * 60 + (int) $minute;
    }
}
