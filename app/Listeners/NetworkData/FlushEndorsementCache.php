<?php

namespace App\Listeners\NetworkData;

use App\Events\NetworkData\AtcSessionEnded;
use App\Models\Atc\Endorsement;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Cache;

class FlushEndorsementCache implements ShouldQueue
{
    public function handle(AtcSessionEnded $event)
    {
        $user = $event->atcSession->account;
        Endorsement::get(['id'])->each(function (Endorsement $endorsement) use ($user) {
            Cache::forget($endorsement->generateCacheKey($user));
        });
    }
}
