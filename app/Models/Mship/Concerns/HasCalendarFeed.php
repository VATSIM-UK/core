<?php

declare(strict_types=1);

namespace App\Models\Mship\Concerns;

use Illuminate\Support\Str;

trait HasCalendarFeed
{
    public function ensureCalendarFeedToken(): string
    {
        if (blank($this->calendar_feed_token)) {
            return $this->regenerateCalendarFeedToken();
        }

        return $this->calendar_feed_token;
    }

    /**
     * Rotate the token, invalidating existing subscriptions. saveQuietly()
     * keeps it out of the event pipeline, which would otherwise record it.
     */
    public function regenerateCalendarFeedToken(): string
    {
        $token = Str::random(48);

        $this->forceFill(['calendar_feed_token' => $token])->saveQuietly();

        return $token;
    }
}
