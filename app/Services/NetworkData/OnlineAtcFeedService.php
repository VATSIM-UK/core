<?php

namespace App\Services\NetworkData;

use App\Models\NetworkData\Atc;

class OnlineAtcFeedService
{
    public function getOnlineAtcSessions(): array
    {
        return Atc::remember(2)
            ->online()
            ->onFrequency()
            ->isUK()
            ->get()
            ->toArray();
    }
}
