<?php

namespace App\Listeners\NetworkData;

use App\Events\NetworkData\AtcSessionEnded;
use App\Models\Atc\PositionGroup;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Cache;

class FlushEndorsementCache implements ShouldQueue
{
    public function handle(AtcSessionEnded $event)
    {
        $user = $event->atcSession->account;
        PositionGroup::get(['id'])->each(function (PositionGroup $endorsement) use ($user) {
            Cache::forget($endorsement->generateCacheKey($user));
        });
    }
}
