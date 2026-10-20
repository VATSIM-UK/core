<?php

declare(strict_types=1);

namespace App\Models\Mship\Concerns;

use Illuminate\Support\Str;

trait HasCalendarFeed
{
    /**
     * Return the member's calendar feed token, creating one if they have never
     * subscribed before.
     */
    public function ensureCalendarFeedToken(): string
    {
        if (blank($this->calendar_feed_token)) {
            return $this->regenerateCalendarFeedToken();
        }

        return $this->calendar_feed_token;
    }

    /**
     * Replace the calendar feed token, invalidating every existing subscription.
     *
     * The token is a bearer credential carried in a URL, so it is kept out of
     * the model event pipeline: saveQuietly() avoids AccountAltered, tracked
     * attribute changes and activity rows that could otherwise record it.
     */
    public function regenerateCalendarFeedToken(): string
    {
        $token = Str::random(48);

        $this->forceFill(['calendar_feed_token' => $token])->saveQuietly();

        return $token;
    }
}
