<?php

declare(strict_types=1);

namespace App\Http\Controllers\Bookings;

use App\Models\Mship\Account;
use App\Services\Bookings\BookingCalendarFeedService;
use Illuminate\Http\Response;

class CalendarFeedController
{
    /**
     * Serve the member's bookings as a read-only iCal feed.
     *
     * The token is the only credential: calendar clients cannot authenticate,
     * so the URL itself must remain secret and revocable.
     */
    public function __invoke(string $token, BookingCalendarFeedService $feed): Response
    {
        $account = Account::query()
            ->where('calendar_feed_token', $token)
            ->first();

        abort_if($account === null, 404);

        return response($feed->forAccount($account), 200, [
            'Content-Type' => 'text/calendar; charset=utf-8',
            'Content-Disposition' => 'inline; filename="vatsim-uk-bookings.ics"',
            'Cache-Control' => 'private, max-age=300',
            'X-Robots-Tag' => 'noindex, nofollow',
        ]);
    }
}
